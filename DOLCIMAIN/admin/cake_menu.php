<?php
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../auth/database.php';

$message = "";

// ---------- CREATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $stmt = $conn->prepare("INSERT INTO CAKE_MENU (CakeName, Flavor, Filling, Size, Price, FeaturedCake, Availability, CakeTier)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $featured = isset($_POST['FeaturedCake']) ? 1 : 0;
    $available = isset($_POST['Availability']) ? 1 : 0;
    $stmt->bind_param("ssssdiis", $_POST['CakeName'], $_POST['Flavor'], $_POST['Filling'], $_POST['Size'],
        $_POST['Price'], $featured, $available, $_POST['CakeTier']);
    $stmt->execute();
    $stmt->close();
    $message = "Cake added successfully.";
}

// ---------- UPDATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $stmt = $conn->prepare("UPDATE CAKE_MENU SET CakeName=?, Flavor=?, Filling=?, Size=?, Price=?, FeaturedCake=?, Availability=?, CakeTier=?
                            WHERE CakeID=?");
    $featured = isset($_POST['FeaturedCake']) ? 1 : 0;
    $available = isset($_POST['Availability']) ? 1 : 0;
    $stmt->bind_param("ssssdiisi", $_POST['CakeName'], $_POST['Flavor'], $_POST['Filling'], $_POST['Size'],
        $_POST['Price'], $featured, $available, $_POST['CakeTier'], $_POST['CakeID']);
    $stmt->execute();
    $stmt->close();
    $message = "Cake updated successfully.";
}

// ---------- DELETE ----------
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM CAKE_MENU WHERE CakeID=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $stmt->close();
    header("Location: cake_menu.php?msg=" . urlencode("Cake deleted."));
    exit;
}
if (isset($_GET['msg'])) { $message = $_GET['msg']; }

// ---------- If editing ----------
$editCake = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM CAKE_MENU WHERE CakeID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editCake = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ---------- READ + SEARCH ----------
$search = trim($_GET['search'] ?? '');
if ($search !== "") {
    $stmt = $conn->prepare("SELECT * FROM CAKE_MENU
                            WHERE CakeName LIKE ? OR Flavor LIKE ? OR Filling LIKE ? OR CakeTier LIKE ?
                            ORDER BY CakeID DESC");
    $like = "%$search%";
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $cakes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $cakes = $conn->query("SELECT * FROM CAKE_MENU ORDER BY CakeID DESC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Cake Menu | DOLCI</title>
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
    <h1 class="page-title">Cake Menu</h1>

    <?php if ($message): ?><div class="admin-alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="card">
        <h2 class="section-title"><?= $editCake ? "Edit Cake" : "Add New Cake" ?></h2>
        <form method="POST" action="cake_menu.php">
            <input type="hidden" name="action" value="<?= $editCake ? 'update' : 'create' ?>">
            <?php if ($editCake): ?><input type="hidden" name="CakeID" value="<?= $editCake['CakeID'] ?>"><?php endif; ?>

            <div class="form-group">
                <label>Cake Name</label>
                <input type="text" name="CakeName" required value="<?= htmlspecialchars($editCake['CakeName'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Flavor</label>
                <input type="text" name="Flavor" value="<?= htmlspecialchars($editCake['Flavor'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Filling</label>
                <input type="text" name="Filling" value="<?= htmlspecialchars($editCake['Filling'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Size</label>
                <input type="text" name="Size" value="<?= htmlspecialchars($editCake['Size'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.01" name="Price" required value="<?= htmlspecialchars($editCake['Price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Cake Tier</label>
                <input type="text" name="CakeTier" value="<?= htmlspecialchars($editCake['CakeTier'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="FeaturedCake" style="width:auto;display:inline;" <?= (!empty($editCake['FeaturedCake'])) ? 'checked' : '' ?>> Featured Cake</label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="Availability" style="width:auto;display:inline;" <?= (!isset($editCake) || !empty($editCake['Availability'])) ? 'checked' : '' ?>> Available</label>
            </div>

            <br>
            <button type="submit" class="btn btn-primary"><?= $editCake ? "Update Cake" : "Add Cake" ?></button>
            <?php if ($editCake): ?><a href="cake_menu.php" class="btn-small secondary">Cancel</a><?php endif; ?>
        </form>
    </div>

    <br>
    <div class="card">
        <h2 class="section-title">Cake List</h2>
        <form method="GET" action="cake_menu.php" style="display:flex; gap:10px; margin-bottom:15px;">
            <input type="text" name="search" placeholder="Search by name, flavor, filling, tier..." value="<?= htmlspecialchars($search) ?>" style="flex:1; padding:8px; border-radius: var(--radius-pill); border:1px solid var(--pink-soft);">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="cake_menu.php" class="btn-small secondary">Clear</a><?php endif; ?>
        </form>

        <table class="admin-table">
            <tr><th>ID</th><th>Name</th><th>Flavor</th><th>Size</th><th>Price</th><th>Tier</th><th>Available</th><th>Actions</th></tr>
            <?php if (count($cakes) === 0): ?><tr><td colspan="8">No cakes found.</td></tr><?php endif; ?>
            <?php foreach ($cakes as $cake): ?>
            <tr>
                <td><?= $cake['CakeID'] ?></td>
                <td><?= htmlspecialchars($cake['CakeName']) ?></td>
                <td><?= htmlspecialchars($cake['Flavor']) ?></td>
                <td><?= htmlspecialchars($cake['Size']) ?></td>
                <td>₱<?= number_format($cake['Price'], 2) ?></td>
                <td><?= htmlspecialchars($cake['CakeTier']) ?></td>
                <td><?= $cake['Availability'] ? 'Yes' : 'No' ?></td>
                <td>
                    <a href="cake_menu.php?edit=<?= $cake['CakeID'] ?>" class="btn-small secondary">Edit</a>
                    <a href="cake_menu.php?delete=<?= $cake['CakeID'] ?>" class="btn-small danger" onclick="return confirm('Delete this cake?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
