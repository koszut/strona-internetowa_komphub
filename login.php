<?php
// login.php
ini_set('display_errors',1);
error_reporting(E_ALL);

session_start();
require_once "db.php"; // $conn jako mysqli

// jeśli już zalogowany -> index
if (!empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identyfikator = trim($_POST['identyfikator'] ?? '');
    $haslo = $_POST['haslo'] ?? '';

    if ($identyfikator === '' || $haslo === '') {
        $_SESSION['error'] = "Podaj login/email i hasło.";
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT user_id, haslo_hash, login, rola FROM uzytkownicy WHERE email = ? OR login = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['error'] = "Błąd serwera. Spróbuj później.";
        header("Location: login.php");
        exit;
    }
    $stmt->bind_param("ss", $identyfikator, $identyfikator);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($haslo, $user['haslo_hash'])) {
      
            $_SESSION['user_id'] = (int)$user['user_id'];
            $_SESSION['login'] = $user['login'];
            $_SESSION['rola'] = $user['rola'];

         
            header("Location: index.php");
            exit;
        }
    }

    $_SESSION['error'] = "Niepoprawne dane logowania.";
    header("Location: login.php");
    exit;
}


?>
<?php include "header.php"; ?>
<!doctype html>
<html lang="pl">
<head><meta charset="utf-8"><title>Logowanie</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="normalize.css">
<link rel="stylesheet" href="variables.css">
<link rel="stylesheet" href="style.css"></head>
<body>
  <h2 class="login__title">Logowanie</h2>

  <?php
  if (!empty($_SESSION['error'])) {
      echo '<p class="login__error">' . htmlspecialchars($_SESSION['error']) . '</p>';
      unset($_SESSION['error']);
  }
  ?>

  <form method="post" action="login.php" class="login__form">
      <label class="form__label">Login lub Email:</label>
      <input class="form__input" type="text" name="identyfikator" required><br>

      <label class="form__label">Hasło:</label>
      <input class="form__input" type="password" name="haslo" required><br>

      <button type="submit" class="action-button">Zaloguj</button>
  </form>
</body>
</html>
