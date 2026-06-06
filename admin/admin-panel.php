<?php require_once "auth-admin.php"; ?>
<?php include "admin-header.php"; ?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><title>Panel administratora</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="../normalize.css">
<link rel="stylesheet" href="../variables.css">
<link rel="stylesheet" href="../style.css">
</head>
<body >
  <h2 class="admin__title">Panel administratora</h2>

  <ul class="admin__list">
    <li class="admin__item"><a class="admin__link" href="admin-product.php">Produkty</a></li>
    <li class="admin__item"><a class="admin__link" href="admin-order.php">Zamówienia</a></li>
    <li class="admin__item"><a class="admin__link" href="admin-comment.php">Komentarze i oceny</a></li>
    <li class="admin__item"><a class="admin__link" href="admin-statystic.php">Statystyki</a></li>
    <li class="admin__item"><a class="admin__link" href="admin-category.php">Kategorie</a></li>
    <li class="admin__item"><a class="admin__link" href="admin-user.php">Przeglądaj użytkowników</a></li>
  </ul>

  <p class="admin__footer">
    <a class="admin__link" href="../index.php">Wróć do sklepu</a>
    <span class="admin__sep">|</span>
    <a class="action-button admin__logout" href="../logout.php">Wyloguj</a>
  </p>
</body>

</html>
