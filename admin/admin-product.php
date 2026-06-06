<?php
require_once "auth-admin.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = isset($_POST["product_id"]) ? (int)$_POST["product_id"] : 0;
    $nazwa = trim($_POST["nazwa"]);
    $opis = trim($_POST["opis"]);
    $cena = (float)$_POST["cena"];
    $kategoria = (int)$_POST["kategoria"];
    $ilosc = (int)$_POST["ilosc"];
    $aktywny = isset($_POST["aktywny"]) ? 1 : 0;
    $zdjecie = trim($_POST["zdjecie"]);

    if ($product_id > 0) {
        $stmt = $conn->prepare("UPDATE produkty SET nazwa=?, opis=?, zdjecie=?, cena=?, kategoria=?, ilosc=?, aktywny=? WHERE product_id=?");
        $stmt->bind_param("sssdiisi", $nazwa, $opis, $zdjecie, $cena, $kategoria, $ilosc, $aktywny, $product_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO produkty (nazwa, opis, zdjecie, cena, kategoria, ilosc, aktywny) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdiis", $nazwa, $opis, $zdjecie, $cena, $kategoria, $ilosc, $aktywny);
    }
    $stmt->execute();
    header("Location: admin-product.php");
    exit;
}

if (isset($_GET["delete"])) {
    $pid = (int)$_GET["delete"];

    
    $stmt = $conn->prepare("DELETE FROM zamowienia_zawartosc WHERE product_id=?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();

    
    $stmt = $conn->prepare("DELETE FROM produkty WHERE product_id=?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();

    header("Location: admin-product.php");
    exit;
}


$produkty = $conn->query("SELECT p.*, k.nazwa AS kategoria_nazwa FROM produkty p LEFT JOIN kategorie k ON p.kategoria = k.category_id ORDER BY p.data_utworzenia DESC");
$kategorie = $conn->query("SELECT category_id, nazwa FROM kategorie ORDER BY nazwa ASC");


$edytuj = null;
if (isset($_GET["edit"])) {
    $pid = (int)$_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM produkty WHERE product_id=?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $res = $stmt->get_result();
    $edytuj = $res->fetch_assoc();
}
?>
<?php include "admin-header.php"; ?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><title>Admin – Produkty</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="../normalize.css">
<link rel="stylesheet" href="../variables.css">
<link rel="stylesheet" href="../style.css">
</head>
<body>
<h2 class="admin-product__title">Produkty</h2>

<h3 class="admin-product__form-title"><?php echo $edytuj ? "Edytuj produkt" : "Dodaj produkt"; ?></h3>
<form method="post" class="admin-product__form">
    <?php if ($edytuj): ?>
        <input type="hidden" name="product_id" value="<?php echo $edytuj["product_id"]; ?>">
    <?php endif; ?>
    Nazwa: <input class="form__input" type="text" name="nazwa" value="<?php echo htmlspecialchars($edytuj["nazwa"] ?? ""); ?>" required><br>
    Opis: <br><textarea class="form__textarea" name="opis" required><?php echo htmlspecialchars($edytuj["opis"] ?? ""); ?></textarea><br>
    Zdjęcie (URL/ścieżka): <input class="form__input" type="text" name="zdjecie" value="<?php echo htmlspecialchars($edytuj["zdjecie"] ?? ""); ?>"><br>
    Cena: <input class="form__input" type="number" step="0.01" name="cena" value="<?php echo htmlspecialchars($edytuj["cena"] ?? ""); ?>" required><br>
    Kategoria:
    <select class="form__select" name="kategoria" required>
        <?php while ($k = $kategorie->fetch_assoc()): ?>
            <option value="<?php echo $k["category_id"]; ?>" <?php echo (($edytuj["kategoria"] ?? 0) == $k["category_id"]) ? "selected" : ""; ?>>
                <?php echo htmlspecialchars($k["nazwa"]); ?>
            </option>
        <?php endwhile; ?>
    </select><br>
    Ilość: <input class="form__input" type="number" name="ilosc" value="<?php echo htmlspecialchars($edytuj["ilosc"] ?? 0); ?>"><br>
    Aktywny: <input class="form__checkbox" type="checkbox" name="aktywny" <?php echo (($edytuj["aktywny"] ?? 1) == 1) ? "checked" : ""; ?>><br>
    <button type="submit" class="action-button admin-product__submit"><?php echo $edytuj ? "Zapisz zmiany" : "Dodaj produkt"; ?></button>
</form>

<hr class="divider">
<h3 class="admin-product__list-title">Lista produktów</h3>
<table class="admin-product__table" border="1" cellpadding="5">
<tr>
  <th class="admin-product__th">ID</th><th class="admin-product__th">Nazwa</th><th class="admin-product__th">Kategoria</th><th class="admin-product__th">Cena</th><th class="admin-product__th">Ilość</th><th class="admin-product__th">Aktywny</th><th class="admin-product__th">Akcje</th>
</tr>
<?php while ($p = $produkty->fetch_assoc()): ?>
<tr class="admin-product__row">
  <td class="admin-product__td"><?php echo $p["product_id"]; ?></td>
  <td class="admin-product__td"><?php echo htmlspecialchars($p["nazwa"]); ?></td>
  <td class="admin-product__td"><?php echo htmlspecialchars($p["kategoria_nazwa"]); ?></td>
  <td class="admin-product__td"><?php echo $p["cena"]; ?> zł</td>
  <td class="admin-product__td"><?php echo $p["ilosc"]; ?></td>
  <td class="admin-product__td"><?php echo $p["aktywny"] ? "tak" : "nie"; ?></td>
  <td class="admin-product__td">
    <a class="link-button admin-product__action" href="admin-product.php?edit=<?php echo $p["product_id"]; ?>">Edytuj</a> |
    <a class="link-button admin-product__action admin-product__delete" href="admin-product.php?delete=<?php echo $p["product_id"]; ?>" onclick="return confirm('Usunąć produkt?')">Usuń</a>
  </td>
</tr>
<?php endwhile; ?>
</table>
<p><a class="admin__link" href="admin-panel.php">Powrót do panelu</a></p>
</body>

</html>
