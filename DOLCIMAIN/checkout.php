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

function decodeMaybeJson($value) {
    $decoded = json_decode($value, true);
    return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DOLCI Checkout</title>
    <link rel="stylesheet" href="css/style.css" />
  </head>

  <body>
    <div id="loader">
      <div class="cupcake">
        <div class="frosting"></div>
        <div class="sprinkles">
          <span></span><span></span><span></span><span></span><span></span>
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
        <a href="order_history.php">Orders</a>
        <a href="cart.php">Cart</a>
        <a href="reviews.php">Reviews</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="dashboard.php" class="login-link">Profile</a>
        <a href="logout.php" class="login-link">Logout</a>
      </div>
    </nav>

    <svg class="icing-drip" viewBox="0 0 1440 40" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <path fill="var(--white)" d="M0,0 H1440 V10 C1380,10 1380,34 1320,34 C1260,34 1260,10 1200,10 C1140,10 1140,34 1080,34 C1020,34 1020,10 960,10 C900,10 900,34 840,34 C780,34 780,10 720,10 C660,10 660,34 600,34 C540,34 540,10 480,10 C420,10 420,34 360,34 C300,34 300,10 240,10 C180,10 180,34 120,34 C60,34 60,10 0,10 Z" />
    </svg>

    <main class="page-container">
      <h1 class="page-title">Checkout</h1>

      <?php if (empty($cart)): ?>
        <div class="card cart-empty">
          <h2>No goodies yet 🍰</h2>
          <p>Looks like your cart is empty. Start browsing our delicious cakes.</p>
          <a class="btn btn-primary" href="menu.php">Browse Menu</a>
        </div>
      <?php else: ?>
        <div class="checkout-layout">
          
          <!-- Order Summary -->
          <div class="cart-list">
            <?php foreach ($cart as $item): ?>
              <?php $perLayerIcing = decodeMaybeJson($item['Icing']); ?>
              <div class="card cart-item">
                <div class="cart-item-header">
                  <h3><?= htmlspecialchars($item['Flavor'] ?? 'Cake') ?></h3>
                  <span class="amount cart-item-total">₱<?= number_format((float) $item['TotalPrice'], 2) ?></span>
                </div>
                <div class="cart-item-details">
                  <p><strong>Tiers:</strong> <?= (int) $item['Layers'] ?></p>
                  <?php if (!empty($item['CakeText'])): ?>
                    <p><strong>Message:</strong> "<?= htmlspecialchars($item['CakeText']) ?>"</p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Payment & Total -->
          <div class="checkout-sidebar">
            <div class="card checkout-summary">
              <h2 class="section-title">Payment Method</h2>
              
              <div class="payment-option selected" data-method="Cash on Delivery">
                <input type="radio" id="cod" name="payment" value="Cash on Delivery" checked>
                <label for="cod">💵 Cash on Delivery</label>
                <p class="payment-desc">Pay with cash when your order is delivered.</p>
              </div>

              <div class="payment-option" data-method="QR">
                <input type="radio" id="qr" name="payment" value="QR">
                <label for="qr">📱 QR Payment</label>
                <p class="payment-desc">Scan the QR code, upload proof, and wait for confirmation.</p>
              </div>

              <div id="qrSection" style="display:none; margin-top: 16px; border: 2px dashed var(--pink-soft); border-radius: 16px; padding: 16px; background: #fffdf8;">
                <p style="margin:0 0 10px; font-weight:700; color: var(--pink-bubble);">Scan this QR to pay</p>
                <div style="display:flex; justify-content:center; align-items:center; min-height: 180px; border-radius: 12px; background: #fff; border: 1px solid var(--pink-soft);">
                  <img src="images/qr_placeholder.jpg" alt="QR code placeholder" style="max-width: 180px; width:100%; height:auto;">
                </div>
                <p style="margin:10px 0 0; font-size:0.85rem; color: var(--cocoa-soft);">Once done with payment, contact DOLCI and send your Order ID and reference number for confirmation.</p>
              </div>

              <div class="price-summary" style="margin-top: 20px; border-top: 2px solid var(--pink-soft); padding-top: 20px;">
                <div class="price-line price-total">
                  <span>Grand Total</span>
                  <span class="amount">₱<?= number_format($grandTotal, 2) ?></span>
                </div>
              </div>

              <button id="place-order-btn" class="btn btn-primary" style="width: 100%; margin-top: 24px;">
                Place Order
              </button>
              <div id="qrConfirmation" style="display:none; margin-top: 16px; padding: 16px; border-radius: 16px; background: #fff7fb; border: 1px solid var(--pink-soft); text-align:center;">
                <p style="margin:0 0 10px; font-weight:700; color: var(--pink-bubble);">Kindly wait for payment confirmation by the admin.</p>
                <a href="order_history.php" class="btn btn-secondary" style="width:100%; margin-top: 8px; text-align:center;">Exit to Orders</a>
              </div>
              <a href="cart.php" class="btn btn-secondary" style="width: 100%; margin-top: 12px; text-align:center;">
                Back to Cart
              </a>
            </div>
          </div>

        </div>
      <?php endif; ?>
    </main>

    <footer><p>&copy; 2026 DOLCI</p></footer>
    
    <script src="js/loader.js"></script>
    <script>
      // Failsafe to ensure loader hides
      setTimeout(() => {
        const loader = document.getElementById("loader");
        if (loader) loader.classList.add("hidden");
      }, 1500);

      const paymentOptions = Array.from(document.querySelectorAll('.payment-option'));
      const qrSection = document.getElementById('qrSection');
      const qrConfirmation = document.getElementById('qrConfirmation');

      paymentOptions.forEach((option) => {
        option.addEventListener('click', () => {
          const radio = option.querySelector('input[type="radio"]');
          if (radio) radio.checked = true;
          paymentOptions.forEach((item) => item.classList.remove('selected'));
          option.classList.add('selected');
          qrSection.style.display = option.dataset.method === 'QR' ? 'block' : 'none';
        });
      });

      document.getElementById("place-order-btn")?.addEventListener("click", async () => {
        const btn = document.getElementById("place-order-btn");
        const selectedMethod = document.querySelector('input[name="payment"]:checked')?.value || 'Cash on Delivery';
        btn.textContent = "Placing Order...";
        btn.disabled = true;

        try {
          const res = await fetch("backend/place_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ paymentMethod: selectedMethod })
          });
          const result = await res.json();
          
          if (result.success) {
            if (selectedMethod === 'QR') {
              btn.style.display = 'none';
              qrConfirmation.style.display = 'block';
            } else {
              alert("Order placed successfully! Your order number is #" + (result.orderCode || result.orderId));
              window.location.href = "order_history.php";
            }
          } else {
            alert(result.message || "Failed to place order.");
            btn.textContent = "Place Order";
            btn.disabled = false;
          }
        } catch (err) {
          console.error(err);
          alert("Something went wrong placing your order. Check the console for details.");
          btn.textContent = "Place Order";
          btn.disabled = false;
        }
      });
    </script>
  </body>
</html>