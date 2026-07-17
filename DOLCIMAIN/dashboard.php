<?php
session_start();
require_once __DIR__ . '/auth/database.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit;
}

 $stmt = $conn->prepare("SELECT FullName, Email, ContactNumber, DateRegistered FROM USER_ACCOUNT WHERE UserID = ?");
 $stmt->bind_param("i", $_SESSION['UserID']);
 $stmt->execute();
 $user = $stmt->get_result()->fetch_assoc();
 $stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Account | DOLCI</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<nav>
    <a href="index.php" class="logo" style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;">DOLCI</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="order_history.php">Orders</a>
        <a href="cart.php">Cart</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
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
    <h1 class="page-title">Hi, <?= htmlspecialchars($user['FullName']) ?>!</h1>
    <p class="page-subtitle">Manage your account and view your order history.</p>

    <div class="card" style="max-width: 500px; margin: auto">
        <p><strong>Email:</strong> <?= htmlspecialchars($user['Email']) ?></p>
        <p><strong>Contact Number:</strong> <?= htmlspecialchars($user['ContactNumber']) ?></p>
        <p><strong>Member Since:</strong> <?= date('F j, Y', strtotime($user['DateRegistered'])) ?></p>

        <br>
        <a href="order_history.php" class="btn btn-primary" style="width: 100%; text-align:center; display:block;">View Order History</a>
        <br>
        <a href="logout.php" class="btn btn-secondary" style="width: 100%; text-align:center; display:block;">Logout</a>
    </div>
</main>
</body>
</html>