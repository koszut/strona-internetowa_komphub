<?php
require_once "auth-admin.php";


$limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 5;
if ($limit < 1) $limit = 5;

$stmt = $conn->prepare("SELECT p.product_id, p.nazwa, s.srednia_ocena, s.ilosc_ocen, p.cena, p.ilosc
                        FROM statystyki_oceny s
                        JOIN produkty p ON s.product_id = p.product_id
                        WHERE p.aktywny = 1
                        ORDER BY s.srednia_ocena DESC, s.ilosc_ocen DESC
                        LIMIT ?");
$stmt->bind_param("i", $limit);
$stmt->execute();
$top = $stmt->get_result();


$popular = $conn->query("SELECT p.product_id, p.nazwa, COUNT(z.product_id) AS kupione_razem
                         FROM zamowienia_zawartosc z
                         JOIN produkty p ON z.product_id = p.product_id
                         GROUP BY p.product_id, p.nazwa
                         ORDER BY kupione_razem DESC
                         LIMIT $limit");
?>
<?php include "admin-header.php"; ?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><title>Admin – Statystyki</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="../normalize.css">
<link rel="stylesheet" href="../variables.css">
<link rel="stylesheet" href="../style.css">
</head>
<body >
<h2 class="stats__title">Statystyki</h2>

<h3 class="stats__section-title">TOP <?php echo $limit; ?> najlepiej oceniane produkty</h3>
<div class="table-wrap">
<table class="stats__table stats__table--top-rated" border="1" cellpadding="5">
<tr>
  <th class="stats__th">ID</th><th class="stats__th">Nazwa</th><th class="stats__th">Średnia ocena</th><th class="stats__th">Liczba ocen</th><th class="stats__th">Cena</th><th class="stats__th">Dostępność</th>
</tr>
<?php while ($t = $top->fetch_assoc()): ?>
<tr class="stats__row">
  <td class="stats__td"><?php echo $t["product_id"]; ?></td>
  <td class="stats__td"><?php echo htmlspecialchars($t["nazwa"]); ?></td>
  <td class="stats__td"><?php echo $t["srednia_ocena"]; ?></td>
  <td class="stats__td"><?php echo $t["ilosc_ocen"]; ?></td>
  <td class="stats__td"><?php echo $t["cena"]; ?> zł</td>
  <td class="stats__td"><?php echo $t["ilosc"] > 0 ? "Dostępny" : "Brak"; ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<h3 class="stats__section-title">TOP <?php echo $limit; ?> najczęściej kupowane produkty</h3>
<div class="table-wrap">
<table class="stats__table stats__table--most-bought" border="1" cellpadding="5">
<tr>
  <th class="stats__th">ID</th><th class="stats__th">Nazwa</th><th class="stats__th">Liczba zakupów</th>
</tr>
<?php while ($p = $popular->fetch_assoc()): ?>
<tr class="stats__row">
  <td class="stats__td"><?php echo $p["product_id"]; ?></td>
  <td class="stats__td"><?php echo htmlspecialchars($p["nazwa"]); ?></td>
  <td class="stats__td"><?php echo $p["kupione_razem"]; ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<p class="stats__back"><a class="admin__link" href="admin-panel.php">Powrót do panelu</a></p>
</body>

</html>
