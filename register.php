<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    $login = trim($_POST["login"]);
    $haslo = $_POST["haslo"];
    $haslo2 = $_POST["haslo2"];

if (empty($_POST["email"]) || empty($login) || empty($haslo) || empty($haslo2)) {
    $_SESSION["error"] = "Wszystkie pola są wymagane!";
    header("Location: register.php");
    exit;
}

$email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
if ($email === false) {
    $_SESSION["error"] = "Wprowadź prawidłowe dane.";
    header("Location: register.php");
    exit;
}

    if ($haslo !== $haslo2) {
        $_SESSION["error"] = "Hasła muszą być identyczne.";
        header("Location: register.php");
        exit;
    }


    $maDuze = preg_match('/[A-Z]/', $haslo);
    $maCyfre = preg_match('/[0-9]/', $haslo);
    $maSpecjalny = preg_match('/[\W]/', $haslo);
    $maDlugosc = strlen($haslo) >= 8;

    if (!$maDuze || !$maCyfre || !$maSpecjalny || !$maDlugosc) {
        $_SESSION["error"] = "Hasło musi mieć co najmniej 8 znaków, zawierać jedną dużą literę, jedną cyfrę i jeden znak specjalny.";
        header("Location: register.php");
        exit;
    }


    $stmt = $conn->prepare("SELECT user_id FROM uzytkownicy WHERE email = ? OR login = ?");
    $stmt->bind_param("ss", $email, $login);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $_SESSION["error"] = "Użytkownik z takim emailem lub loginem już istnieje.";
        header("Location: register.php");
        exit;
    }
    $stmt->close();


    $haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO uzytkownicy (email, haslo_hash, login) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $haslo_hash, $login);

    if ($stmt->execute()) {
        $_SESSION["success"] = "Rejestracja zakończona sukcesem. Możesz się zalogować.";
        header("Location: login.php");
        exit;
    } else {
        $_SESSION["error"] = "Błąd: " . $stmt->error;
        header("Location: register.php");
        exit;
    }
}
?>
<?php include "header.php"; ?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Rejestracja</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="normalize.css">
<link rel="stylesheet" href="variables.css">
<link rel="stylesheet" href="style.css">

</head>
<body >
  <h2 class="registration__title">Rejestracja</h2>

  <?php
  if (isset($_SESSION["error"])) {
      echo '<p class="registration__message registration__message--error">' . htmlspecialchars($_SESSION["error"]) . '</p>';
      unset($_SESSION["error"]);
  }
  if (isset($_SESSION["success"])) {
      echo '<p class="registration__message registration__message--success">' . htmlspecialchars($_SESSION["success"]) . '</p>';
      unset($_SESSION["success"]);
  }
  ?>

  <form method="post" novalidate class="registration__form">
      <label class="form__label">Email:</label>
      <input class="form__input" type="email" name="email" required><br>

      <label class="form__label">Login:</label>
      <input class="form__input" type="text" name="login" required><br>

      <label class="form__label">Hasło:</label>
      <input
          class="form__input"
          type="password"
          name="haslo"
          required
          pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[\W]).{8,}"
          title="Minimum 8 znaków, co najmniej jedna duża litera, jedna cyfra i jeden znak specjalny"
      ><br>

      <label class="form__label">Powtórz hasło:</label>
      <input class="form__input" type="password" name="haslo2" required><br>

      <small class="form__note">Hasło musi mieć minimum 8 znaków, zawierać jedną dużą literę, jedną cyfrę i jeden znak specjalny.</small><br><br>

      <button type="submit" class="action-button">Zarejestruj</button>
  </form>
</body>

</html>
