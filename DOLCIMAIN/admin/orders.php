<?php
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../auth/database.php';

$message = $_GET['msg'] ?? '';

// ---------- CREATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $adminId = $_POST['AdminID'] !== '' ? (int)$_POST['AdminID'] : null;
    $stmt = $conn->prepare("INSERT INTO `ORDER` (CustomerID, CustomNote, OrderStatus, AdminID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $_POST['CustomerID'], $_POST['CustomNote'], $_POST['OrderStatus'], $adminId);
    $stmt->execute();
    $stmt->close();
    $message = "Order created successfully.";
}

// ---------- UPDATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $adminId = $_POST['AdminID'] !== '' ? (int)$_POST['AdminID'] : null;
    $stmt = $conn->prepare("UPDATE `ORDER` SET CustomerID=?, CustomNote=?, OrderStatus=?, AdminID=? WHERE OrderID=?");
    $stmt->bind_param("issii", $_POST['CustomerID'], $_POST['CustomNote'], $_POST['OrderStatus'], $adminId, $_POST['OrderID']);
    $stmt->execute();
    $stmt->close();
    $message = "Order updated successfully.";
}

// ---------- DELETE ----------
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM `ORDER` WHERE OrderID=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $stmt->close();
    header("Location: orders.php?msg=" . urlencode("Order deleted."));
    exit;
}

// ---------- Dropdown data ----------
$customers = $conn->query("SELECT UserID, FullName FROM USER_ACCOUNT ORDER BY FullName")->fetch_all(MYSQLI_ASSOC);
$admins = $conn->query("SELECT AdminID, AdminName FROM ADMIN ORDER BY AdminName")->fetch_all(MYSQLI_ASSOC);

// ---------- If editing ----------
$editOrder = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM `ORDER` WHERE OrderID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editOrder = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ---------- View line items ----------
$viewItems = null;
$viewOrderId = $_GET['view'] ?? null;
if ($viewOrderId) {
    $stmt = $conn->prepare("SELECT ol.*, cm.CakeName FROM ORDERLIST ol
                            JOIN CAKE_MENU cm ON ol.CakeID = cm.CakeID
                            WHERE ol.OrderID = ?");
    $stmt->bind_param("i", $viewOrderId);
    $stmt->execute();
    $viewItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ---------- READ + SEARCH ----------
$search = trim($_GET['search'] ?? '');
$baseQuery = "SELECT o.*, u.FullName AS CustomerName, a.AdminName
              FROM `ORDER` o
              JOIN USER_ACCOUNT u ON o.CustomerID = u.UserID
              LEFT JOIN ADMIN a ON o.AdminID = a.AdminID";
if ($search !== "") {
    $stmt = $conn->prepare($baseQuery . " WHERE u.FullName LIKE ? OR o.OrderStatus LIKE ? OR o.OrderID = ?
                            ORDER BY o.OrderID DESC");
    $like = "%$search%";
    $orderIdSearch = is_numeric($search) ? (int)$search : -1;
    $stmt->bind_param("ssi", $like, $like, $orderIdSearch);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $orders = $conn->query($baseQuery . " ORDER BY o.OrderID DESC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Orders | DOLCI</title>
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
        <a href="../index.html">View Customer Site</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="page-container">
    <h1 class="page-title">Orders</h1>
    <?php if ($message): ?><div class="admin-alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="card">
        <h2 class="section-title"><?= $editOrder ? "Edit Order #" . $editOrder['OrderID'] : "Create New Order" ?></h2>
        <form method="POST" action="orders.php">
            <input type="hidden" name="action" value="<?= $editOrder ? 'update' : 'create' ?>">
            <?php if ($editOrder): ?><input type="hidden" name="OrderID" value="<?= $editOrder['OrderID'] ?>"><?php endif; ?>

            <div class="form-group">
                <label>Customer</label>
                <select name="CustomerID" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['UserID'] ?>" <?= (isset($editOrder) && $editOrder['CustomerID'] == $c['UserID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['FullName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Assigned Admin/Staff</label>
                <select name="AdminID">
                    <option value="">-- Unassigned --</option>
                    <?php foreach ($admins as $a): ?>
                        <option value="<?= $a['AdminID'] ?>" <?= (isset($editOrder) && $editOrder['AdminID'] == $a['AdminID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['AdminName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Order Status</label>
                <select name="OrderStatus">
                    <?php foreach (['Pending','Confirmed','Preparing','Completed','Cancelled'] as $status): ?>
                        <option value="<?= $status ?>" <?= (isset($editOrder) && $editOrder['OrderStatus'] === $status) ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Custom Note</label>
                <input type="text" name="CustomNote" value="<?= htmlspecialchars($editOrder['CustomNote'] ?? '') ?>">
            </div>

            <br>
            <button type="submit" class="btn btn-primary"><?= $editOrder ? "Update Order" : "Create Order" ?></button>
            <?php if ($editOrder): ?><a href="orders.php" class="btn-small secondary">Cancel</a><?php endif; ?>
        </form>
    </div>

    <br>
    <div class="card">
        <h2 class="section-title">Orders List</h2>
        <form method="GET" action="orders.php" style="display:flex; gap:10px; margin-bottom:15px;">
            <input type="text" name="search" placeholder="Search by customer, status, or Order ID..." value="<?= htmlspecialchars($search) ?>" style="flex:1; padding:8px; border-radius: var(--radius-pill); border:1px solid var(--pink-soft);">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="orders.php" class="btn-small secondary">Clear</a><?php endif; ?>
        </form>

        <table class="admin-table">
            <tr><th>ID</th><th>Customer</th><th>Date</th><th>Status</th><th>Assigned To</th><th>Actions</th></tr>
            <?php if (count($orders) === 0): ?><tr><td colspan="6">No orders found.</td></tr><?php endif; ?>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= $o['OrderID'] ?></td>
                <td><?= htmlspecialchars($o['CustomerName']) ?></td>
                <td><?= $o['OrderDate'] ?></td>
                <td><?= htmlspecialchars($o['OrderStatus']) ?></td>
                <td><?= htmlspecialchars($o['AdminName'] ?? 'Unassigned') ?></td>
                <td>
                    <a href="orders.php?view=<?= $o['OrderID'] ?>" class="btn-small secondary">Items</a>
                    <a href="orders.php?edit=<?= $o['OrderID'] ?>" class="btn-small secondary">Edit</a>
                    <a href="orders.php?delete=<?= $o['OrderID'] ?>" class="btn-small danger" onclick="return confirm('Delete this order?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php if ($viewItems !== null): ?>
    <br>
    <div class="card">
        <h2 class="section-title">Items in Order #<?= htmlspecialchars($viewOrderId) ?></h2>
        <table class="admin-table">
            <tr><th>Cake</th><th>Quantity</th><th>Cake Text</th><th>Layers</th><th>Price</th></tr>
            <?php if (count($viewItems) === 0): ?><tr><td colspan="5">No items found.</td></tr><?php endif; ?>
            <?php foreach ($viewItems as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['CakeName']) ?></td>
                <td><?= $item['Quantity'] ?></td>
                <td><?= htmlspecialchars($item['CakeText'] ?? '') ?></td>
                <td><?= $item['Layers'] ?></td>
                <td>₱<?= number_format($item['EntirePrice'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
