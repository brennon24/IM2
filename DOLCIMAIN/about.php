<?php
session_start();
$loggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['UserID']);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About DOLCI</title>
    <link rel="stylesheet" href="css/style.css" />
  </head>

  <body>
    <div id="loader">
      <div class="cupcake">
        <div class="frosting"></div>

        <div class="sprinkles">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>

        <div class="wrapper"></div>
      </div>

      <h2>Preparing something sweet...</h2>
    </div>

    <nav>
      <a href="index.php" class="logo">DOLCI</a>

      <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="cart.php">Cart</a>
        <a href="about.php" class="active">About</a>
        <a href="contact.php">Contact</a>
        <?php if ($loggedIn): ?>
          <a href="dashboard.php" class="login-link">Profile</a>
          <a href="logout.php" class="login-link">Logout</a>
        <?php else: ?>
          <a href="login.php" class="login-link">Login</a>
        <?php endif; ?>
      </div>
    </nav>

    <svg
      class="icing-drip"
      viewBox="0 0 1440 40"
      preserveAspectRatio="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
    >
      <path
        fill="var(--white)"
        d="M0,0 H1440 V10
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
      C60,34 60,10 0,10 Z"
      />
    </svg>

    <main class="page-container">
      <h1 class="page-title">About DOLCI</h1>

      <div class="info-card">
        <p>
          DOLCI is a modern cake ordering and inventory management system
          designed to make ordering custom cakes simple, fast, and enjoyable.
        </p>

        <p>
          Customers can browse our menu, customize their cakes, place orders,
          and monitor their order status, while bakery staff efficiently manage
          inventory and incoming orders through one unified platform.
        </p>
      </div>
    </main>

    <footer>
      <p>&copy; 2026 DOLCI</p>
    </footer>
    <script src="js/loader.js"></script>
  </body>
</html>
