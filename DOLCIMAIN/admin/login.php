<?php
session_start();
require_once __DIR__ . '/../auth/database.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT AdminID, AdminName, Password, Role FROM ADMIN WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    if ($admin && password_verify($password, $admin['Password'])) {
        $_SESSION['AdminID'] = $admin['AdminID'];
        $_SESSION['AdminName'] = $admin['AdminName'];
        $_SESSION['Role'] = $admin['Role'];
        header("Location: index.php");
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
    <title>Admin Login | DOLCI</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<nav>
    <a href="../index.html" class="logo" style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;">DOLCI</a>
    <div class="nav-links">
        <a href="../index.html">Customer Site</a>
    </div>
</nav>

<main class="page-container">
    <h1 class="page-title">Admin Login</h1>
    <p class="page-subtitle">For DOLCI staff and administrators only.</p>

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
    </div>
</main>
</body>
</html>
