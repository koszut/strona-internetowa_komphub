<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once "db.php"; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$akcja = $_POST['akcja'] ?? '';

if ($id <= 0 || !isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

switch ($akcja) {
    case 'plus':
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
        break;

    case 'minus':
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = max(1, $_SESSION['cart'][$id] - 1);
        }
        break;

    case 'usun':
        unset($_SESSION['cart'][$id]);
        if (empty($_SESSION['cart'])) unset($_SESSION['cart']);
        break;

    default:

        break;
}

header("Location: cart.php");
exit;
