<?php
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../auth/database.php';

$message = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $hashed = password_hash($_POST['Password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO USER_ACCOUNT (FullName, Email, Password, ContactNumber) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $_POST['FullName'], $_POST['Email'], $hashed, $_POST['ContactNumber']);
    $stmt->execute();
    $stmt->close();
    $message = "Customer account created successfully.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    if (!empty($_POST['Password'])) {
        $hashed = password_hash($_POST['Password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE USER_ACCOUNT SET FullName=?, Email=?, Password=?, ContactNumber=? WHERE UserID=?");
        $stmt->bind_param("ssssi", $_POST['FullName'], $_POST['Email'], $hashed, $_POST['ContactNumber'], $_POST['UserID']);
    } else {
        $stmt = $conn->prepare("UPDATE USER_ACCOUNT SET FullName=?, Email=?, ContactNumber=? WHERE UserID=?");
        $stmt->bind_param("sssi", $_POST['FullName'], $_POST['Email'], $_POST['ContactNumber'], $_POST['UserID']);
    }
    $stmt->execute();
    $stmt->close();
    $message = "Customer account updated successfully.";
}

if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM USER_ACCOUNT WHERE UserID=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php?msg=" . urlencode("Customer account deleted."));
    exit;
}

$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM USER_ACCOUNT WHERE UserID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$search = trim($_GET['search'] ?? '');
if ($search !== "") {
    $stmt = $conn->prepare("SELECT * FROM USER_ACCOUNT WHERE FullName LIKE ? OR Email LIKE ? OR ContactNumber LIKE ? ORDER BY UserID DESC");
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $users = $conn->query("SELECT * FROM USER_ACCOUNT ORDER BY UserID DESC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Customers | DOLCI</title>
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
    <h1 class="page-title">Customer Accounts</h1>
    <?php if ($message): ?><div class="admin-alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="card">
        <h2 class="section-title"><?= $editUser ? "Edit Customer" : "Add New Customer" ?></h2>
        <form method="POST" action="users.php">
            <input type="hidden" name="action" value="<?= $editUser ? 'update' : 'create' ?>">
            <?php if ($editUser): ?><input type="hidden" name="UserID" value="<?= $editUser['UserID'] ?>"><?php endif; ?>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="FullName" required value="<?= htmlspecialchars($editUser['FullName'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="Email" required value="<?= htmlspecialchars($editUser['Email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="ContactNumber" value="<?= htmlspecialchars($editUser['ContactNumber'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password <?= $editUser ? '(leave blank to keep current)' : '' ?></label>
                <input type="password" name="Password" <?= $editUser ? '' : 'required' ?>>
            </div>

            <br>
            <button type="submit" class="btn btn-primary"><?= $editUser ? "Update Customer" : "Add Customer" ?></button>
            <?php if ($editUser): ?><a href="users.php" class="btn-small secondary">Cancel</a><?php endif; ?>
        </form>
    </div>

    <br>
    <div class="card">
        <h2 class="section-title">Customer List</h2>
        <form method="GET" action="users.php" style="display:flex; gap:10px; margin-bottom:15px;">
            <input type="text" name="search" placeholder="Search by name, email, or contact number..." value="<?= htmlspecialchars($search) ?>" style="flex:1; padding:8px; border-radius: var(--radius-pill); border:1px solid var(--pink-soft);">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="users.php" class="btn-small secondary">Clear</a><?php endif; ?>
        </form>

        <table class="admin-table">
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Contact</th><th>Registered</th><th>Actions</th></tr>
            <?php if (count($users) === 0): ?><tr><td colspan="6">No customers found.</td></tr><?php endif; ?>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['UserID'] ?></td>
                <td><?= htmlspecialchars($u['FullName']) ?></td>
                <td><?= htmlspecialchars($u['Email']) ?></td>
                <td><?= htmlspecialchars($u['ContactNumber']) ?></td>
                <td><?= $u['DateRegistered'] ?></td>
                <td>
                    <a href="users.php?edit=<?= $u['UserID'] ?>" class="btn-small secondary">Edit</a>
                    <a href="users.php?delete=<?= $u['UserID'] ?>" class="btn-small danger" onclick="return confirm('Delete this customer? This also removes their orders and reviews.');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
