<?php
session_start();
require_once __DIR__ . '/auth/database.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT o.OrderID, o.OrderDate, o.OrderStatus, cm.CakeName, ol.Quantity, ol.CakeText, ol.Layers, ol.EntirePrice
    FROM `ORDER` o
    JOIN ORDERLIST ol ON o.OrderID = ol.OrderID
    JOIN CAKE_MENU cm ON ol.CakeID = cm.CakeID
    WHERE o.CustomerID = ?
    ORDER BY o.OrderDate DESC
");
$stmt->bind_param("i", $_SESSION['UserID']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order History | DOLCI</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<nav>
    <a href="index.html" class="logo" style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;">DOLCI</a>
    <div class="nav-links">
        <a href="index.html">Home</a>
        <a href="menu.html">Menu</a>
        <a href="order.html">Order</a>
        <a href="cart.html">Cart</a>
        <a href="about.html">About</a>
        <a href="contact.html">Contact</a>
        <a href="dashboard.php" class="login-link active">My Account</a>
    </div>
</nav>

<svg class="icing-drip" viewBox="0 0 1440 40" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path fill="var(--white)" d="M0,0 H1440 V10
      C1380,10 1380,34 1320,34
      C1260,34 1260,10 1200,10
      C1140,10 1140,34 1080,34
      C1020,34 1020,10 960,10
      C900,10 900,34 840,34
      C780,34 780,10 720,10
      C660,10 660,34 600,34
      C540,34 540,10 480,10
      C420,10 420,34 360,34
      C300,34 300,10 240,10
      C180,10 180,34 120,34
      C60,34 60,10 0,10 Z" />
</svg>

<main class="page-container">
    <h1 class="page-title">Your Order History</h1>
    <p class="page-subtitle">Every cake you've ordered from DOLCI.</p>

    <div class="card" style="max-width: 800px; margin: auto;">
        <?php if (count($orders) === 0): ?>
            <p style="text-align:center;">No orders yet — <a href="menu.html">browse the menu</a> to place your first one!</p>
        <?php else: ?>
            <table style="width:100%; border-collapse: collapse;">
                <tr style="text-align:left; border-bottom: 2px solid var(--pink-soft);">
                    <th style="padding:8px;">Order</th>
                    <th style="padding:8px;">Cake</th>
                    <th style="padding:8px;">Qty</th>
                    <th style="padding:8px;">Layers</th>
                    <th style="padding:8px;">Text</th>
                    <th style="padding:8px;">Status</th>
                    <th style="padding:8px;">Total</th>
                </tr>
                <?php foreach ($orders as $o): ?>
                <tr style="border-bottom: 1px solid var(--pink-soft);">
                    <td style="padding:8px;">#<?= $o['OrderID'] ?></td>
                    <td style="padding:8px;"><?= htmlspecialchars($o['CakeName']) ?></td>
                    <td style="padding:8px;"><?= $o['Quantity'] ?></td>
                    <td style="padding:8px;"><?= $o['Layers'] ?></td>
                    <td style="padding:8px;"><?= htmlspecialchars($o['CakeText'] ?? '—') ?></td>
                    <td style="padding:8px;"><?= htmlspecialchars($o['OrderStatus']) ?></td>
                    <td style="padding:8px;">₱<?= number_format($o['EntirePrice'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
