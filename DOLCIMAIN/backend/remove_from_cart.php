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

$stmt = $conn->prepare("DELETE FROM CART WHERE CartID = ? AND UserID = ?");
$stmt->bind_param('ii', $cartId, $userId);

if ($stmt->execute()) {
    if ($stmt->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found or not yours to remove.']);
    } else {
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Could not remove: ' . $stmt->error]);
}

$stmt->close();
$conn->close();