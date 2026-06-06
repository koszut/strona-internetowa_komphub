<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once "db.php";

$cart = $_SESSION['cart'] ?? [];

include "header.php";

if (empty($cart) || !is_array($cart)) {
    ?>
    <h2>Składanie zamówienia</h2>
    <p>Koszyk jest pusty. <a href="index.php">Wróć do sklepu</a></p>
    </body>
    </html>
    <?php
    exit;
}

$ids = array_map('intval', array_keys($cart));
$ids = array_filter($ids, fn($v) => $v > 0);
if (empty($ids)) {
    ?>
    <h2>Składanie zamówienia</h2>
    <p>Brak poprawnych produktów w koszyku. <a href="index.php">Wróć do sklepu</a></p>
    </body>
    </html>
    <?php
    exit;
}

$in = implode(',', $ids);
$sql = "SELECT product_id, nazwa, cena FROM produkty WHERE product_id IN ($in)";
$res = $conn->query($sql);
if (!$res) {
    ?>
    <h2>Składanie zamówienia</h2>
    <p>Błąd zapytania do bazy: <?php echo htmlspecialchars($conn->error); ?></p>
    </body>
    </html>
    <?php
    exit;
}

$products = [];
while ($r = $res->fetch_assoc()) {
    $products[(int)$r['product_id']] = $r;
}

$foundAny = false;
foreach ($ids as $id) {
    if (isset($products[$id])) { $foundAny = true; break; }
}
if (!$foundAny) {
    ?>
    <h2>Składanie zamówienia</h2>
    <p>Żaden z produktów w koszyku nie istnieje w sklepie. <a href="index.php">Wróć do sklepu</a></p>
    </body>
    </html>
    <?php
    exit;
}
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>Składanie zamówienia</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="normalize.css">
<link rel="stylesheet" href="variables.css">
<link rel="stylesheet" href="style.css">
</head>
<body >
  <h2 class="checkout-title">Składanie zamówienia</h2>

  <form class="checkout-form" method="post" action="checkout.php">
    <label class="label-address">Adres dostawy:</label>
    <textarea class="input-address" name="adres" required></textarea>

    <h3 class="summary-title">Podsumowanie koszyka:</h3>

    <table class="summary-table" cellpadding="5">
      <thead class="summary-head">
        <tr>
          <th>Produkt</th>
          <th>Cena</th>
          <th>Ilość</th>
          <th>Razem</th>
        </tr>
      </thead>
      <tbody class="summary-body">
        <?php
        $suma = 0.0;
        foreach ($cart as $pid => $qty) {
            $pid = (int)$pid;
            $qty = (int)$qty;
            if (!isset($products[$pid])) continue;
            $price = (float)$products[$pid]['cena'];
            $razem = $price * $qty;
            $suma += $razem;
            echo "<tr class='summary-row'>
                    <td class='summary-cell summary-name'>".htmlspecialchars($products[$pid]['nazwa'])."</td>
                    <td class='summary-cell summary-price'>".number_format($price,2,',',' ')." zł</td>
                    <td class='summary-cell summary-qty'>{$qty}</td>
                    <td class='summary-cell summary-line'>".number_format($razem,2,',',' ')." zł</td>
                  </tr>";
        }
        if ($suma <= 0) {
            echo "<tr class='summary-row'><td class='summary-cell' colspan='4'>Brak dostępnych produktów w koszyku. <a href='index.php'>Wróć do sklepu</a></td></tr>";
        } else {
            echo "<tr class='summary-row summary-total'><td class='summary-cell' colspan='3'><strong>Suma</strong></td><td class='summary-cell'><strong>".number_format($suma,2,',',' ')." zł</strong></td></tr>";
        }
        ?>
      </tbody>
    </table>

    <?php if ($suma > 0): ?>
      <div class="checkout-actions">
        <button class="primary-button" type="submit">Złóż zamówienie</button>
      </div>
    <?php else: ?>
      <p class="checkout-warning">Nie można złożyć zamówienia — sprawdź zawartość koszyka.</p>
    <?php endif; ?>
  </form>
</body>

</html>
