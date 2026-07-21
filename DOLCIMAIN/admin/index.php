<?php
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../auth/database.php';

$stats = [
    'orders' => (int) $conn->query("SELECT COUNT(*) AS total FROM `ORDER`")->fetch_assoc()['total'],
    'customers' => (int) $conn->query("SELECT COUNT(*) AS total FROM USER_ACCOUNT")->fetch_assoc()['total'],
    'cakes' => (int) $conn->query("SELECT COUNT(*) AS total FROM CAKE_MENU")->fetch_assoc()['total'],
    'admins' => (int) $conn->query("SELECT COUNT(*) AS total FROM ADMIN")->fetch_assoc()['total'],
];

$recentOrders = $conn->query("SELECT o.OrderID, o.OrderCode, u.FullName, o.OrderStatus, o.OrderDate
    FROM `ORDER` o
    JOIN USER_ACCOUNT u ON o.CustomerID = u.UserID
    ORDER BY o.OrderID DESC
    LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | DOLCI</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<nav>
    <a href="index.php" class="logo" style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;">DOLCI Admin</a>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="cake_menu.php">Cake Menu</a>
        <a href="orders.php">Orders</a>
        <a href="payments.php">Payments</a>
        <a href="reviews.php">Reviews</a>
        <a href="users.php">Customers</a>
        <a href="admins.php">Admin/Staff</a>
        <a href="../index.php">View Customer Site</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="page-container">
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-subtitle">Overview of your DOLCI store activity.</p>

    <div class="card">
        <h2 class="section-title">Quick Stats</h2>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
            <div style="background: var(--pink-soft); padding: 16px; border-radius: var(--radius-lg);">
                <strong><?= $stats['orders'] ?></strong>
                <div>Orders</div>
            </div>
            <div style="background: var(--pink-soft); padding: 16px; border-radius: var(--radius-lg);">
                <strong><?= $stats['customers'] ?></strong>
                <div>Customers</div>
            </div>
            <div style="background: var(--pink-soft); padding: 16px; border-radius: var(--radius-lg);">
                <strong><?= $stats['cakes'] ?></strong>
                <div>Cakes</div>
            </div>
            <div style="background: var(--pink-soft); padding: 16px; border-radius: var(--radius-lg);">
                <strong><?= $stats['admins'] ?></strong>
                <div>Admins / Staff</div>
            </div>
        </div>
    </div>

    <br>

    <div class="card">
        <h2 class="section-title">Recent Orders</h2>
        <table class="admin-table">
            <tr><th>ID</th><th>Customer</th><th>Status</th><th>Date</th></tr>
            <?php if (count($recentOrders) === 0): ?>
                <tr><td colspan="4">No orders yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td>#<?= htmlspecialchars($order['OrderCode'] ?? $order['OrderID']) ?></td>
                    <td><?= htmlspecialchars($order['FullName']) ?></td>
                    <td><?= htmlspecialchars($order['OrderStatus']) ?></td>
                    <td><?= htmlspecialchars($order['OrderDate']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
