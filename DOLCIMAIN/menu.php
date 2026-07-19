<?php
session_start();
require_once __DIR__ . '/auth/database.php'; // expects $conn (mysqli)
$editItem = null;
$loggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['UserID']);

if (isset($_GET['edit']) && (!empty($_SESSION['user_id']) || !empty($_SESSION['UserID']))) {
    $cartId = (int) $_GET['edit'];
    $userId = (int) ($_SESSION['user_id'] ?? $_SESSION['UserID'] ?? 0);

    $stmt = $conn->prepare("SELECT * FROM CART WHERE CartID = ? AND UserID = ?");
    $stmt->bind_param('ii', $cartId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $decodedIcing = json_decode($row['Icing'], true);
        $decodedFilling = json_decode($row['Filling'], true);
        $isPerLayer = json_last_error() === JSON_ERROR_NONE && is_array($decodedIcing);

        $editItem = [
            'id' => (int) $row['CartID'],
            'cakeId' => (int) $row['CakeID'],
            'flavor' => $row['Flavor'],
            'base_price' => null, // resolved client-side from the matching flavor card
            'size' => $row['Size'] ?? '8 inch',
            'tiers' => (int) $row['Layers'],
            'icing' => $isPerLayer ? $decodedIcing : $row['Icing'],
            'filling' => $isPerLayer ? $decodedFilling : $row['Filling'],
            'decorations' => $row['Decorations'] ? array_map('trim', explode(',', $row['Decorations'])) : [],
            'dedication' => $row['CakeText'],
        ];
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DOLCI Menu</title>
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
    <a href="index.php" class="logo" style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;">DOLCI</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="order_history.php">Orders</a>
        <a href="cart.php">Cart</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <?php 
            // For pages that have the $loggedIn check, keep the Profile/Logout buttons:
            $loggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['UserID']);
            if ($loggedIn): ?>
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
      <h1 class="page-title">Choose among our dedicated selections!</h1>
      <p class="page-subtitle">
        Pick a flavor, build it up in tiers, and customize every layer.
      </p>

      <!-- Flavor Showcase -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">Choose Your Flavor</h2>
        </div>

        <div class="flavor-grid" id="flavorGrid">
          <div class="flavor-card" data-cakeid="1" data-flavor="vanilla" data-price="500">
            <div class="flavor-cake flavor-vanilla">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Vanilla</h3>
            <p class="price">₱500 base</p>
          </div>

          <div class="flavor-card" data-cakeid="2" data-flavor="Chocolate" data-price="550">
            <div class="flavor-cake flavor-chocolate">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Chocolate</h3>
            <p class="price">₱550 base</p>
          </div>

          <div class="flavor-card" data-cakeid="3" data-flavor="strawberry" data-price="550">
            <div class="flavor-cake flavor-strawberry">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Strawberry</h3>
            <p class="price">₱550 base</p>
          </div>

          <div class="flavor-card" data-cakeid="4" data-flavor="redvelvet" data-price="650">
            <div class="flavor-cake flavor-redvelvet">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Red Velvet</h3>
            <p class="price">₱650 base</p>
          </div>

          <div class="flavor-card" data-cakeid="5" data-flavor="mango" data-price="600">
            <div class="flavor-cake flavor-mango">
              <div class="flavor-cherry"></div>
              <div class="flavor-cake-drip"></div>
              <div class="flavor-cake-body"></div>
            </div>
            <h3>Mango</h3>
            <p class="price">₱600 base</p>
          </div>
        </div>
      </section>

      <br />

      <!-- Size -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">Choose Your Size</h2>
          <span class="price-tag" id="sizePriceTag">+₱0</span>
        </div>
        <p class="page-subtitle" style="margin-bottom: 20px">
          Priced relative to our standard 8-inch cake.
        </p>

        <div class="size-grid" id="sizeGrid">
          <button type="button" class="size-card" data-diameter="6" data-price="-100">
            <strong>6"</strong>
            <span>Serves 6–8</span>
          </button>
          <button type="button" class="size-card selected" data-diameter="8" data-price="0">
            <strong>8"</strong>
            <span>Serves 10–12</span>
          </button>
          <button type="button" class="size-card" data-diameter="10" data-price="250">
            <strong>10"</strong>
            <span>Serves 15–20</span>
          </button>
          <button type="button" class="size-card" data-diameter="12" data-price="500">
            <strong>12"</strong>
            <span>Serves 25+</span>
          </button>
        </div>
      </section>

      <br />

      <!-- Tiers -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">How Many Tiers?</h2>
          <span class="price-tag" id="tierPriceTag">+₱0</span>
        </div>
        <p class="page-subtitle" style="margin-bottom: 20px">
          Every tier past the first adds ₱300.
        </p>

        <div class="tier-stepper">
          <button type="button" id="tierMinus" aria-label="Remove a tier">
            −
          </button>
          <span class="tier-count" id="tierCount">1</span>
          <button type="button" id="tierPlus" aria-label="Add a tier">+</button>
        </div>
      </section>

      <br />

      <!-- Size Comparison Visualization -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">Visualize Your Cake</h2>
        </div>
        <p class="page-subtitle" style="margin-bottom: 10px">
          Scaled next to a standard 10-inch dinner plate for reference.
        </p>

        <div class="visual-stage">
          <div class="visual-plate" id="visualPlate"></div>
          <div class="visual-cake-stack" id="visualCakeStack"></div>
          <div class="visual-ruler" id="visualRuler"></div>
        </div>

        <div class="visual-readout" id="visualReadout"></div>
      </section>

      <br />

      <!-- Icing & Filling -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">Icing &amp; Filling</h2>
        </div>

        <div class="mode-toggle" id="modeToggle">
          <button type="button" class="active" data-mode="uniform">
            Same for all tiers
          </button>
          <button type="button" data-mode="perlayer">Different per tier</button>
        </div>

        <br />

        <!-- Uniform mode -->
        <div class="customization-grid" id="uniformLayer">
          <div class="form-group">
            <label>Icing</label>
            <select id="uniformIcing">
              <option>Buttercream</option>
              <option>Whipped Cream</option>
              <option>Chocolate Ganache</option>
              <option>Cream Cheese Frosting</option>
              <option>Fondant</option>
            </select>
          </div>

          <div class="form-group">
            <label>Filling</label>
            <select id="uniformFilling">
              <option>Chocolate Mousse</option>
              <option>Fresh Strawberries</option>
              <option>Vanilla Cream</option>
              <option>Cookies &amp; Cream</option>
              <option>Salted Caramel</option>
              <option>Blueberry Jam</option>
              <option>No Filling</option>
            </select>
          </div>
        </div>

        <!-- Per-layer mode -->
        <div id="perLayerContainer" style="display: none"></div>

        <div class="form-group" style="margin-top: 10px">
          <label>Dedication Message</label>
          <input
            type="text"
            id="dedicationMessage"
            maxlength="40"
            placeholder="Happy Birthday Anna!"
          />
        </div>
      </section>

      <br />

      <!-- Decorations -->
      <section class="card">
        <div class="section-header">
          <h2 class="section-title">Extra Decorations</h2>
        </div>

        <div class="checkbox-grid" id="decorationsGrid">
          <label>
            <input type="checkbox" data-price="50" />
            <span>Fresh Fruits <em>+₱50</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="30" />
            <span>Sprinkles <em>+₱30</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="70" />
            <span>Chocolate Drip <em>+₱70</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="120" />
            <span>Macarons <em>+₱120</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="150" />
            <span>Gold Leaf <em>+₱150</em></span>
          </label>
          <label>
            <input type="checkbox" data-price="100" />
            <span>Edible Flowers <em>+₱100</em></span>
          </label>
        </div>
      </section>

      <br />

      <!-- Price Summary -->
      <section class="card price-summary">
        <h2 class="section-title">Price Summary</h2>

        <div class="price-line">
          <span id="summaryFlavorLabel">Vanilla base</span>
          <span class="amount" id="summaryFlavorPrice">₱500</span>
        </div>
        <div class="price-line">
          <span id="summarySizeLabel">8" size</span>
          <span class="amount" id="summarySizePrice">+₱0</span>
        </div>
        <div class="price-line">
          <span id="summaryTierLabel">1 tier</span>
          <span class="amount" id="summaryTierPrice">+₱0</span>
        </div>
        <div class="price-line">
          <span>Icing &amp; Filling</span>
          <span class="amount" id="summaryCustomizationPrice">+₱0</span>
        </div>
        <div class="price-line">
          <span>Decorations</span>
          <span class="amount" id="summaryDecorPrice">+₱0</span>
        </div>
        <div class="price-line price-total">
          <span>Total</span>
          <span class="amount" id="summaryTotalPrice">₱500</span>
        </div>
      </section>

      <br />

      <div style="text-align: center">
        <button id="addToCartBtn" type="button" class="btn btn-primary">
          <?= $editItem ? 'Update Cake' : 'Add to Cart' ?>
        </button>
      </div>
    </main>

    <footer>
      <p>&copy; 2026 DOLCI</p>
    </footer>

    <script>
      // If the user arrived via cart.php's "Edit" link, this holds that
      // cart item's saved configuration so menu.js can pre-fill the form.
      const editItem = <?= $editItem ? json_encode($editItem) : 'null' ?>;
    </script>
    <script src="js/loader.js"></script>
    <script src="js/menu.js"></script>
  </body>
</html>