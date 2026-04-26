<?php
session_start();
require_once "db.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : "nazwa ASC";

$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT p.product_id, p.nazwa, p.cena, p.zdjecie, k.nazwa AS kategoria
        FROM produkty p
        LEFT JOIN kategorie k ON p.kategoria = k.category_id
        WHERE p.aktywny = 1";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND p.nazwa LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if ($category > 0) {
    $sql .= " AND p.kategoria = ?";
    $params[] = $category;
    $types .= "i";
}

$sql .= " ORDER BY $sort LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$count_sql = "SELECT COUNT(*) AS total FROM produkty WHERE aktywny = 1";
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);
?>
<?php include "header.php"; ?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="normalize.css">
<link rel="stylesheet" href="variables.css">
<link rel="stylesheet" href="style.css">
<title>Sklep – Strona główna</title>
</head>
<body>
<h2>Lista produktów</h2>

<form class="search" method="get">
    Wyszukaj: <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>">
    Kategoria:
    <select name="category">
        <option value="0">Wszystkie Produkty</option>
        <?php
        $cat_result = $conn->query("SELECT category_id, nazwa FROM kategorie");
        while ($cat = $cat_result->fetch_assoc()) {
            $selected = ($category == $cat['category_id']) ? "selected" : "";
            echo "<option value='{$cat['category_id']}' $selected>{$cat['nazwa']}</option>";
        }
        ?>
    </select>
    Sortuj:
    <select name="sort">
        <option value="nazwa ASC" <?php if($sort=="nazwa ASC") echo "selected"; ?>>Nazwa A-Z</option>
        <option value="nazwa DESC" <?php if($sort=="nazwa DESC") echo "selected"; ?>>Nazwa Z-A</option>
        <option value="cena ASC" <?php if($sort=="cena ASC") echo "selected"; ?>>Cena rosnąco</option>
        <option value="cena DESC" <?php if($sort=="cena DESC") echo "selected"; ?>>Cena malejąco</option>
    </select>
    <button type="submit">Filtruj</button>
</form>

<div class="produtcts-container">
<?php
while ($row = $result->fetch_assoc()) {
    echo "<a href='product.php?id={$row['product_id']}' class='produkt-link'>";
    echo "  <div class='produkt-box'>";
    if ($row['zdjecie']) {
        echo "    <img src='product-image/{$row['zdjecie']}' alt='zdjęcie produktu'>";
    }
    echo "    <h3>" . htmlspecialchars($row['nazwa']) . "</h3>";
    echo "    <p>Cena: {$row['cena']} zł</p>";
    echo "    <p>Kategoria: " . htmlspecialchars($row['kategoria']) . "</p>";
    echo '<p><a class="primary-button" href="add-to-cart.php?id=' . $row['product_id'] . '">Dodaj do koszyka</a></p>';

    echo "  </div>";
    echo "</a>";
}
?>

</div>

<div class="pages">

</div>

</body>
</html>
