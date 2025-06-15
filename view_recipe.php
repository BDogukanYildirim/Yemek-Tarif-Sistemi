<?php
require_once "config.php";
session_start();
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
$navbar_content = isset($_SESSION['user_id']) ?
    '<li class="nav-item"><a class="nav-link" href="dashboard.php">Kontrol Paneli</a></li>
     <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış Yap</a></li>' :
    '<li class="nav-item"><a class="nav-link" href="login.php">Giriş Yap</a></li>
     <li class="nav-item"><a class="nav-link" href="register.php">Kayıt Ol</a></li>';

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("location: all_recipes.php");
    exit;
}

$recipe_id = $_GET["id"];
$sql = "SELECT r.*, u.User_Name 
        FROM recipes r 
        JOIN users u ON r.User_ID = u.User_ID 
        WHERE r.Recipe_ID = :recipe_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":recipe_id", $recipe_id, PDO::PARAM_INT);
$stmt->execute();
$recipe = $stmt->fetch();

if (!$recipe) {
    header("location: all_recipes.php");
    exit;
}

$action_buttons = '';
if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $recipe["User_ID"]) {
    $action_buttons = '
        <a href="edit_recipe.php?id=' . $recipe["Recipe_ID"] . '" class="btn btn-warning mt-2">Düzenle</a>
        <a href="delete_recipe.php?id=' . $recipe["Recipe_ID"] . '" class="btn btn-danger mt-2" onclick="return confirm(\'Bu tarifi silmek istediğinizden emin misiniz?\')">Sil</a>';
}

$template = file_get_contents('templates/view_recipe.html');
$template = str_replace('{{NAVBAR_CONTENT}}', $navbar_content, $template);
$template = str_replace('{{RECIPE_NAME}}', htmlspecialchars($recipe["Recipe_Name"]), $template);
$template = str_replace('{{INGREDIENTS}}', nl2br(htmlspecialchars($recipe["Ingredients"])), $template);
$template = str_replace('{{PREPARATION_METHOD}}', nl2br(htmlspecialchars($recipe["Preparation_Method"])), $template);
$template = str_replace('{{PORTION_COUNT}}', htmlspecialchars($recipe["Portion_Count"]), $template);
$template = str_replace('{{USER_NAME}}', htmlspecialchars($recipe["User_Name"]), $template);
$template = str_replace('{{ACTION_BUTTONS}}', $action_buttons, $template);
$template = str_replace('{{AVATAR_SRC}}', $avatar_src, $template);
echo $template;
?>