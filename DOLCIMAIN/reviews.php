<?php
session_start();
require_once __DIR__ . '/auth/database.php';

$loggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['UserID']);
$userId = $_SESSION['UserID'] ?? $_SESSION['user_id'] ?? null;

$message = "";
$messageType = "success";

// ---------- Submit a new review ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!$loggedIn) {
        $message = "Please log in to leave a review.";
        $messageType = "error";
    } else {
        $reviewType = $_POST['ReviewType'] ?? 'cake';
        $ratingEmoji = trim($_POST['RatingEmoji'] ?? '');
        $reviewText = trim($_POST['ReviewText'] ?? '');

        if ($reviewType === 'website') {
            // General site feedback -- not tied to any specific cake
            if (!$ratingEmoji || !$reviewText) {
                $message = "Please choose a rating and write your feedback.";
                $messageType = "error";
            } else {
                $stmt = $conn->prepare("INSERT INTO REVIEW (CakeID, UserID, RatingEmoji, ReviewText) VALUES (NULL, ?, ?, ?)");
                $stmt->bind_param("iss", $userId, $ratingEmoji, $reviewText);
                $stmt->execute();
                $stmt->close();
                $message = "Thanks for your feedback about the site!";
                $messageType = "success";
            }
        } else {
            $cakeId = (int) ($_POST['CakeID'] ?? 0);

            if (!$cakeId || !$ratingEmoji || !$reviewText) {
                $message = "Please choose a cake, a rating, and write your review.";
                $messageType = "error";
            } else {
                // Confirm this customer actually has a completed order containing this cake
                $checkStmt = $conn->prepare("SELECT 1 FROM ORDER_ITEM oi
                                              JOIN `ORDER` o ON oi.OrderID = o.OrderID
                                              WHERE o.CustomerID = ? AND o.OrderStatus = 'Completed' AND oi.CakeID = ?
                                              LIMIT 1");
                $checkStmt->bind_param("ii", $userId, $cakeId);
                $checkStmt->execute();
                $eligible = $checkStmt->get_result()->num_rows > 0;
                $checkStmt->close();

                if (!$eligible) {
                    $message = "You can only review cakes from your completed orders.";
                    $messageType = "error";
                } else {
                    $stmt = $conn->prepare("INSERT INTO REVIEW (CakeID, UserID, RatingEmoji, ReviewText) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiss", $cakeId, $userId, $ratingEmoji, $reviewText);
                    $stmt->execute();
                    $stmt->close();
                    $message = "Thanks for your review!";
                    $messageType = "success";
                }
            }
        }
    }
}

// ---------- Cakes for the dropdown: only from THIS customer's completed orders ----------
$cakes = [];
if ($loggedIn) {
    $stmt = $conn->prepare("SELECT DISTINCT cm.CakeID, cm.CakeName
                            FROM ORDER_ITEM oi
                            JOIN `ORDER` o ON oi.OrderID = o.OrderID
                            JOIN CAKE_MENU cm ON oi.CakeID = cm.CakeID
                            WHERE o.CustomerID = ? AND o.OrderStatus = 'Completed'
                            ORDER BY cm.CakeName ASC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cakes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$preselectedCakeId = (int) ($_GET['cake_id'] ?? 0);

// ---------- All reviews, newest first ----------
$search = trim($_GET['search'] ?? '');
if ($search !== "") {
    $stmt = $conn->prepare("SELECT r.*, c.CakeName, u.FullName FROM REVIEW r
                            LEFT JOIN CAKE_MENU c ON r.CakeID = c.CakeID
                            JOIN USER_ACCOUNT u ON r.UserID = u.UserID
                            WHERE c.CakeName LIKE ? OR u.FullName LIKE ? OR r.ReviewText LIKE ?
                            ORDER BY r.ReviewDate DESC");
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $reviews = $conn->query("SELECT r.*, c.CakeName, u.FullName FROM REVIEW r
                              LEFT JOIN CAKE_MENU c ON r.CakeID = c.CakeID
                              JOIN USER_ACCOUNT u ON r.UserID = u.UserID
                              ORDER BY r.ReviewDate DESC")->fetch_all(MYSQLI_ASSOC);
}

$emojiOptions = ['😍', '😊', '👍', '😐', '😞'];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reviews | DOLCI</title>
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <nav>
      <a
        href="index.php"
        class="logo"
        style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;"
        >DOLCI</a
      >
      <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="order_history.php">Orders</a>
        <a href="cart.php">Cart</a>
        <a href="reviews.php" class="active">Reviews</a>
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
      <h1 class="page-title">Customer Reviews</h1>
      <p class="page-subtitle">See what fellow cake lovers are saying, or share your own experience.</p>

      <?php if ($message): ?>
        <div class="form-error" style="<?= $messageType === 'success' ? 'background:#e3ffe3;color:#2e8b2e;' : '' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <?php if ($loggedIn): ?>
      <div class="card" style="max-width: 700px; margin: 0 auto 40px;" id="leave-review">
        <h2 class="section-title">Leave a Review</h2>

        <form method="POST" action="reviews.php" id="reviewForm">
          <input type="hidden" name="action" value="submit_review">

          <div class="form-group">
            <label>What's this review about?</label>
            <div style="display:flex; gap:14px; flex-wrap:wrap;">
              <label style="display:flex; align-items:center; gap:6px; background:var(--cream); border:2px solid var(--pink-soft); border-radius:16px; padding:10px 16px; cursor:pointer; font-weight:700;">
                <input type="radio" name="ReviewType" value="cake" id="typeCake" checked
                       style="accent-color: var(--pink-bubble); transform: scale(1.2);"
                       <?= count($cakes) === 0 ? 'disabled' : '' ?>>
                A Cake I Ordered
              </label>
              <label style="display:flex; align-items:center; gap:6px; background:var(--cream); border:2px solid var(--pink-soft); border-radius:16px; padding:10px 16px; cursor:pointer; font-weight:700;">
                <input type="radio" name="ReviewType" value="website" id="typeWebsite"
                       style="accent-color: var(--pink-bubble); transform: scale(1.2);"
                       <?= count($cakes) === 0 ? 'checked' : '' ?>>
                The Website Overall
              </label>
            </div>
          </div>

          <div class="form-group" id="cakeSelectGroup" style="<?= count($cakes) === 0 ? 'display:none;' : '' ?>">
            <?php if (count($cakes) === 0): ?>
              <p style="color: var(--cocoa-soft); margin:0;">
                You can review a cake once one of your orders is marked <strong>Completed</strong>.
                Check your <a href="order_history.php" style="color: var(--pink-bubble); font-weight:700;">Order History</a>.
              </p>
            <?php else: ?>
              <label>Which cake?</label>
              <select name="CakeID">
                <option value="">-- Select a cake --</option>
                <?php foreach ($cakes as $cake): ?>
                  <option value="<?= $cake['CakeID'] ?>" <?= $preselectedCakeId === (int)$cake['CakeID'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cake['CakeName']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label>How was it?</label>
            <div style="display:flex; gap:14px; flex-wrap:wrap;">
              <?php foreach ($emojiOptions as $emoji): ?>
                <label style="display:flex; align-items:center; gap:6px; background:var(--cream); border:2px solid var(--pink-soft); border-radius:16px; padding:10px 16px; cursor:pointer; font-weight:700; font-size:1.4rem;">
                  <input type="radio" name="RatingEmoji" value="<?= $emoji ?>" required style="accent-color: var(--pink-bubble); transform: scale(1.2);">
                  <?= $emoji ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-group">
            <label>Your Review</label>
            <textarea name="ReviewText" rows="4" placeholder="Tell us what you thought..." required></textarea>
          </div>

          <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
      </div>
      <?php else: ?>
        <div class="card" style="max-width: 700px; margin: 0 auto 40px; text-align:center;">
          <p><a href="login.php" style="color: var(--pink-bubble); font-weight:700;">Log in</a> to leave your own review.</p>
        </div>
      <?php endif; ?>

      <h2 class="section-title" style="text-align:center;">All Reviews</h2>
      <form method="GET" action="reviews.php" style="display:flex; gap:10px; max-width:700px; margin:0 auto 30px;">
        <input type="text" name="search" placeholder="Search by cake, name, or review text..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-secondary" style="white-space:nowrap;">Search</button>
      </form>

      <div style="display:flex; flex-direction:column; gap:20px; max-width:700px; margin:0 auto;">
        <?php if (count($reviews) === 0): ?>
          <p style="text-align:center; color: var(--cocoa-soft);">No reviews yet. Be the first to share one!</p>
        <?php endif; ?>
        <?php foreach ($reviews as $r): ?>
          <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
              <div>
                <strong style="color: var(--pink-bubble); font-family: var(--font-display); font-size:1.1rem;">
                  <?= $r['CakeName'] ? htmlspecialchars($r['CakeName']) : '🌐 About the Website' ?>
                </strong>
                <div style="font-size:0.85rem; color: var(--cocoa-soft);">
                  by <?= htmlspecialchars($r['FullName']) ?> · <?= date('M j, Y', strtotime($r['ReviewDate'])) ?>
                </div>
              </div>
              <div style="font-size:1.8rem;"><?= htmlspecialchars($r['RatingEmoji']) ?></div>
            </div>
            <p style="margin:0;"><?= nl2br(htmlspecialchars($r['ReviewText'])) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </main>

    <script>
      const typeCake = document.getElementById('typeCake');
      const typeWebsite = document.getElementById('typeWebsite');
      const cakeSelectGroup = document.getElementById('cakeSelectGroup');

      function updateReviewTypeUI() {
        if (!cakeSelectGroup) return;
        if (typeWebsite && typeWebsite.checked) {
          cakeSelectGroup.style.display = 'none';
        } else {
          cakeSelectGroup.style.display = '';
        }
      }
      typeCake?.addEventListener('change', updateReviewTypeUI);
      typeWebsite?.addEventListener('change', updateReviewTypeUI);
      updateReviewTypeUI();
    </script>

    <footer>
      <p>&copy; 2026 DOLCI</p>
    </footer>
  </body>
</html>
