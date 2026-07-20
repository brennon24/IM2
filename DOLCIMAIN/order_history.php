<?php
session_start();
require_once __DIR__ . '/auth/database.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit;
}

// Fetch all orders for this customer
 $stmt = $conn->prepare("SELECT * FROM `ORDER` WHERE CustomerID = ? ORDER BY OrderDate DESC");
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
    <a href="index.php" class="logo" style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;">DOLCI</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <!-- Notice the "Orders" link is missing here -->
        <a href="cart.php">Cart</a>
        <a href="reviews.php">Reviews</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="dashboard.php" class="login-link">Profile</a>
        <a href="logout.php" class="login-link">Logout</a>
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

    <?php if (count($orders) === 0): ?>
        <div class="card cart-empty" style="max-width: 600px; margin: auto;">
            <h2>No orders yet 🍰</h2>
            <p>You haven't placed any orders yet. Let's fix that!</p>
            <a class="btn btn-primary" href="menu.php">Start an Order</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $o): 
            // Fetch the items for THIS specific order
            $orderId = (int) $o['OrderID'];
            $itemStmt = $conn->prepare("SELECT * FROM ORDER_ITEM WHERE OrderID = ?");
            $itemStmt->bind_param("i", $orderId);
            $itemStmt->execute();
            $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $itemStmt->close();

            // Calculate total for this specific order
            $orderTotal = array_reduce($items, function ($sum, $item) {
                return $sum + (float) $item['TotalPrice'];
            }, 0);
        ?>
            <div class="card" style="max-width: 800px; margin: 0 auto 30px auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 2px solid var(--pink-soft); padding-bottom: 10px;">
                    <div>
                        <h3 style="margin: 0; color: var(--pink-bubble); font-family: var(--font-display);">Order #<?= $o['OrderID'] ?></h3>
                        <small style="color: var(--cocoa-soft);"><?= date('F j, Y \a\t g:i A', strtotime($o['OrderDate'])) ?></small>
                    </div>
                    <span class="order-status status-<?= strtolower(htmlspecialchars($o['OrderStatus'])) ?>">
                        <?= htmlspecialchars($o['OrderStatus']) ?>
                    </span>
                </div>

                <p style="margin: 0 0 15px 0;"><strong>Payment Method:</strong> <?= htmlspecialchars($o['PaymentMethod']) ?></p>

                <table style="width:100%; border-collapse: collapse;">
                    <tr style="text-align:left; border-bottom: 1px dashed var(--pink-soft);">
                        <th style="padding:8px;">Cake Flavor</th>
                        <th style="padding:8px;">Qty</th>
                        <th style="padding:8px;">Layers</th>
                        <th style="padding:8px;">Message</th>
                        <th style="padding:8px; text-align:right;">Price</th>
                    </tr>
                    <?php foreach ($items as $item): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding:8px; font-weight: 700;"><?= htmlspecialchars($item['Flavor'] ?? 'Custom Cake') ?></td>
                        <td style="padding:8px;"><?= (int) $item['Quantity'] ?></td>
                        <td style="padding:8px;"><?= (int) $item['Layers'] ?></td>
                        <td style="padding:8px;"><?= htmlspecialchars($item['CakeText'] ?? '—') ?></td>
                        <td style="padding:8px; text-align:right; color: var(--gold-deep); font-weight: 800;">
                            ₱<?= number_format((float) $item['TotalPrice'], 2) ?>
                            <?php if (strtolower($o['OrderStatus']) === 'completed'): ?>
                                <br>
                                <a href="reviews.php?cake_id=<?= (int) $item['CakeID'] ?>#leave-review"
                                   style="font-size:0.8rem; font-weight:700; color: var(--pink-bubble);">
                                    Leave a Review
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="4" style="padding:12px 8px; text-align:right; font-weight: 800;">Order Total:</td>
                        <td style="padding:12px 8px; text-align:right; font-size: 1.2rem; color: var(--pink-bubble); font-weight: 800;">₱<?= number_format($orderTotal, 2) ?></td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer>
  <p>&copy; 2026 DOLCI</p>
</footer>
</body>
</html>