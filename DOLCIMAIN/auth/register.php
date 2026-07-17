<?php
session_start();
require_once __DIR__ . '/database.php'; // expects $conn (mysqli)

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
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullname === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } else {
        $stmt = $conn->prepare("SELECT UserID FROM USER_ACCOUNT WHERE Email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'An account with that email already exists.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO USER_ACCOUNT (FullName, Email, Password, ContactNumber) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('ssss', $fullname, $email, $hashedPassword, $contact);

        if ($stmt->execute()) {
            $userId = (int) $stmt->insert_id;
            $_SESSION['user_id'] = $userId;
            $_SESSION['UserID'] = $userId;
            $_SESSION['user_name'] = $fullname;
            $_SESSION['FullName'] = $fullname;
            $stmt->close();
            header('Location: ' . ($basePath ? $basePath : '.') . '/index.php');
            exit;
        } else {
            $errors[] = 'Could not create account: ' . $stmt->error;
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOLCI - Register</title>
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
        <h1 class="page-title">Create Your Account</h1>
        <p class="page-subtitle">Sign up to start ordering your favorite cakes and treats.</p>

        <div class="card" style="max-width: 500px; margin: auto">

            <?php if (!empty($errors)): ?>
                <div class="form-error">
                    <?= htmlspecialchars(implode(' ', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact" value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%">Register</button>

            </form>

            <p style="text-align: center; margin-top: 24px; margin-bottom: 0; color: var(--cocoa-soft);">
                Already have an account?
                <a href="<?= $basePath ?>/login.php" style="color: var(--pink-bubble); font-weight: 700; text-decoration: none;">Login</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 DOLCI</p>
    </footer>

</body>
</html>