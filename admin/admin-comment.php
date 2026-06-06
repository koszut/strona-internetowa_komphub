<?php
require_once "auth-admin.php";

if (isset($_GET["delete"])) {
    $rid = (int)$_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM oceny WHERE review_id=?");
    $stmt->bind_param("i", $rid);
    $stmt->execute();

    if (isset($_GET["product_id"])) {
        $pid = (int)$_GET["product_id"];
        $stmt = $conn->prepare("SELECT AVG(ocena) AS avg_ocena, COUNT(*) AS cnt FROM oceny WHERE product_id=?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $avg = $data["avg_ocena"] ?? 0;
        $cnt = $data["cnt"] ?? 0;
        $stmt = $conn->prepare("INSERT INTO statystyki_oceny (product_id, srednia_ocena, ilosc_ocen) VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE srednia_ocena=VALUES(srednia_ocena), ilosc_ocen=VALUES(ilosc_ocen)");
        $stmt->bind_param("idi", $pid, $avg, $cnt);
        $stmt->execute();
    }

    header("Location: admin-comment.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $review_id = (int)$_POST["review_id"];
    $ocena = (int)$_POST["ocena"];
    $komentarz = trim($_POST["komentarz"]);
    $stmt = $conn->prepare("UPDATE oceny SET ocena=?, komentarz=? WHERE review_id=?");
    $stmt->bind_param("isi", $ocena, $komentarz, $review_id);
    $stmt->execute();

    $stmt = $conn->prepare("SELECT product_id FROM oceny WHERE review_id=?");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row) {
        $pid = (int)$row["product_id"];
        $stmt = $conn->prepare("SELECT AVG(ocena) AS avg_ocena, COUNT(*) AS cnt FROM oceny WHERE product_id=?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $avg = $data["avg_ocena"] ?? 0;
        $cnt = $data["cnt"] ?? 0;
        $stmt = $conn->prepare("INSERT INTO statystyki_oceny (product_id, srednia_ocena, ilosc_ocen) VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE srednia_ocena=VALUES(srednia_ocena), ilosc_ocen=VALUES(ilosc_ocen)");
        $stmt->bind_param("idi", $pid, $avg, $cnt);
        $stmt->execute();
    }

    header("Location: admin-comment.php");
    exit;
}

$sql = "SELECT o.review_id, o.product_id, o.ocena, o.komentarz, o.kiedy_dodany, u.login AS autor, p.nazwa AS produkt
        FROM oceny o
        JOIN uzytkownicy u ON o.user_id=u.user_id
        JOIN produkty p ON o.product_id=p.product_id
        ORDER BY o.kiedy_dodany DESC";
$reviews = $conn->query($sql);
?>
<?php include "admin-header.php"; ?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><title>Admin – Komentarze</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="../normalize.css">
<link rel="stylesheet" href="../variables.css">
<link rel="stylesheet" href="../style.css">
</head>
<body >
  <div class=" reviews">

    <h2 class="reviews__title">Komentarze i oceny</h2>

    <div class="table-wrap">
      <table class="reviews__table">
        <thead class="reviews__thead">
          <tr class="reviews__head-row">
            <th class="reviews__th">ID</th>
            <th class="reviews__th">Produkt</th>
            <th class="reviews__th">Autor</th>
            <th class="reviews__th">Ocena</th>
            <th class="reviews__th">Komentarz</th>
            <th class="reviews__th">Data</th>
            <th class="reviews__th">Akcje</th>
          </tr>
        </thead>
        <tbody class="reviews__tbody">
        <?php while ($r = $reviews->fetch_assoc()): ?>
        <tr class="reviews__row">
          <td class="reviews__td"><?php echo $r["review_id"]; ?></td>
          <td class="reviews__td"><?php echo htmlspecialchars($r["produkt"]); ?></td>
          <td class="reviews__td"><?php echo htmlspecialchars($r["autor"]); ?></td>
          <td class="reviews__td reviews__td--center"><?php echo $r["ocena"]; ?>/5</td>
          <td class="reviews__td"><?php echo nl2br(htmlspecialchars($r["komentarz"])); ?></td>
          <td class="reviews__td"><?php echo $r["kiedy_dodany"]; ?></td>
          <td class="reviews__td reviews__actions">
            <form method="post" class="review__form">
              <input type="hidden" name="review_id" value="<?php echo $r["review_id"]; ?>">
              <label class="form__label form__label--inline">Ocena:</label>
              <select name="ocena" class="form__select">
                <?php for ($i=1; $i<=5; $i++): ?>
                  <option value="<?php echo $i; ?>" <?php echo ($r["ocena"]==$i)?"selected":""; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
              </select>

              <label class="form__label">Komentarz:</label>
              <textarea name="komentarz" rows="3" class="form__input"><?php echo htmlspecialchars($r["komentarz"]); ?></textarea>

              <div class="review__form-actions">
                <button type="submit" class="action-button">Zapisz</button>
              </div>
            </form>

            <a class="link-button reviews__delete" href="komentarze.php?delete=<?php echo $r["review_id"]; ?>&product_id=<?php echo $r["product_id"]; ?>" onclick="return confirm('Usunąć komentarz?')">Usuń</a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <p class="reviews__back"><a class="admin__link" href="admin-panel.php">Powrót do panelu</a></p>
  </div>
</body>

</html>
