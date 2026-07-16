<?php
session_start();
require_once __DIR__ . '/database.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
$baseUrl = dirname($scriptName);
if (basename($baseUrl) === 'auth') {
    $baseUrl = dirname($baseUrl);
}
$baseUrl = rtrim($baseUrl, '/');

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email is already taken
        $stmt = $conn->prepare("SELECT UserID FROM USER_ACCOUNT WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with that email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO USER_ACCOUNT (FullName, Email, Password, ContactNumber) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $fullName, $email, $hashedPassword, $contact);
            $insert->execute();
            $insert->close();

            header("Location: {$baseUrl}/login.php");
            exit;
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register | DOLCI</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/css/style.css" />
</head>
<body>
<nav>
    <a href="<?= htmlspecialchars($baseUrl) ?>/index.php" class="logo" style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;">DOLCI</a>
    <div class="nav-links">
        <a href="<?= htmlspecialchars($baseUrl) ?>/index.php">Home</a>
        <a href="<?= htmlspecialchars($baseUrl) ?>/menu.php">Menu</a>
        <a href="<?= htmlspecialchars($baseUrl) ?>/order.php">Order</a>
        <a href="<?= htmlspecialchars($baseUrl) ?>/cart.php">Cart</a>
        <a href="<?= htmlspecialchars($baseUrl) ?>/about.php">About</a>
        <a href="<?= htmlspecialchars($baseUrl) ?>/contact.php">Contact</a>
        <a href="<?= htmlspecialchars($baseUrl) ?>/login.php" class="login-link">Login</a>
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
    <h1 class="page-title">Create Your Account</h1>
    <p class="page-subtitle">Join DOLCI to start ordering your dream cakes.</p>

    <div class="card" style="max-width: 500px; margin: auto">
        <?php if ($error): ?>
            <p style="color: var(--pink-deep); font-weight: 600;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="<?= htmlspecialchars($baseUrl) ?>/register.php">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button class="btn btn-primary" style="width: 100%" type="submit">Register</button>
        </form>

        <p style="text-align:center; margin-top:16px;">
            Already have an account? <a href="<?= htmlspecialchars($baseUrl) ?>/login.php">Login here</a>
        </p>
    </div>
</main>
</body>
</html>