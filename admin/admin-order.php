<?php
require_once "auth-admin.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = (int)$_POST["order_id"];
    $status = $_POST["status_zamowienia"];

    $stmt = $conn->prepare("UPDATE zamowienia SET status_zamowienia=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    header("Location: admin-order.php");
    exit;
}

$sql = "SELECT o.*, u.login AS user_login 
        FROM zamowienia o 
        JOIN uzytkownicy u ON o.user_id = u.user_id 
        ORDER BY o.data_zamowienia DESC";
$orders = $conn->query($sql);

$statuses = [
    'nowe', 'opłacone', 'w trakcie transportu', 'dostarczone', 'zakończone', 'anulowane'
];
?>
<?php include "admin-header.php"; ?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><title>Admin – Zamówienia</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="../normalize.css">
<link rel="stylesheet" href="../variables.css">
<link rel="stylesheet" href="../style.css">
</head>
<body >
  <div class=" admin-orders">

    <h2 class="admin-orders__title">Zamówienia</h2>

    <div class="table-wrap">
      <table class="admin-orders__table">
        <thead class="admin-orders__thead">
          <tr class="admin-orders__head-row">
            <th class="admin-orders__th">ID</th>
            <th class="admin-orders__th">Użytkownik</th>
            <th class="admin-orders__th">Kwota</th>
            <th class="admin-orders__th">Status</th>
            <th class="admin-orders__th">Data</th>
            <th class="admin-orders__th">Adres</th>
            <th class="admin-orders__th">Pozycje</th>
            <th class="admin-orders__th">Akcja</th>
          </tr>
        </thead>
        <tbody class="admin-orders__tbody">
        <?php while ($o = $orders->fetch_assoc()): ?>
        <tr class="admin-orders__row">
          <td class="admin-orders__td"><?php echo $o["order_id"]; ?></td>
          <td class="admin-orders__td"><?php echo htmlspecialchars($o["user_login"]); ?></td>
          <td class="admin-orders__td"><?php echo $o["kwota_suma"]; ?> zł</td>
          <td class="admin-orders__td"><?php echo $o["status_zamowienia"]; ?></td>
          <td class="admin-orders__td"><?php echo $o["data_zamowienia"]; ?></td>
          <td class="admin-orders__td"><?php echo nl2br(htmlspecialchars($o["adres_dostawy"])); ?></td>
          <td class="admin-orders__td admin-orders__items">
            <?php
              $stmt = $conn->prepare("SELECT z.ilosc, z.cena, p.nazwa FROM zamowienia_zawartosc z JOIN produkty p ON z.product_id = p.product_id WHERE z.order_id=?");
              $stmt->bind_param("i", $o["order_id"]);
              $stmt->execute();
              $items = $stmt->get_result();
              while ($it = $items->fetch_assoc()) {
                echo htmlspecialchars($it["nazwa"])." x".$it["ilosc"]." (".$it["cena"]." zł)<br>";
              }
            ?>
          </td>
          <td class="admin-orders__td admin-orders__actions">
            <form method="post" class="order-status__form">
              <input type="hidden" name="order_id" value="<?php echo $o["order_id"]; ?>">
              <label class="form__label form__label--inline">Status</label>
              <select name="status_zamowienia" class="form__select">
                <?php foreach ($statuses as $s): ?>
                  <option value="<?php echo $s; ?>" <?php echo ($o["status_zamowienia"] === $s) ? "selected" : ""; ?>>
                    <?php echo $s; ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="action-button order-status__submit">Zmień</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <p class="admin-orders__back"><a class="admin__link" href="admin-panel.php">Powrót do panelu</a></p>
  </div>
</body>

</html>
