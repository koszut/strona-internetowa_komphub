<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once "db.php"; 


$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: order.php");
    exit;
}

$adres = trim($_SESSION['adres'] ?? ($_POST['adres'] ?? ''));
if ($adres === '') {
    $_SESSION['error_msg'] = "Adres dostawy jest wymagany.";
    header("Location: order.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart) || !is_array($cart)) {
    $_SESSION['error_msg'] = "Koszyk jest pusty.";
    header("Location: cart.php");
    exit;
}

$ids = array_map('intval', array_keys($cart));
$ids = array_filter($ids, fn($v) => $v > 0);
if (empty($ids)) {
    $_SESSION['error_msg'] = "Brak poprawnych produktów w koszyku.";
    header("Location: cart.php");
    exit;
}

function refValues(array $arr) {
    $refs = [];
    foreach ($arr as $k => $v) $refs[$k] = &$arr[$k];
    return $refs;
}


function getUserIdColumnInfo(mysqli $conn) {
    $sql = "SELECT IS_NULLABLE, COLUMN_DEFAULT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'zamowienia' AND COLUMN_NAME = 'user_id' LIMIT 1";
    $res = $conn->query($sql);
    if (!$res) return null;
    return $res->fetch_assoc();
}


function ensureGuestUser(mysqli $conn) {
    $res = $conn->query("SELECT user_id FROM uzytkownicy WHERE login = 'guest_checkout' LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) return (int)$row['user_id'];
    $stmt = $conn->prepare("INSERT INTO uzytkownicy (login, haslo, email) VALUES ('guest_checkout', '', '')");
    if ($stmt && $stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        return (int)$id;
    }
    return 0;
}

try {

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT product_id, nazwa, cena, ilosc AS stan FROM produkty WHERE product_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception("Błąd przygotowania zapytania produktów: " . $conn->error);

    $types = str_repeat('i', count($ids));
    $params = array_merge([$types], $ids);
    call_user_func_array([$stmt, 'bind_param'], refValues($params));
    if (!$stmt->execute()) throw new Exception("Błąd wykonania zapytania produktów: " . $stmt->error);

    $res = $stmt->get_result();
    $found = []; $products = [];
    while ($r = $res->fetch_assoc()) {
        $pid = (int)$r['product_id'];
        $found[] = $pid;
        $products[$pid] = $r;
    }
    $stmt->close();


    $missing = array_diff($ids, $found);
    if (!empty($missing)) {
        foreach ($missing as $m) {
            unset($_SESSION['cart'][$m]);
            unset($cart[$m]);
        }
        $_SESSION['error_msg'] = "Niektóre produkty nie są już dostępne i zostały usunięte: " . implode(', ', $missing) . ".";
    }
    if (empty($cart)) {
        header("Location: cart.php");
        exit;
    }


    $conn->begin_transaction();
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT product_id, nazwa, cena, ilosc AS stan FROM produkty WHERE product_id IN ($placeholders) FOR UPDATE";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception("Błąd prepare FOR UPDATE: " . $conn->error);
    $params = array_merge([$types], $ids);
    call_user_func_array([$stmt, 'bind_param'], refValues($params));
    if (!$stmt->execute()) throw new Exception("Błąd execute FOR UPDATE: " . $stmt->error);
    $res = $stmt->get_result();
    $products = [];
    while ($r = $res->fetch_assoc()) $products[(int)$r['product_id']] = $r;
    $stmt->close();


    $suma = 0.0;
    foreach ($cart as $pid => $qty) {
        $pid = (int)$pid; $qty = (int)$qty;
        if (!isset($products[$pid])) throw new Exception("Produkt o ID {$pid} nie istnieje (po aktualizacji).");
        if ($qty <= 0) throw new Exception("Nieprawidłowa ilość dla produktu {$pid}.");
        if ((int)$products[$pid]['stan'] < $qty) throw new Exception("Brak wystarczającej ilości produktu: " . $products[$pid]['nazwa']);
        $suma += (float)$products[$pid]['cena'] * $qty;
    }


    $status_value = 'oczekujace'; 


    $colInfo = getUserIdColumnInfo($conn);
    $isNullable = false; $hasDefault = false;
    if ($colInfo) {
        $isNullable = ($colInfo['IS_NULLABLE'] === 'YES');
        $hasDefault = ($colInfo['COLUMN_DEFAULT'] !== null);
    }


    if (!empty($_SESSION['user_id'])) {
        $user_id_value = (int)$_SESSION['user_id'];
        $stmt = $conn->prepare(
            "INSERT INTO zamowienia (user_id, kwota_suma, status_zamowienia, data_zamowienia, adres_dostawy)
             VALUES (?, ?, ?, NOW(), ?)"
        );
        if ($stmt === false) throw new Exception("Błąd prepare zamowienia: " . $conn->error);
        $stmt->bind_param("idss", $user_id_value, $suma, $status_value, $adres);

    } else {
        if ($isNullable) {
            $stmt = $conn->prepare(
                "INSERT INTO zamowienia (user_id, kwota_suma, status_zamowienia, data_zamowienia, adres_dostawy)
                 VALUES (NULL, ?, ?, NOW(), ?)"
            );
            if ($stmt === false) throw new Exception("Błąd prepare zamowienia (NULL): " . $conn->error);
            $stmt->bind_param("dss", $suma, $status_value, $adres);

        } elseif ($hasDefault) {
            $stmt = $conn->prepare(
                "INSERT INTO zamowienia (kwota_suma, status_zamowienia, data_zamowienia, adres_dostawy)
                 VALUES (?, ?, NOW(), ?)"
            );
            if ($stmt === false) throw new Exception("Błąd prepare zamowienia (default): " . $conn->error);
            $stmt->bind_param("dss", $suma, $status_value, $adres);

        } else {
            $guestId = ensureGuestUser($conn);
            if ($guestId === 0) throw new Exception("Brak możliwości przypisania user_id dla gościa.");
            $stmt = $conn->prepare(
                "INSERT INTO zamowienia (user_id, kwota_suma, status_zamowienia, data_zamowienia, adres_dostawy)
                 VALUES (?, ?, ?, NOW(), ?)"
            );
            if ($stmt === false) throw new Exception("Błąd prepare zamowienia (guest): " . $conn->error);
            $stmt->bind_param("idss", $guestId, $suma, $status_value, $adres);
        }
    }

    if (!$stmt->execute()) throw new Exception("Błąd zapisu zamówienia: " . $stmt->error);
    $order_id = $stmt->insert_id;
    $stmt->close();


    $ins = $conn->prepare("INSERT INTO zamowienia_zawartosc (order_id, product_id, ilosc, cena) VALUES (?, ?, ?, ?)");
    if ($ins === false) throw new Exception("Błąd prepare zamowienia_zawartosc: " . $conn->error);
    $upd = $conn->prepare("UPDATE produkty SET ilosc = ilosc - ? WHERE product_id = ?");
    if ($upd === false) throw new Exception("Błąd prepare update ilosc: " . $conn->error);
    $upd2 = $conn->prepare("UPDATE produkty SET aktywny = 0 WHERE product_id = ? AND ilosc <= 0");
    if ($upd2 === false) throw new Exception("Błąd prepare update aktywny: " . $conn->error);

    foreach ($cart as $pid => $qty) {
        $pid = (int)$pid; $qty = (int)$qty;
        $price = (float)$products[$pid]['cena'];

        $ins->bind_param("iiid", $order_id, $pid, $qty, $price);
        if (!$ins->execute()) throw new Exception("Błąd zapisu pozycji: " . $ins->error);

        $upd->bind_param("ii", $qty, $pid);
        if (!$upd->execute()) throw new Exception("Błąd aktualizacji stanu: " . $upd->error);

        $upd2->bind_param("i", $pid);
        $upd2->execute();
    }

    $ins->close();
    $upd->close();
    $upd2->close();

    $conn->commit();

    unset($_SESSION['cart']);
    include "header.php";
    ?>
    <!doctype html>
    <html lang="pl">
    <head><meta charset="utf-8"><title>Potwierdzenie zamówienia</title></head>
    <body>
    <h2>Dziękujemy za zamówienie!</h2>
    <?php if (!empty($_SESSION['error_msg'])): ?>
        <p style="color:orange;"><?php echo htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?></p>
    <?php endif; ?>
    <p>Numer zamówienia: <?php echo htmlspecialchars($order_id); ?></p>
    <p>Adres dostawy: <?php echo nl2br(htmlspecialchars($adres)); ?></p>
    <p >Kwota: <?php echo number_format($suma,2,',',' '); ?> zł</p>
    <p ><a  href="index.php">Wróć do sklepu</a></p>
    </body>
    </html>
    <?php
    exit;

} catch (Exception $e) {
    if ($conn->errno) $conn->rollback();
    error_log("Checkout error: " . $e->getMessage());
    include "header.php";
    ?>
    <!doctype html>
    <html lang="pl">
    <head><meta charset="utf-8"><title>Błąd zamówienia</title></head>
    <body>
    <h2>Błąd przy składaniu zamówienia</h2>
    <p style="color:red;"><?php echo htmlspecialchars($e->getMessage()); ?></p>
    <p><a href="cart.php">Wróć do koszyka</a></p>
    </body>
    </html>
    <?php
    exit;
}
