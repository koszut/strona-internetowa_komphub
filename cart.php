<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once "db.php"; 

$cart = $_SESSION['cart'] ?? [];
if (empty($cart) || !is_array($cart)) {
    include 'header.php';
    echo "Koszyk jest pusty. <a href='index.php'>Wróć do sklepu</a>";
    exit;
}

$ids = array_map('intval', array_keys($cart));
$ids = array_filter($ids, fn($v) => $v > 0);
if (empty($ids)) {
    include 'header.php';
    echo "Koszyk jest pusty. <a href='index.php'>Wróć do sklepu</a>";
    exit;
}

$in = implode(',', $ids);
$sql = "SELECT product_id, nazwa, cena, zdjecie FROM produkty WHERE product_id IN ($in)";
$res = $conn->query($sql);
if (!$res) die("Błąd zapytania: " . $conn->error);

$products = [];
while ($r = $res->fetch_assoc()) $products[$r['product_id']] = $r;

include "header.php";
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Koszyk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="normalize.css">
<link rel="stylesheet" href="variables.css">
<link rel="stylesheet" href="style.css">
</head>
<body class="cart-body">
<h1 class="cart-title">Twój koszyk</h1>

<table class="cart-table">
<thead class="cart-header">
    <tr>
        <th>Zdjęcie</th>
        <th>Produkt</th>
        <th>Cena</th>
        <th>Ilość</th>
        <th>Razem</th>
        <th>Akcje</th>
    </tr>
</thead>
<tbody class="cart-body-rows">
<?php
$total = 0;
foreach ($cart as $pid => $qty) {
    $pid = (int)$pid; $qty = (int)$qty;
    if (!isset($products[$pid])) continue;
    $p = $products[$pid];
    $price = isset($p['cena']) ? (float)$p['cena'] : 0.0;
    $line = $price * $qty;
    $total += $line;

    $name = htmlspecialchars($p['nazwa'] ?? 'Produkt');
    $priceFmt = number_format($price,2,',',' ') . ' zł';
    $lineFmt = number_format($line,2,',',' ') . ' zł';
    $imgSrc = !empty($p['zdjecie']) ? htmlspecialchars($p['zdjecie']) : null;

    echo "<tr class='cart-row'>
            <td class='cart-cell cart-image-cell'>";
    if ($imgSrc) {
        echo "<img class='product-thumb' src='product-image/{$imgSrc}' alt='{$name}'>";
    } else {
        echo "<div class='no-image'>Brak zdjęcia</div>";
    }
    echo    "</td>
            <td class='cart-cell cart-name'>{$name}</td>
            <td class='cart-cell cart-price'>{$priceFmt}</td>
            <td class='cart-cell cart-qty'>{$qty}</td>
            <td class='cart-cell cart-line'>{$lineFmt}</td>
            <td class='cart-cell cart-actions'>
                <form class='cart-action-form' method='post' action='cart-action.php'>
                    <input type='hidden' name='id' value='{$pid}'>
                    <input type='hidden' name='akcja' value='plus'>
                    <button class='action-button action-plus' type='submit'>+</button>
                </form>
                <form class='cart-action-form' method='post' action='cart-action.php'>
                    <input type='hidden' name='id' value='{$pid}'>
                    <input type='hidden' name='akcja' value='minus'>
                    <button class='action-button action-minus' type='submit'>-</button>
                </form>
                <form class='cart-action-form' method='post' action='cart-action.php'>
                    <input type='hidden' name='id' value='{$pid}'>
                    <input type='hidden' name='akcja' value='usun'>
                    <button class='action-button action-remove' type='submit'>Usuń</button>
                </form>
            </td>
          </tr>";
}
?>
</tbody>
<tfoot class="cart-footer">
<tr>
    <td class="cart-cell" colspan="4"><strong>Razem</strong></td>
    <td class="cart-cell" colspan="2"><strong><?php echo number_format($total,2,',',' '); ?> zł</strong></td>
</tr>
</tfoot>
</table>

<div class="cart-actions-bottom">
    <a class="primary-button" href="index.php">Kontynuuj zakupy</a>
    <a class="primary-button" href="order.php">Złóż zamówienie</a>
</div>
</body>

</html>
