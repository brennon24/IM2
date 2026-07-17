<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/database.php'; // expects $conn (mysqli)

$userId = (int) ($_SESSION['user_id'] ?? $_SESSION['UserID'] ?? 0);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Please log in.', 'redirect' => 'login.php']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$cartId = (int) ($data['id'] ?? 0);

if (!$cartId) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
    exit;
}

$cakeId = (int) ($data['cakeId'] ?? 0);
$flavor = $data['flavor'] ?? '';
$layers = (int) ($data['tiers'] ?? 1);
$icing = is_array($data['icing'] ?? null) ? json_encode($data['icing']) : ($data['icing'] ?? '');
$filling = is_array($data['filling'] ?? null) ? json_encode($data['filling']) : ($data['filling'] ?? '');
$decorations = !empty($data['decorations']) ? implode(', ', $data['decorations']) : '';
$cakeText = $data['dedication'] ?? '';
$totalPrice = (float) ($data['total'] ?? 0);

// UserID is included in the WHERE clause so a user can only ever edit their own cart rows
$stmt = $conn->prepare(
    "UPDATE CART
     SET CakeID = ?, Flavor = ?, Layers = ?, Icing = ?, Filling = ?, Decorations = ?, CakeText = ?, TotalPrice = ?
     WHERE CartID = ? AND UserID = ?"
);
$stmt->bind_param(
    'isissssdii',
    $cakeId,
    $flavor,
    $layers,
    $icing,
    $filling,
    $decorations,
    $cakeText,
    $totalPrice,
    $cartId,
    $userId
);

if ($stmt->execute()) {
    if ($stmt->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found or not yours to edit.']);
    } else {
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Could not update: ' . $stmt->error]);
}

$stmt->close();
$conn->close();