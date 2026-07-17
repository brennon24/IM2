<?php
// Include this at the very top of every protected admin page.
// Blocks access unless a valid admin session exists.
session_start();

if (!isset($_SESSION['AdminID'])) {
    header("Location: login.php");
    exit;
}
