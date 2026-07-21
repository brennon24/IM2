<?php
 $host = "localhost";
 $user = "root";
 $password = "";
 $database = "dolci_db";

 $conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

 $conn->query("CREATE DATABASE IF NOT EXISTS `{$database}`");
 $conn->select_db($database);

function generateUniqueOrderCode($conn) {
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $code = str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        $check = $conn->prepare("SELECT 1 FROM `ORDER` WHERE OrderCode = ?");
        $check->bind_param("s", $code);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if (!$exists) {
            return $code;
        }
    }

    throw new RuntimeException("Unable to generate a unique order code.");
}

 $schemaStatements = [
    "CREATE TABLE IF NOT EXISTS USER_ACCOUNT (
        UserID INT AUTO_INCREMENT PRIMARY KEY,
        FullName VARCHAR(100) NOT NULL,
        Email VARCHAR(100) NOT NULL UNIQUE,
        Password VARCHAR(255) NOT NULL,
        ContactNumber VARCHAR(20),
        DateRegistered DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS ADMIN (
        AdminID INT AUTO_INCREMENT PRIMARY KEY,
        AdminName VARCHAR(100) NOT NULL,
        Email VARCHAR(100) NOT NULL UNIQUE,
        Password VARCHAR(255) NOT NULL,
        Role VARCHAR(50) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS CAKE_MENU (
        CakeID INT AUTO_INCREMENT PRIMARY KEY,
        CakeName VARCHAR(100) NOT NULL,
        Flavor VARCHAR(50),
        Filling VARCHAR(50),
        Size VARCHAR(50),
        Price DECIMAL(10,2) NOT NULL,
        FeaturedCake BOOLEAN DEFAULT FALSE,
        Availability BOOLEAN DEFAULT TRUE,
        CakeTier VARCHAR(50)
    )",
    "CREATE TABLE IF NOT EXISTS CART (
        CartID INT AUTO_INCREMENT PRIMARY KEY,
        UserID INT NOT NULL,
        CakeID INT NOT NULL,
        Flavor VARCHAR(50),
        Layers INT NOT NULL DEFAULT 1,
        Icing TEXT,
        Filling TEXT,
        Decorations TEXT,
        CakeText VARCHAR(150),
        Quantity INT DEFAULT 1,
        TotalPrice DECIMAL(10,2),
        DateAdded DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (UserID) REFERENCES USER_ACCOUNT(UserID),
        FOREIGN KEY (CakeID) REFERENCES CAKE_MENU(CakeID)
    )",
    "CREATE TABLE IF NOT EXISTS `ORDER` (
        OrderID INT AUTO_INCREMENT PRIMARY KEY,
        OrderCode VARCHAR(10) NOT NULL UNIQUE,
        CustomerID INT NOT NULL,
        OrderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
        CustomNote TEXT,
        OrderStatus VARCHAR(50) DEFAULT 'Pending',
        PaymentMethod VARCHAR(50) DEFAULT 'Cash on Delivery',
        AdminID INT,
        FOREIGN KEY (CustomerID) REFERENCES USER_ACCOUNT(UserID)
            ON UPDATE CASCADE ON DELETE RESTRICT,
        FOREIGN KEY (AdminID) REFERENCES ADMIN(AdminID)
            ON UPDATE CASCADE ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS ORDER_ITEM (
        OrderItemID INT AUTO_INCREMENT PRIMARY KEY,
        OrderID INT NOT NULL,
        CakeID INT NOT NULL,
        Flavor VARCHAR(50),
        Layers INT DEFAULT 1,
        Icing TEXT,
        Filling TEXT,
        Decorations TEXT,
        CakeText VARCHAR(150),
        Quantity INT DEFAULT 1,
        TotalPrice DECIMAL(10,2),
        FOREIGN KEY (OrderID) REFERENCES `ORDER`(OrderID) ON DELETE CASCADE,
        FOREIGN KEY (CakeID) REFERENCES CAKE_MENU(CakeID)
    )",
    "CREATE TABLE IF NOT EXISTS PAYMENT (
        PaymentID INT AUTO_INCREMENT PRIMARY KEY,
        OrderID INT NOT NULL UNIQUE,
        Account VARCHAR(100),
        PaymentDate DATETIME,
        PaymentMethod VARCHAR(50),
        PaymentStatus VARCHAR(50) DEFAULT 'Unpaid',
        FOREIGN KEY (OrderID) REFERENCES `ORDER`(OrderID)
            ON UPDATE CASCADE ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS REVIEW (
        ReviewID INT AUTO_INCREMENT PRIMARY KEY,
        CakeID INT NULL,
        UserID INT NOT NULL,
        RatingEmoji VARCHAR(10),
        ReviewText TEXT,
        ReviewDate DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (CakeID) REFERENCES CAKE_MENU(CakeID)
            ON UPDATE CASCADE ON DELETE CASCADE,
        FOREIGN KEY (UserID) REFERENCES USER_ACCOUNT(UserID)
            ON UPDATE CASCADE ON DELETE CASCADE
    )"
];

foreach ($schemaStatements as $sql) {
    $conn->query($sql);
}

$ordersTableCheck = $conn->query("SHOW COLUMNS FROM `ORDER` LIKE 'OrderCode'");
if ($ordersTableCheck && $ordersTableCheck->num_rows == 0) {
    $conn->query("ALTER TABLE `ORDER` ADD COLUMN OrderCode VARCHAR(10) NOT NULL DEFAULT '' AFTER OrderID");
}

$ordersWithoutCode = $conn->query("SELECT OrderID FROM `ORDER` WHERE OrderCode = ''");
if ($ordersWithoutCode) {
    while ($row = $ordersWithoutCode->fetch_assoc()) {
        $code = generateUniqueOrderCode($conn);
        $stmt = $conn->prepare("UPDATE `ORDER` SET OrderCode = ? WHERE OrderID = ?");
        $stmt->bind_param("si", $code, $row['OrderID']);
        $stmt->execute();
        $stmt->close();
    }
}

$uniqueOrderCodeIndex = $conn->query("SHOW INDEX FROM `ORDER` WHERE Column_name = 'OrderCode'");
if ($uniqueOrderCodeIndex && $uniqueOrderCodeIndex->num_rows == 0) {
    $conn->query("ALTER TABLE `ORDER` ADD UNIQUE INDEX OrderCode (OrderCode)");
}

$reviewTableCheck = $conn->query("SHOW TABLES LIKE 'REVIEW'");
if ($reviewTableCheck && $reviewTableCheck->num_rows > 0) {
    $conn->query("ALTER TABLE REVIEW MODIFY CakeID INT NULL");
}

 $menuCheck = $conn->query("SELECT COUNT(*) AS count FROM CAKE_MENU");
if ($menuCheck && $menuCheck->fetch_assoc()['count'] == 0) {
    $conn->query("INSERT INTO CAKE_MENU (CakeName, Flavor, Filling, Size, Price) VALUES
        ('Vanilla Cake','Vanilla','Vanilla Cream','8 inch',500),
        ('Chocolate Cake','Chocolate','Chocolate Mousse','8 inch',550),
        ('Strawberry Cake','Strawberry','Fresh Strawberries','8 inch',600),
        ('Red Velvet Cake','Red Velvet','Cream Cheese','8 inch',650),
        ('Mango Cake','Mango','Mango Cream','8 inch',600)");
}

$adminCheck = $conn->query("SELECT COUNT(*) AS count FROM ADMIN");
if ($adminCheck && $adminCheck->fetch_assoc()['count'] == 0) {
    $defaultAdminName = 'Administrator';
    $defaultAdminEmail = 'admin@dolci.com';
    $defaultAdminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
    $defaultAdminRole = 'Admin';

    $stmt = $conn->prepare("INSERT INTO ADMIN (AdminName, Email, Password, Role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $defaultAdminName, $defaultAdminEmail, $defaultAdminPasswordHash, $defaultAdminRole);
    $stmt->execute();
    $stmt->close();
}
?>