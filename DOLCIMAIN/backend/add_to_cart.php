<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/database.php'; // expects $conn (mysqli)

$userId = (int) ($_SESSION['user_id'] ?? $_SESSION['UserID'] ?? 0);
if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to add items to your cart.',
        'redirect' => 'login.php',
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['flavor']) || empty($data['cakeId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid cake data.']);
    exit;
}

$cakeId = (int) $data['cakeId'];
$flavor = $data['flavor'];
$layers = (int) ($data['tiers'] ?? 1);

// Per-layer mode sends an array (one entry per tier) — store as JSON.
// Uniform mode sends a single string — store as-is.
$icing = is_array($data['icing'] ?? null) ? json_encode($data['icing']) : ($data['icing'] ?? '');
$filling = is_array($data['filling'] ?? null) ? json_encode($data['filling']) : ($data['filling'] ?? '');

$decorations = !empty($data['decorations']) ? implode(', ', $data['decorations']) : '';
$cakeText = $data['dedication'] ?? '';
$totalPrice = (float) ($data['total'] ?? 0);

$stmt = $conn->prepare(
    "INSERT INTO CART (UserID, CakeID, Flavor, Layers, Icing, Filling, Decorations, CakeText, Quantity, TotalPrice)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)"
);
$stmt->bind_param(
    'iisissssd',
    $userId,
    $cakeId,
    $flavor,
    $layers,
    $icing,
    $filling,
    $decorations,
    $cakeText,
    $totalPrice
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not add to cart: ' . $stmt->error]);
}

$stmt->close();
$conn->close();