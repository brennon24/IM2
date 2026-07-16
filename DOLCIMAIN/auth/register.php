<!DOCTYPE html>
<!-- register.php -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOLCI - Register</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <nav>
        <a href="../index.html" class="logo">DOLCI</a>
        <div class="nav-links">
            <a href="../index.html">Home</a>
            <a href="../menu.html">Menu</a>
            <a href="../about.html">About</a>
            <a href="../contact.html">Contact</a>
            <a href="login.php" class="login-link">Login</a>
        </div>
    </nav>

    <main class="page-container">
        <h1 class="page-title">Create Your Account</h1>
        <p class="page-subtitle">Sign up to start ordering your favorite cakes and treats.</p>

        <div class="card" style="max-width: 500px; margin: auto">
            <form method="POST">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password">
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%">Register</button>

            </form>

            <p style="text-align: center; margin-top: 24px; margin-bottom: 0; color: var(--cocoa-soft);">
                Already have an account?
                <a href="login.php" style="color: var(--pink-bubble); font-weight: 700; text-decoration: none;">Login</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 DOLCI</p>
    </footer>

</body>
</html>