<?php
require_once "auth-admin.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_id = isset($_POST["category_id"]) ? (int)$_POST["category_id"] : 0;
    $nazwa = trim($_POST["nazwa"]);

    if ($category_id > 0) {
        $stmt = $conn->prepare("UPDATE kategorie SET nazwa=? WHERE category_id=?");
        $stmt->bind_param("si", $nazwa, $category_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO kategorie (nazwa) VALUES (?)");
        $stmt->bind_param("s", $nazwa);
    }
    $stmt->execute();
    header("Location: admin-category.php");
    exit;
}

if (isset($_GET["delete"])) {
    $cid = (int)$_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM kategorie WHERE category_id=?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    header("Location: admin-category.php");
    exit;
}

$kategorie = $conn->query("SELECT * FROM kategorie ORDER BY nazwa ASC");

$edytuj = null;
if (isset($_GET["edit"])) {
    $cid = (int)$_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM kategorie WHERE category_id=?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $res = $stmt->get_result();
    $edytuj = $res->fetch_assoc();
}
?>
<?php include "admin-header.php"; ?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Admin – Kategorie</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="../normalize.css">
<link rel="stylesheet" href="../variables.css">
<link rel="stylesheet" href="../style.css">
</head>
<body>
  <div class=" admin-category">

    <h2 class="admin-category__title">Kategorie produktów</h2>

    <h3 class="admin-category__form-title"><?php echo $edytuj ? "Edytuj kategorię" : "Dodaj kategorię"; ?></h3>
    <form method="post" class="admin-category__form">
        <?php if ($edytuj): ?>
            <input type="hidden" name="category_id" value="<?php echo $edytuj["category_id"]; ?>">
        <?php endif; ?>
        <label class="form__label">Nazwa:</label>
        <input class="form__input" type="text" name="nazwa" value="<?php echo htmlspecialchars($edytuj["nazwa"] ?? ""); ?>" required>
        <button type="submit" class="action-button admin-category__submit"><?php echo $edytuj ? "Zapisz zmiany" : "Dodaj"; ?></button>
    </form>

    <hr class="divider">

    <h3 class="admin-category__list-title">Lista kategorii</h3>
    <div class="table-wrap">
      <table class="admin-category__table">
        <thead class="admin-category__thead">
          <tr>
            <th class="admin-category__th">ID</th>
            <th class="admin-category__th">Nazwa</th>
            <th class="admin-category__th">Akcje</th>
          </tr>
        </thead>
        <tbody class="admin-category__tbody">
        <?php while ($k = $kategorie->fetch_assoc()): ?>
        <tr class="admin-category__row">
          <td class="admin-category__td"><?php echo $k["category_id"]; ?></td>
          <td class="admin-category__td"><?php echo htmlspecialchars($k["nazwa"]); ?></td>
          <td class="admin-category__td">
            <a class="link-button admin-category__action" href="admin-category.php?edit=<?php echo $k["category_id"]; ?>">Edytuj</a>
            <span class="admin-category__sep">|</span>
            <a class="link-button admin-category__action admin-category__delete" href="admin-category.php?delete=<?php echo $k["category_id"]; ?>" onclick="return confirm('Usunąć kategorię?')">Usuń</a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <p class="admin-category__back"><a class="admin__link" href="admin-panel.php">Powrót do panelu
</html>
