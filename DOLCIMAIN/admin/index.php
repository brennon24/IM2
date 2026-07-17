<?php require_once __DIR__ . '/admin_auth.php'; ?>
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
    <h1 class="page-title">Welcome, <?= htmlspecialchars($_SESSION['AdminName']) ?></h1>
    <p class="page-subtitle">Manage DOLCI's cake catalog, orders, and payments here.</p>

    <div class="card" style="max-width: 900px; margin: auto; display:flex; flex-wrap:wrap; gap:15px; justify-content:center;">
        <a href="cake_menu.php" class="btn btn-primary">Manage Cake Menu</a>
        <a href="orders.php" class="btn btn-primary">Manage Orders</a>
        <a href="payments.php" class="btn btn-primary">Manage Payments</a>
        <a href="reviews.php" class="btn btn-primary">Manage Reviews</a>
        <a href="users.php" class="btn btn-primary">Manage Customers</a>
        <a href="admins.php" class="btn btn-primary">Manage Admin/Staff</a>
    </div>
</main>
</body>
</html>
