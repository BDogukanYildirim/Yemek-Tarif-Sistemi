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

$navbar_content = '<li class="nav-item"><a class="nav-link text-success" href="dashboard.php">Kontrol Paneli</a></li>
                <li class="nav-item"><a class="nav-link text-success" href="logout.php">Çıkış Yap</a></li>' ;

$recipe_name = $ingredients = $preparation_method = $portion_count = "";
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
        $sql = "INSERT INTO recipes (Recipe_Name, Ingredients, Preparation_Method, Portion_Count, User_ID) VALUES (:name, :ingredients, :preparation_method, :portion_count, :user_id)";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":name", $recipe_name, PDO::PARAM_STR);
            $stmt->bindParam(":ingredients", $ingredients, PDO::PARAM_STR);
            $stmt->bindParam(":preparation_method", $preparation_method, PDO::PARAM_STR);
            $stmt->bindParam(":portion_count", $portion_count, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);

            if ($stmt->execute()) {
                header("location: dashboard.php");
                exit;
            } else {
                $errors[] = "Tarif eklenemedi. Lütfen tekrar deneyin.";
            }
        }
    }
}

$errors_html = !empty($errors) ? '<div class="alert alert-danger">' . implode('', array_map(fn($e) => "<p>$e</p>", $errors)) . '</div>' : '';
$template = file_get_contents('templates/add_recipe.html');
$template = str_replace('{{NAVBAR_CONTENT}}', $navbar_content, $template);
$template = str_replace('{{RECIPE_NAME}}', htmlspecialchars($recipe_name), $template);
$template = str_replace('{{INGREDIENTS}}', htmlspecialchars($ingredients), $template);
$template = str_replace('{{PREPARATION_METHOD}}', htmlspecialchars($preparation_method), $template);
$template = str_replace('{{PORTION_COUNT}}', htmlspecialchars($portion_count), $template);
$template = str_replace('{{ERRORS}}', $errors_html, $template);
$template = str_replace('{{AVATAR_SRC}}', $avatar_src, $template);
echo $template;
?>