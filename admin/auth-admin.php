<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["rola"] ?? "user") !== "admin") {
    header("Location: ../login.php");
    exit;
}
