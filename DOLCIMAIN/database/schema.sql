DROP DATABASE IF EXISTS dolci_db;
CREATE DATABASE dolci_db;
USE dolci_db;
 
-- =========================================================
-- USER ACCOUNT
-- =========================================================
CREATE TABLE USER_ACCOUNT (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    ContactNumber VARCHAR(20),
    DateRegistered DATETIME DEFAULT CURRENT_TIMESTAMP
);
 
-- =========================================================
-- ADMIN
-- =========================================================
CREATE TABLE ADMIN (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    AdminName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role VARCHAR(50) NOT NULL -- e.g. 'Admin', 'Staff'
);
 
-- =========================================================
-- CAKE MENU
-- =========================================================
CREATE TABLE CAKE_MENU (
    CakeID INT AUTO_INCREMENT PRIMARY KEY,
    CakeName VARCHAR(100) NOT NULL,
    Flavor VARCHAR(50),
    Filling VARCHAR(50),
    Size VARCHAR(50),
    Price DECIMAL(10,2) NOT NULL,
    FeaturedCake BOOLEAN DEFAULT FALSE,
    Availability BOOLEAN DEFAULT TRUE,
    CakeTier VARCHAR(50) -- e.g. 'Regular', 'Premium'
);
 
-- =========================================================
-- ORDER
-- (FK: CustomerID -> USER_ACCOUNT, AdminID -> ADMIN)
-- =========================================================
CREATE TABLE `ORDER` (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL,
    OrderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CustomNote TEXT,
    OrderStatus VARCHAR(50) DEFAULT 'Pending', -- Pending, Confirmed, Preparing, Completed, Cancelled
    AdminID INT,
    FOREIGN KEY (CustomerID) REFERENCES USER_ACCOUNT(UserID)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (AdminID) REFERENCES ADMIN(AdminID)
        ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE CART (
    CartID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    CakeID INT NOT NULL,
    Flavor VARCHAR(50),
    Layers INT NOT NULL DEFAULT 1,
    Icing VARCHAR(100),
    Filling VARCHAR(100),
    Decorations TEXT,
    CakeText VARCHAR(150),
    Quantity INT DEFAULT 1,
    TotalPrice DECIMAL(10,2),
    DateAdded DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (UserID) REFERENCES USER_ACCOUNT(UserID),
    FOREIGN KEY (CakeID) REFERENCES CAKE_MENU(CakeID)
);
 
-- =========================================================
-- ORDERLIST (line items of an order)
-- (FK: CakeID -> CAKE_MENU, OrderID -> ORDER)
-- Includes CakeText (message on cake) and Layers (customization)
-- =========================================================
CREATE TABLE ORDERLIST (
    OrderListID INT AUTO_INCREMENT PRIMARY KEY,
    CakeID INT NOT NULL,
    OrderID INT NOT NULL,
    Quantity INT NOT NULL DEFAULT 1,
    CakeText VARCHAR(150), -- text/message to write on the cake
    Layers INT NOT NULL DEFAULT 1, -- number of cake layers/tiers
    EntirePrice DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (CakeID) REFERENCES CAKE_MENU(CakeID)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (OrderID) REFERENCES `ORDER`(OrderID)
        ON UPDATE CASCADE ON DELETE CASCADE
);
 
-- =========================================================
-- PAYMENT (one-to-one with ORDER per ERD)
-- =========================================================
CREATE TABLE PAYMENT (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT NOT NULL UNIQUE, -- UNIQUE enforces the 1:1 relationship
    Account VARCHAR(100),
    PaymentDate DATETIME,
    PaymentMethod VARCHAR(50), -- Cash on Delivery, Bank Transfer, QR
    PaymentStatus VARCHAR(50) DEFAULT 'Unpaid', -- Unpaid, Paid, Refunded
    FOREIGN KEY (OrderID) REFERENCES `ORDER`(OrderID)
        ON UPDATE CASCADE ON DELETE CASCADE
);
 
-- =========================================================
-- REVIEW
-- (FK: CakeID -> CAKE_MENU, UserID -> USER_ACCOUNT)
-- =========================================================
CREATE TABLE REVIEW (
    ReviewID INT AUTO_INCREMENT PRIMARY KEY,
    CakeID INT NOT NULL,
    UserID INT NOT NULL,
    RatingEmoji VARCHAR(10), -- e.g. store emoji code or 1-5 mapped to emoji
    ReviewText TEXT,
    ReviewDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CakeID) REFERENCES CAKE_MENU(CakeID)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (UserID) REFERENCES USER_ACCOUNT(UserID)
        ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO CAKE_MENU (CakeName, Flavor, Filling, Size, Price)
VALUES
('Vanilla Cake','Vanilla','Vanilla Cream','8 inch',500),
('Chocolate Cake','Chocolate','Chocolate Mousse','8 inch',550),
('Strawberry Cake','Strawberry','Fresh Strawberries','8 inch',600),
('Red Velvet Cake','Red Velvet','Cream Cheese','8 inch',650),
('Mango Cake','Mango','Mango Cream','8 inch',600);
 
-- =========================================================
-- OPTIONAL: Sample seed data
-- Uncomment the lines below if you want sample cakes/accounts
-- pre-loaded when teammates import this schema.
-- =========================================================
 
-- INSERT INTO USER_ACCOUNT (FullName, Email, Password, ContactNumber) VALUES
-- ('Maria Santos', 'maria@email.com', '$2y$10$examplehashedpassword', '09171234567');
 
-- INSERT INTO ADMIN (AdminName, Email, Password, Role) VALUES
-- ('Jared Gomora', 'jared@dolci.com', '$2y$10$examplehashedpassword', 'Admin');
 
-- INSERT INTO CAKE_MENU (CakeName, Flavor, Filling, Size, Price, FeaturedCake, Availability, CakeTier) VALUES
-- ('Chocolate Truffle', 'Chocolate', 'Chocolate Ganache', '8 inch', 850.00, 1, 1, 'Premium');