<?php
session_start();
require_once __DIR__ . '/auth/database.php'; // expects $conn (mysqli)

$loggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['UserID']);
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? $_SESSION['UserID'] ?? 0);

$cart = [];
$stmt = $conn->prepare("SELECT * FROM CART WHERE UserID = ? ORDER BY DateAdded DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cart[] = $row;
}
$stmt->close();

$grandTotal = array_reduce($cart, function ($sum, $item) {
    return $sum + (float) $item['TotalPrice'];
}, 0);

// Helper: per-layer icing/filling is stored as a JSON string; uniform mode is a plain string
function decodeMaybeJson($value)
{
    $decoded = json_decode($value, true);
    return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DOLCI Cart</title>
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
        <a href="cart.php" class="active">Cart</a>
        <a href="about.php">About</a>
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
      <h1 class="page-title">Your Cart</h1>

      <?php if (empty($cart)): ?>
        <div class="card cart-empty">
          <h2>No goodies yet 🍰</h2>
          <p>Looks like your cart is empty. Start browsing our delicious cakes.</p>
          <a class="btn btn-primary" href="menu.php">Browse Menu</a>
        </div>
      <?php else: ?>

        <div class="cart-list">
          <?php foreach ($cart as $item): ?>
            <?php
            $perLayerIcing = decodeMaybeJson($item['Icing']);
            $perLayerFilling = decodeMaybeJson($item['Filling']);
            $isPerLayer = $perLayerIcing !== null;
            ?>
            <div class="card cart-item">
              <div class="cart-item-header">
                <h3><?= htmlspecialchars($item['Flavor'] ?? 'Cake') ?></h3>
                <span class="amount cart-item-total">
                  ₱<?= number_format((float) $item['TotalPrice'], 2) ?>
                </span>
              </div>

              <div class="cart-item-details">
                <p><strong>Tiers:</strong> <?= (int) $item['Layers'] ?></p>

                <?php if ($isPerLayer): ?>
                  <p><strong>Icing &amp; Filling (per tier):</strong></p>
                  <ul class="cart-layer-list">
                    <?php foreach ($perLayerIcing as $i => $icingRow): ?>
                      <?php $fillingRow = $perLayerFilling[$i] ?? null; ?>
                      <li>
                        Tier <?= (int) ($icingRow['tier'] ?? $i + 1) ?>:
                        <?= htmlspecialchars($icingRow['value'] ?? '') ?> icing,
                        <?= htmlspecialchars($fillingRow['value'] ?? '') ?> filling
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <p>
                    <strong>Icing:</strong> <?= htmlspecialchars($item['Icing'] ?? '—') ?><br />
                    <strong>Filling:</strong> <?= htmlspecialchars($item['Filling'] ?? '—') ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($item['Decorations'])): ?>
                  <p><strong>Decorations:</strong> <?= htmlspecialchars($item['Decorations']) ?></p>
                <?php endif; ?>

                <?php if (!empty($item['CakeText'])): ?>
                  <p><strong>Message:</strong> "<?= htmlspecialchars($item['CakeText']) ?>"</p>
                <?php endif; ?>
              </div>

              <div class="cart-item-actions">
                <a class="btn btn-secondary" href="menu.php?edit=<?= (int) $item['CartID'] ?>">
                  Edit
                </a>
                <button
                  type="button"
                  class="btn btn-secondary remove-item-btn"
                  data-id="<?= (int) $item['CartID'] ?>"
                >
                  Remove
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <br />

        <div class="card price-summary">
          <div class="price-line price-total">
            <span>Grand Total</span>
            <span class="amount">₱<?= number_format($grandTotal, 2) ?></span>
          </div>
        </div>

        <br />

        <div style="text-align: center">
          <button class="btn btn-primary" disabled>
            Proceed to Checkout (coming soon)
          </button>
        </div>

      <?php endif; ?>
    </main>

    <footer>
      <p>&copy; 2026 DOLCI</p>
    </footer>
    <script src="js/loader.js"></script>
    <script>
      document.querySelectorAll(".remove-item-btn").forEach((btn) => {
        btn.addEventListener("click", async () => {
          const id = btn.dataset.id;
          if (!confirm("Remove this cake from your cart?")) return;

          try {
            const res = await fetch("backend/remove_from_cart.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ id }),
            });
            const result = await res.json();
            if (result.success) {
              window.location.href = "cart.php";
            } else {
              alert(result.message || "Couldn't remove that item.");
            }
          } catch (err) {
            console.error(err);
            alert("Something went wrong removing that item.");
          }
        });
      });
    </script>
  </body>
</html>