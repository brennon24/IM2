<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/database.php';

 $userId = (int) ($_SESSION['user_id'] ?? $_SESSION['UserID'] ?? 0);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Please log in to place an order.', 'redirect' => 'login.php']);
    exit;
}

// Fetch current cart items
 $stmt = $conn->prepare("SELECT * FROM CART WHERE UserID = ?");
 $stmt->bind_param('i', $userId);
 $stmt->execute();
 $result = $stmt->get_result();
 $cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}
 $stmt->close();

if (empty($cartItems)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

// Start Database Transaction (Ensures all queries succeed or none do)
 $conn->begin_transaction();

try {
    // 1. Create the Official Order Record
    $paymentMethod = 'Cash on Delivery';
    $customNote = 'Order placed via Cash on Delivery';
    $orderCode = generateUniqueOrderCode($conn);
    $stmtOrder = $conn->prepare(
        "INSERT INTO `ORDER` (CustomerID, CustomNote, OrderStatus, PaymentMethod, OrderCode) VALUES (?, ?, 'Pending', ?, ?)"
    );
    $stmtOrder->bind_param('isss', $userId, $customNote, $paymentMethod, $orderCode);
    $stmtOrder->execute();
    $orderId = $stmtOrder->insert_id;
    $stmtOrder->close();

    // 2. Move Cart Items to ORDER_ITEM table permanently
    $stmtItem = $conn->prepare(
        "INSERT INTO ORDER_ITEM (OrderID, CakeID, Flavor, Layers, Icing, Filling, Decorations, CakeText, Quantity, TotalPrice)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $orderGrandTotal = 0;

    foreach ($cartItems as $item) {
        $stmtItem->bind_param(
            'iisissssid',
            $orderId,
            $item['CakeID'],
            $item['Flavor'],
            $item['Layers'],
            $item['Icing'],
            $item['Filling'],
            $item['Decorations'],
            $item['CakeText'],
            $item['Quantity'],
            $item['TotalPrice']
        );
        $stmtItem->execute();
        $orderGrandTotal += (float) $item['TotalPrice'];
    }
    $stmtItem->close();

    // 3. Create the matching Payment record — every order gets exactly one,
    //    starting as 'Unpaid' since Cash on Delivery hasn't been collected yet.
    $paymentStatus = 'Unpaid';
    $stmtPayment = $conn->prepare(
        "INSERT INTO PAYMENT (OrderID, PaymentMethod, PaymentStatus) VALUES (?, ?, ?)"
    );
    $stmtPayment->bind_param('iss', $orderId, $paymentMethod, $paymentStatus);
    $stmtPayment->execute();
    $stmtPayment->close();

    // 4. Clear the user's cart
    $stmtClear = $conn->prepare("DELETE FROM CART WHERE UserID = ?");
    $stmtClear->bind_param('i', $userId);
    $stmtClear->execute();
    $stmtClear->close();

    // Commit all changes to the database
    $conn->commit();
    echo json_encode(['success' => true, 'orderId' => $orderId, 'orderCode' => $orderCode]);

} catch (Exception $e) {
    // If any error occurs, undo the changes
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
}

 $conn->close();
?>