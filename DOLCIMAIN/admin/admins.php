<?php
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../auth/database.php';

$message = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $hashed = password_hash($_POST['Password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO ADMIN (AdminName, Email, Password, Role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $_POST['AdminName'], $_POST['Email'], $hashed, $_POST['Role']);
    $stmt->execute();
    $stmt->close();
    $message = "Admin/Staff account created successfully.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    if (!empty($_POST['Password'])) {
        $hashed = password_hash($_POST['Password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE ADMIN SET AdminName=?, Email=?, Password=?, Role=? WHERE AdminID=?");
        $stmt->bind_param("ssssi", $_POST['AdminName'], $_POST['Email'], $hashed, $_POST['Role'], $_POST['AdminID']);
    } else {
        $stmt = $conn->prepare("UPDATE ADMIN SET AdminName=?, Email=?, Role=? WHERE AdminID=?");
        $stmt->bind_param("sssi", $_POST['AdminName'], $_POST['Email'], $_POST['Role'], $_POST['AdminID']);
    }
    $stmt->execute();
    $stmt->close();
    $message = "Admin/Staff account updated successfully.";
}

if (isset($_GET['delete'])) {
    // Prevent an admin from deleting their own currently logged-in account
    if ((int)$_GET['delete'] === (int)$_SESSION['AdminID']) {
        $message = "You can't delete your own account while logged in.";
    } else {
        $stmt = $conn->prepare("DELETE FROM ADMIN WHERE AdminID=?");
        $stmt->bind_param("i", $_GET['delete']);
        $stmt->execute();
        $stmt->close();
        header("Location: admins.php?msg=" . urlencode("Admin/Staff account deleted."));
        exit;
    }
}

$editAdmin = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM ADMIN WHERE AdminID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editAdmin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$search = trim($_GET['search'] ?? '');
if ($search !== "") {
    $stmt = $conn->prepare("SELECT * FROM ADMIN WHERE AdminName LIKE ? OR Email LIKE ? OR Role LIKE ? ORDER BY AdminID DESC");
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $admins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $admins = $conn->query("SELECT * FROM ADMIN ORDER BY AdminID DESC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Admin/Staff | DOLCI</title>
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
    <h1 class="page-title">Admin/Staff Accounts</h1>
    <?php if ($message): ?><div class="admin-alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="card">
        <h2 class="section-title"><?= $editAdmin ? "Edit Admin/Staff" : "Add New Admin/Staff" ?></h2>
        <form method="POST" action="admins.php">
            <input type="hidden" name="action" value="<?= $editAdmin ? 'update' : 'create' ?>">
            <?php if ($editAdmin): ?><input type="hidden" name="AdminID" value="<?= $editAdmin['AdminID'] ?>"><?php endif; ?>

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="AdminName" required value="<?= htmlspecialchars($editAdmin['AdminName'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="Email" required value="<?= htmlspecialchars($editAdmin['Email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="Role">
                    <?php foreach (['Admin','Staff'] as $role): ?>
                        <option value="<?= $role ?>" <?= (isset($editAdmin) && $editAdmin['Role'] === $role) ? 'selected' : '' ?>><?= $role ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Password <?= $editAdmin ? '(leave blank to keep current)' : '' ?></label>
                <input type="password" name="Password" <?= $editAdmin ? '' : 'required' ?>>
            </div>

            <br>
            <button type="submit" class="btn btn-primary"><?= $editAdmin ? "Update Account" : "Add Account" ?></button>
            <?php if ($editAdmin): ?><a href="admins.php" class="btn-small secondary">Cancel</a><?php endif; ?>
        </form>
    </div>

    <br>
    <div class="card">
        <h2 class="section-title">Admin/Staff List</h2>
        <form method="GET" action="admins.php" style="display:flex; gap:10px; margin-bottom:15px;">
            <input type="text" name="search" placeholder="Search by name, email, or role..." value="<?= htmlspecialchars($search) ?>" style="flex:1; padding:8px; border-radius: var(--radius-pill); border:1px solid var(--pink-soft);">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="admins.php" class="btn-small secondary">Clear</a><?php endif; ?>
        </form>

        <table class="admin-table">
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
            <?php if (count($admins) === 0): ?><tr><td colspan="5">No accounts found.</td></tr><?php endif; ?>
            <?php foreach ($admins as $a): ?>
            <tr>
                <td><?= $a['AdminID'] ?></td>
                <td><?= htmlspecialchars($a['AdminName']) ?></td>
                <td><?= htmlspecialchars($a['Email']) ?></td>
                <td><?= htmlspecialchars($a['Role']) ?></td>
                <td>
                    <a href="admins.php?edit=<?= $a['AdminID'] ?>" class="btn-small secondary">Edit</a>
                    <a href="admins.php?delete=<?= $a['AdminID'] ?>" class="btn-small danger" onclick="return confirm('Delete this account?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
