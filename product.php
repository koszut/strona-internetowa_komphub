<?php
session_start();
require_once "db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT p.*, k.nazwa AS kategoria 
                        FROM produkty p 
                        LEFT JOIN kategorie k ON p.kategoria = k.category_id 
                        WHERE p.product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Produkt nie istnieje.";
    exit;
}
$produkt = $result->fetch_assoc();

$stmt = $conn->prepare("SELECT srednia_ocena, ilosc_ocen 
                        FROM statystyki_oceny 
                        WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$stat = $res->fetch_assoc();
$srednia = $stat ? $stat['srednia_ocena'] : 0;
$ilosc_ocen = $stat ? $stat['ilosc_ocen'] : 0;

$stmt = $conn->prepare("SELECT o.review_id, o.ocena, o.komentarz, o.kiedy_dodany, u.login 
                        FROM oceny o 
                        JOIN uzytkownicy u ON o.user_id = u.user_id 
                        WHERE o.product_id = ? 
                        ORDER BY o.kiedy_dodany DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$komentarze = $stmt->get_result();
?>
<?php include "header.php"; ?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($produkt['nazwa']); ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="normalize.css">
<link rel="stylesheet" href="variables.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php
if (isset($_SESSION['error_msg'])) {
    echo "<p class='msg-error'>" . htmlspecialchars($_SESSION['error_msg']) . "</p>";
    unset($_SESSION['error_msg']);
}
if (isset($_SESSION['success_msg'])) {
    echo "<p class='msg-success'>" . htmlspecialchars($_SESSION['success_msg']) . "</p>";
    unset($_SESSION['success_msg']);
}
?>

<div class="product-container">
    <div class="product-image">
        <?php if ($produkt['zdjecie']): ?>
            <img src="product-image/<?php echo htmlspecialchars($produkt['zdjecie']); ?>" alt="Zdjęcie produktu">
        <?php else: ?>
            <p>Brak zdjęcia</p>
        <?php endif; ?>
    </div>

    <div class="product-info">
        <h2><?php echo htmlspecialchars($produkt['nazwa']); ?></h2>
        <p><?php echo htmlspecialchars($produkt['opis']); ?></p>
        <p class="price">Cena: <?php echo $produkt['cena']; ?> zł</p>
        <p>Kategoria: <?php echo htmlspecialchars($produkt['kategoria']); ?></p>
        <p>Dostępność: <?php echo $produkt['ilosc'] > 0 ? "Dostępny" : "Brak w magazynie"; ?></p>
        <p><a class="primary-button" href="add-to-cart.php?id=<?php echo $produkt['product_id']; ?>">Dodaj do koszyka</a></p>
        <h3>Średnia ocena: <?php echo $srednia; ?> (<?php echo $ilosc_ocen; ?> ocen)</h3>
    </div>
</div>

<div class="comments-block">
    <h3>Komentarze:</h3>
    <?php
    if ($komentarze->num_rows === 0) {
        echo "<p>Brak komentarzy.</p>";
    } else {
        while ($row = $komentarze->fetch_assoc()) {
            echo "<div class='comment'>";
            echo "<strong>".htmlspecialchars($row['login'])."</strong> ";
            echo "ocenił na ".$row['ocena']."/5<br>";
            echo "<em>".htmlspecialchars($row['komentarz'])."</em><br>";
            echo "<small>".$row['kiedy_dodany']."</small>";
            echo "</div>";
        }
    }
    ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <h3>Dodaj ocenę i komentarz</h3>
        <form method="post" action="add-comment.php" class="comment-form">
            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
            Ocena:
            <select name="ocena" required>
                <option value="1">1 ★</option>
                <option value="2">2 ★★</option>
                <option value="3">3 ★★★</option>
                <option value="4">4 ★★★★</option>
                <option value="5">5 ★★★★★</option>
            </select><br>
            Komentarz:<br>
            <textarea name="komentarz" required></textarea><br>
            <button type="submit">Dodaj</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Zaloguj się</a>, aby dodać ocenę.</p>
    <?php endif; ?>
</div>



</body>
</html>
