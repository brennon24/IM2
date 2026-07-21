<?php
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../auth/database.php';

$message = $_GET['msg'] ?? '';

// ---------- CREATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $paymentDate = $_POST['PaymentDate'] !== '' ? str_replace('T', ' ', $_POST['PaymentDate']) . ':00' : null;
    $stmt = $conn->prepare("INSERT INTO PAYMENT (OrderID, Account, PaymentDate, PaymentMethod, PaymentStatus) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $_POST['OrderID'], $_POST['Account'], $paymentDate, $_POST['PaymentMethod'], $_POST['PaymentStatus']);
    $stmt->execute();
    $stmt->close();
    $message = "Payment recorded successfully.";
}

// ---------- UPDATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $paymentDate = $_POST['PaymentDate'] !== '' ? str_replace('T', ' ', $_POST['PaymentDate']) . ':00' : null;
    $stmt = $conn->prepare("UPDATE PAYMENT SET OrderID=?, Account=?, PaymentDate=?, PaymentMethod=?, PaymentStatus=? WHERE PaymentID=?");
    $stmt->bind_param("issssi", $_POST['OrderID'], $_POST['Account'], $paymentDate, $_POST['PaymentMethod'], $_POST['PaymentStatus'], $_POST['PaymentID']);
    $stmt->execute();
    $stmt->close();
    $message = "Payment updated successfully.";
}

// ---------- DELETE ----------
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM PAYMENT WHERE PaymentID=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $stmt->close();
    header("Location: payments.php?msg=" . urlencode("Payment deleted."));
    exit;
}

// ---------- Orders without a payment (for Create dropdown) ----------
$openOrders = $conn->query("SELECT o.OrderID, o.OrderCode, u.FullName FROM `ORDER` o
                            JOIN USER_ACCOUNT u ON o.CustomerID = u.UserID
                            LEFT JOIN PAYMENT p ON o.OrderID = p.OrderID
                            WHERE p.PaymentID IS NULL
                            ORDER BY o.OrderID DESC")->fetch_all(MYSQLI_ASSOC);

// ---------- If editing ----------
$editPayment = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM PAYMENT WHERE PaymentID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editPayment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ---------- READ + SEARCH ----------
$search = trim($_GET['search'] ?? '');
$baseQuery = "SELECT p.*, u.FullName AS CustomerName FROM PAYMENT p
              JOIN `ORDER` o ON p.OrderID = o.OrderID
              JOIN USER_ACCOUNT u ON o.CustomerID = u.UserID";
if ($search !== "") {
    $stmt = $conn->prepare($baseQuery . " WHERE u.FullName LIKE ? OR p.PaymentMethod LIKE ? OR p.PaymentStatus LIKE ? OR p.OrderID = ?
                            ORDER BY p.PaymentID DESC");
    $like = "%$search%";
    $orderIdSearch = is_numeric($search) ? (int)$search : -1;
    $stmt->bind_param("sssi", $like, $like, $like, $orderIdSearch);
    $stmt->execute();
    $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $payments = $conn->query($baseQuery . " ORDER BY p.PaymentID DESC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Payments | DOLCI</title>
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
    <h1 class="page-title">Payments</h1>
    <?php if ($message): ?><div class="admin-alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="card">
        <h2 class="section-title"><?= $editPayment ? "Edit Payment #" . $editPayment['PaymentID'] : "Record New Payment" ?></h2>
        <form method="POST" action="payments.php">
            <input type="hidden" name="action" value="<?= $editPayment ? 'update' : 'create' ?>">
            <?php if ($editPayment): ?><input type="hidden" name="PaymentID" value="<?= $editPayment['PaymentID'] ?>"><?php endif; ?>

            <div class="form-group">
                <label>Order</label>
                <?php if ($editPayment): ?>
                    <input type="text" value="Order #<?= $editPayment['OrderID'] ?>" disabled>
                    <input type="hidden" name="OrderID" value="<?= $editPayment['OrderID'] ?>">
                <?php else: ?>
                    <select name="OrderID" required>
                        <option value="">-- Select Order (unpaid only) --</option>
                        <?php foreach ($openOrders as $o): ?>
                            <option value="<?= $o['OrderID'] ?>">Order #<?= htmlspecialchars($o['OrderCode'] ?? $o['OrderID']) ?> — <?= htmlspecialchars($o['FullName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Account (e.g. GCash number, bank account)</label>
                <input type="text" name="Account" value="<?= htmlspecialchars($editPayment['Account'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Payment Date</label>
                <input type="datetime-local" name="PaymentDate" value="<?= $editPayment && $editPayment['PaymentDate'] ? str_replace(' ', 'T', substr($editPayment['PaymentDate'],0,16)) : '' ?>">
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <select name="PaymentMethod">
                    <?php foreach (['Cash on Delivery','Bank Transfer','QR'] as $method): ?>
                        <option value="<?= $method ?>" <?= (isset($editPayment) && $editPayment['PaymentMethod'] === $method) ? 'selected' : '' ?>><?= $method ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Payment Status</label>
                <select name="PaymentStatus">
                    <?php foreach (['Unpaid','Paid','Refunded'] as $status): ?>
                        <option value="<?= $status ?>" <?= (isset($editPayment) && $editPayment['PaymentStatus'] === $status) ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <br>
            <button type="submit" class="btn btn-primary"><?= $editPayment ? "Update Payment" : "Record Payment" ?></button>
            <?php if ($editPayment): ?><a href="payments.php" class="btn-small secondary">Cancel</a><?php endif; ?>
        </form>
    </div>

    <br>
    <div class="card">
        <h2 class="section-title">Payments List</h2>
        <form method="GET" action="payments.php" style="display:flex; gap:10px; margin-bottom:15px;">
            <input type="text" name="search" placeholder="Search by customer, method, status, or Order ID..." value="<?= htmlspecialchars($search) ?>" style="flex:1; padding:8px; border-radius: var(--radius-pill); border:1px solid var(--pink-soft);">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="payments.php" class="btn-small secondary">Clear</a><?php endif; ?>
        </form>

        <table class="admin-table">
            <tr><th>ID</th><th>Order</th><th>Customer</th><th>Method</th><th>Status</th><th>Actions</th></tr>
            <?php if (count($payments) === 0): ?><tr><td colspan="6">No payments found.</td></tr><?php endif; ?>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= $p['PaymentID'] ?></td>
                <td>#<?= htmlspecialchars($p['OrderCode'] ?? $p['OrderID']) ?></td>
                <td><?= htmlspecialchars($p['CustomerName']) ?></td>
                <td><?= htmlspecialchars($p['PaymentMethod']) ?></td>
                <td><?= htmlspecialchars($p['PaymentStatus']) ?></td>
                <td>
                    <a href="payments.php?edit=<?= $p['PaymentID'] ?>" class="btn-small secondary">Edit</a>
                    <a href="payments.php?delete=<?= $p['PaymentID'] ?>" class="btn-small danger" onclick="return confirm('Delete this payment?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
