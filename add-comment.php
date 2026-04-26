<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    $ocena = (int)$_POST['ocena'];
    $komentarz = trim($_POST['komentarz']);

    if ($ocena >= 1 && $ocena <= 5 && $komentarz !== "") {
        $check = $conn->prepare("SELECT review_id FROM oceny WHERE product_id = ? AND user_id = ?");
        $check->bind_param("ii", $product_id, $user_id);
        $check->execute();
        $checkRes = $check->get_result();

        if ($checkRes->num_rows > 0) {
            $_SESSION['error_msg'] = "Nie możesz dodać dwóch komentarzy do tego samego produktu.";
        } else {
            $stmt = $conn->prepare("INSERT INTO oceny (product_id, user_id, ocena, komentarz, kiedy_dodany) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiis", $product_id, $user_id, $ocena, $komentarz);
            $stmt->execute();
            $_SESSION['success_msg'] = "Twoja ocena została dodana.";
        }
    }
}
$stmt = $conn->prepare("SELECT AVG(ocena) AS srednia, COUNT(*) AS liczba FROM oceny WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$srednia = $data['srednia'];
$ilosc = $data['liczba'];

$stmt = $conn->prepare("REPLACE INTO statystyki_oceny (product_id, srednia_ocena, ilosc_ocen) VALUES (?, ?, ?)");
$stmt->bind_param("idi", $product_id, $srednia, $ilosc);
$stmt->execute();

header("Location: product.php?id=" . $product_id);
exit;
