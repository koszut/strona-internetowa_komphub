<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT login, email, rola FROM uzytkownicy WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

function human_status(string $s): string {
    $map = [
        'oczekujace' => 'Oczekujące',
        'zrealizowane' => 'Zrealizowane',
        'anulowane' => 'Anulowane',
        'w_trakcie' => 'W trakcie realizacji',
        'pending' => 'Oczekujące'
    ];
    $key = mb_strtolower($s, 'UTF-8');
    return $map[$key] ?? ucfirst(str_replace('_', ' ', $s));
}

$stmt = $conn->prepare("SELECT order_id, kwota_suma, status_zamowienia, data_zamowienia, adres_dostawy FROM zamowienia WHERE user_id = ? ORDER BY data_zamowienia DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$ordersRes = $stmt->get_result();
$orders = $ordersRes->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include "header.php";
?>

<h2 class="account__title">Moje konto</h2>
<p class="account__field"><strong class="account__label">Login:</strong> <?php echo htmlspecialchars($user['login']); ?></p>
<p class="account__field"><strong class="account__label">Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
<p class="account__field"><strong class="account__label">Rola:</strong> <?php echo htmlspecialchars($user['rola']); ?></p>

<hr class="divider">

<h2 class="orders__title">Moje zamówienia</h2>

<?php if (empty($orders)): ?>
    <p class="orders__empty">Nie masz jeszcze żadnych zamówień. <a class="orders__link" href="index.php">Wróć do sklepu</a></p>
<?php else: ?>

    <?php foreach ($orders as $order): ?>
        <?php

        $stmt = $conn->prepare(
            "SELECT z.order_id, z.product_id, z.ilosc, z.cena, p.nazwa
             FROM zamowienia_zawartosc z
             LEFT JOIN produkty p ON p.product_id = z.product_id
             WHERE z.order_id = ?"
        );
        $stmt->bind_param("i", $order['order_id']);
        $stmt->execute();
        $itemsRes = $stmt->get_result();
        $items = $itemsRes->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        ?>

        <div class="order">
            <p class="order__meta"><strong class="order__label">Numer zamówienia:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
            <p class="order__meta"><strong class="order__label">Data:</strong> <?php echo htmlspecialchars($order['data_zamowienia']); ?></p>
            <p class="order__meta"><strong class="order__label">Status:</strong> <?php echo htmlspecialchars(human_status($order['status_zamowienia'])); ?></p>
            <p class="order__meta"><strong class="order__label">Kwota:</strong> <?php echo number_format((float)$order['kwota_suma'], 2, ',', ' '); ?> zł</p>
            <p class="order__meta"><strong class="order__label">Adres dostawy:</strong><br><?php echo nl2br(htmlspecialchars($order['adres_dostawy'])); ?></p>

            <h4 class="order__items-title">Pozycje</h4>
            <table class="order__table">
                <thead class="order__thead">
                    <tr class="order__row">
                        <th class="order__th order__th--left">Produkt</th>
                        <th class="order__th order__th--right">Cena</th>
                        <th class="order__th order__th--right">Ilość</th>
                        <th class="order__th order__th--right">Razem</th>
                    </tr>
                </thead>
                <tbody class="order__tbody">
                <?php foreach ($items as $it): ?>
                    <tr class="order__row">
                        <td class="order__td"><?php echo htmlspecialchars($it['nazwa'] ?? 'Produkt usunięty'); ?></td>
                        <td class="order__td order__td--right"><?php echo number_format((float)$it['cena'], 2, ',', ' '); ?> zł</td>
                        <td class="order__td order__td--right"><?php echo (int)$it['ilosc']; ?></td>
                        <td class="order__td order__td--right"><?php echo number_format((float)$it['cena'] * (int)$it['ilosc'], 2, ',', ' '); ?> zł</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>

<?php endif; ?>



