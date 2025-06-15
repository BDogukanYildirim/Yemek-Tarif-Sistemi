<?php
require_once "config.php";
session_start();
if (!isset($_SESSION["user_id"])) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$sql = "SELECT User_Name, Avatar FROM users WHERE User_ID = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();
if ($user) {
    $user_name = htmlspecialchars($user["User_Name"]);
    if ($user["Avatar"]) {
        $avatar_src = 'data:image/jpeg;base64,' . base64_encode($user["Avatar"]);
    }
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("location: dashboard.php");
    exit;
}

$recipe_id = $_GET["id"];
$sql = "SELECT * FROM recipes WHERE Recipe_ID = :recipe_id AND User_ID = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":recipe_id", $recipe_id, PDO::PARAM_INT);
$stmt->bindParam(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
$stmt->execute();
$recipe = $stmt->fetch();

if (!$recipe) {
    header("location: dashboard.php");
    exit;
}

$recipe_name = $recipe["Recipe_Name"];
$ingredients = $recipe["Ingredients"];
$preparation_method = $recipe["Preparation_Method"];
$portion_count = $recipe["Portion_Count"];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["name"]))) {
        $errors[] = "Lütfen tarif adı girin.";
    } else {
        $recipe_name = trim($_POST["name"]);
    }

    if (empty(trim($_POST["ingredients"]))) {
        $errors[] = "Lütfen malzemeleri girin.";
    } else {
        $ingredients = trim($_POST["ingredients"]);
    }

    if (empty(trim($_POST["preparation_method"]))) {
        $errors[] = "Lütfen hazırlanış yöntemini girin.";
    } else {
        $preparation_method = trim($_POST["preparation_method"]);
    }

    if (empty($_POST["portion_count"]) || !is_numeric($_POST["portion_count"]) || $_POST["portion_count"] <= 0) {
        $errors[] = "Lütfen geçerli bir porsiyon sayısı girin.";
    } else {
        $portion_count = intval($_POST["portion_count"]);
    }

    if (empty($errors)) {
        $sql = "UPDATE recipes SET Recipe_Name = :name, Ingredients = :ingredients, Preparation_Method = :preparation_method, Portion_Count = :portion_count WHERE Recipe_ID = :recipe_id AND User_ID = :user_id";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":name", $recipe_name, PDO::PARAM_STR);
            $stmt->bindParam(":ingredients", $ingredients, PDO::PARAM_STR);
            $stmt->bindParam(":preparation_method", $preparation_method, PDO::PARAM_STR);
            $stmt->bindParam(":portion_count", $portion_count, PDO::PARAM_INT);
            $stmt->bindParam(":recipe_id", $recipe_id, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);

            if ($stmt->execute()) {
                header("location: dashboard.php");
                exit;
            } else {
                $errors[] = "Tarif güncellenemedi.";
            }
        }
    }
}

include 'templates/edit_recipe.html';
$template = file_get_contents('templates/edit_recipe.html');
$template = str_replace('{{AVATAR_SRC}}', $avatar_src, $template);
echo $template;
?>
