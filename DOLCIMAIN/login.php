<?php
session_start();
require_once __DIR__ . '/auth/database.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT UserID, FullName, Password FROM USER_ACCOUNT WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['FullName'] = $user['FullName'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Incorrect email or password.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | DOLCI</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<nav>
    <a href="index.php" class="logo" style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;">DOLCI</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="order.php">Order</a>
        <a href="cart.php">Cart</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="login.php" class="login-link active">Login</a>
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
    <h1 class="page-title">Welcome Back!</h1>
    <p class="page-subtitle">Log in to place orders and manage your account.</p>

    <div class="card" style="max-width: 500px; margin: auto">
        <?php if ($error): ?>
            <p style="color: var(--pink-deep); font-weight: 600;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button class="btn btn-primary" style="width: 100%" type="submit">Login</button>
        </form>

        <p style="text-align:center; margin-top:16px;">
            No account? <a href="register.php">Register here</a>
        </p>
    </div>
</main>
</body>
</html>
