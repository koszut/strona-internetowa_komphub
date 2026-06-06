<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>SinSky</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="../normalize.css">
<link rel="stylesheet" href="../variables.css">
<link rel="stylesheet" href="../style.css">
</head>
<body>

<nav>
  <img src="../other-image/logo.png" alt="">
  <a href="../index.php">Strona główna</a>
  <a href="../cart.php">Koszyk</a>

  <?php if (!isset($_SESSION['user_id'])): ?>
      <a href="../login.php">Logowanie</a>
      <a href="../register.php">Rejestracja</a>
  <?php else: ?>
      <a href="../logout.php">Wyloguj</a>
      <a href="../my-account.php" class="primary-button">Moje konto</a>
      <?php if (isset($_SESSION['rola']) && $_SESSION['rola'] === 'admin'): ?>
          <a href="admin-panel.php" class="primary-button">Panel admina</a>
      <?php endif; ?>
  <?php endif; ?>
</nav>
<div class="container">
