<?php
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../auth/database.php';

$message = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $stmt = $conn->prepare("UPDATE REVIEW SET RatingEmoji=?, ReviewText=? WHERE ReviewID=?");
    $stmt->bind_param("ssi", $_POST['RatingEmoji'], $_POST['ReviewText'], $_POST['ReviewID']);
    $stmt->execute();
    $stmt->close();
    $message = "Review updated successfully.";
}

if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM REVIEW WHERE ReviewID=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $stmt->close();
    header("Location: reviews.php?msg=" . urlencode("Review deleted."));
    exit;
}

$editReview = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM REVIEW WHERE ReviewID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editReview = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$search = trim($_GET['search'] ?? '');
$baseQuery = "SELECT r.*, c.CakeName, u.FullName FROM REVIEW r
              LEFT JOIN CAKE_MENU c ON r.CakeID = c.CakeID
              JOIN USER_ACCOUNT u ON r.UserID = u.UserID";
if ($search !== "") {
    $stmt = $conn->prepare($baseQuery . " WHERE c.CakeName LIKE ? OR u.FullName LIKE ? OR r.ReviewText LIKE ?
                            ORDER BY r.ReviewDate DESC");
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $reviews = $conn->query($baseQuery . " ORDER BY r.ReviewDate DESC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Reviews | DOLCI</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<nav>
    <a href="index.php" class="logo" style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--pink-bubble); padding: 0; letter-spacing: 0.02em;">DOLCI Admin</a>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="cake_menu.php">Cake Menu</a>
        <a href="orders.php">Orders</a>
        <a href="payments.php">Payments</a>
        <a href="reviews.php">Reviews</a>
        <a href="users.php">Customers</a>
        <a href="admins.php">Admin/Staff</a>
        <a href="../index.php">View Customer Site</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="page-container">
    <h1 class="page-title">Reviews</h1>
    <?php if ($message): ?><div class="admin-alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <?php if ($editReview): ?>
    <div class="card">
        <h2 class="section-title">Edit Review #<?= $editReview['ReviewID'] ?></h2>
        <form method="POST" action="reviews.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="ReviewID" value="<?= $editReview['ReviewID'] ?>">
            <div class="form-group">
                <label>Rating Emoji</label>
                <input type="text" name="RatingEmoji" value="<?= htmlspecialchars($editReview['RatingEmoji']) ?>" maxlength="10">
            </div>
            <div class="form-group">
                <label>Review Text</label>
                <input type="text" name="ReviewText" value="<?= htmlspecialchars($editReview['ReviewText']) ?>">
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Update Review</button>
            <a href="reviews.php" class="btn-small secondary">Cancel</a>
        </form>
    </div>
    <br>
    <?php endif; ?>

    <div class="card">
        <h2 class="section-title">Reviews List</h2>
        <p style="font-size:14px; color: var(--cocoa-soft);">Reviews are submitted by customers on the site. Admins can edit (moderate) or delete them here.</p>
        <form method="GET" action="reviews.php" style="display:flex; gap:10px; margin-bottom:15px;">
            <input type="text" name="search" placeholder="Search by cake, customer, or review text..." value="<?= htmlspecialchars($search) ?>" style="flex:1; padding:8px; border-radius: var(--radius-pill); border:1px solid var(--pink-soft);">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="reviews.php" class="btn-small secondary">Clear</a><?php endif; ?>
        </form>

        <table class="admin-table">
            <tr><th>ID</th><th>Cake</th><th>Customer</th><th>Rating</th><th>Review</th><th>Actions</th></tr>
            <?php if (count($reviews) === 0): ?><tr><td colspan="6">No reviews found.</td></tr><?php endif; ?>
            <?php foreach ($reviews as $r): ?>
            <tr>
                <td><?= $r['ReviewID'] ?></td>
                <td><?= $r['CakeName'] ? htmlspecialchars($r['CakeName']) : '🌐 Website Feedback' ?></td>
                <td><?= htmlspecialchars($r['FullName']) ?></td>
                <td><?= htmlspecialchars($r['RatingEmoji']) ?></td>
                <td><?= htmlspecialchars($r['ReviewText']) ?></td>
                <td>
                    <a href="reviews.php?edit=<?= $r['ReviewID'] ?>" class="btn-small secondary">Edit</a>
                    <a href="reviews.php?delete=<?= $r['ReviewID'] ?>" class="btn-small danger" onclick="return confirm('Delete this review?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
