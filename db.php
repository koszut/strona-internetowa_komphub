<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "KompHub";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Błąd połączenia z bazą: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
