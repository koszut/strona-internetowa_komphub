<?php
require_once "auth-admin.php";
require_once "../db.php";

if (isset($_GET["delete"])) {
    $uid = (int)$_GET["delete"];

    $stmt = $conn->prepare("DELETE FROM zamowienia_zawartosc WHERE order_id IN (SELECT order_id FROM zamowienia WHERE user_id=?)");
    $stmt->bind_param("i", $uid);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM zamowienia WHERE user_id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM uzytkownicy WHERE user_id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();

    header("Location: admin-user.php");
    exit;
}

$users = $conn->query("SELECT user_id, login, email, rola, data_utworzenia FROM uzytkownicy ORDER BY data_utworzenia DESC");

include "admin-header.php";
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Admin – Użytkownicy</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="../normalize.css">
<link rel="stylesheet" href="../variables.css">
<link rel="stylesheet" href="../style.css">
</head>
<body >
<h2 class="users__title">Lista użytkowników</h2>

<div class="table-wrap">
<table class="users__table" border="1" cellpadding="5">
<tr>
  <th class="users__th">ID</th><th class="users__th">Login</th><th class="users__th">Email</th><th class="users__th">Rola</th><th class="users__th">Data rejestracji</th><th class="users__th">Akcje</th>
</tr>
<?php while ($u = $users->fetch_assoc()): ?>
<tr class="users__row">
  <td class="users__td"><?php echo $u["user_id"]; ?></td>
  <td class="users__td"><?php echo htmlspecialchars($u["login"]); ?></td>
  <td class="users__td"><?php echo htmlspecialchars($u["email"]); ?></td>
  <td class="users__td"><?php echo htmlspecialchars($u["rola"]); ?></td>
  <td class="users__td"><?php echo $u["data_utworzenia"]; ?></td>
  <td class="users__td users__actions">
    <a class="link-button users__delete" href="admin-user.php?delete=<?php echo $u["user_id"]; ?>" onclick="return confirm('Usunąć użytkownika i jego dane?')">Usuń</a>
  </td>
</tr>
<?php endwhile; ?>
</table>
</div>

<p class="users__back"><a class="admin__link" href="admin-panel.php">Powrót do panelu</a></p>
</body>

</html>
