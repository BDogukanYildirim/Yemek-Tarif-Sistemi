<?php
require_once "config.php";
session_start();

if (!isset($_SESSION["user_id"]) || !isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("location: login.php");
    exit;
}

$recipe_id = $_GET["id"];
$sql = "DELETE FROM recipes WHERE Recipe_ID = :recipe_id AND User_ID = :user_id";
if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":recipe_id", $recipe_id, PDO::PARAM_INT);
    $stmt->bindParam(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("location: dashboard.php");
        exit;
    } else {
        echo "Tarif silinirken hata oluştu.";
    }
}
?>