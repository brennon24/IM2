<?php
session_start();
require_once __DIR__ . '/auth/database.php'; // expects $conn (mysqli)

$errors = [];
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if (basename($basePath) === 'auth') {
    $basePath = dirname($basePath);
}
$basePath = $basePath === '/' ? '' : $basePath;
$loggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['UserID']);

if ($loggedIn) {
    header('Location: ' . ($basePath ? $basePath : '.') . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Please fill in both fields.';
    } else {
        $stmt = $conn->prepare("SELECT UserID, FullName, Password FROM USER_ACCOUNT WHERE Email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['Password'])) {
            $userId = (int) $user['UserID'];
            $_SESSION['user_id'] = $userId;
            $_SESSION['UserID'] = $userId;
            $_SESSION['user_name'] = $user['FullName'];
            $_SESSION['FullName'] = $user['FullName'];
            header('Location: ' . ($basePath ? $basePath : '.') . '/index.php');
            exit;
        } else {
            $errors[] = 'Incorrect email or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOLCI - Login</title>
    <link rel="stylesheet" href="<?= $basePath ?>/css/style.css">
</head>

<body>

    <nav>
        <a href="<?= $basePath ?>/index.php" class="logo">DOLCI</a>
        <div class="nav-links">
            <a href="<?= $basePath ?>/index.php">Home</a>
            <a href="<?= $basePath ?>/menu.php">Menu</a>
            <a href="<?= $basePath ?>/about.php">About</a>
            <a href="<?= $basePath ?>/contact.php">Contact</a>
            <?= $loggedIn
                ? '<a href="' . $basePath . '/dashboard.php" class="login-link">Profile</a>'
                : '<a href="' . $basePath . '/login.php" class="login-link">Login</a>' ?>
        </div>
    </nav>

    <main class="page-container">
        <h1 class="page-title">Welcome Back!</h1>
        <p class="page-subtitle">Log in to place orders and manage your account.</p>

        <div class="card" style="max-width: 500px; margin: auto">

            <?php if (!empty($errors)): ?>
                <div class="form-error">
                    <?= htmlspecialchars(implode(' ', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%">Login</button>

            </form>

            <p style="text-align: center; margin-top: 24px; margin-bottom: 0; color: var(--cocoa-soft);">
                Don't have an account?
                <a href="<?= $basePath ?>/register.php" style="color: var(--pink-bubble); font-weight: 700; text-decoration: none;">Sign Up</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 DOLCI</p>
    </footer>

</body>
</html>