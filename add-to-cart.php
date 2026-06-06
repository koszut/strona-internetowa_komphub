<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once "db.php"; 

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT 1 FROM produkty WHERE product_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {

        header("Location: index.php");
        exit;
    }
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;

header("Location: cart.php");
exit;
