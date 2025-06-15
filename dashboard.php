<?php
session_start();
require_once "config.php";

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
                   <li class="nav-item"><a class="nav-link text-success" href="logout.php">Çıkış Yap</a></li>';

$user_id = $_SESSION["user_id"];
$sql = "SELECT User_Name FROM users WHERE User_ID = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();
$user_name = htmlspecialchars($user["User_Name"]);

$sql = "SELECT * FROM recipes WHERE User_ID = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$recipes = $stmt->fetchAll();

$recipes_html = empty($recipes) ? '<p class="text-muted">Şefim henüz tarifiniz yok.Kendi yorumunuzu kattığınız tarifi kaydetmek için hemen başlayın!</p>' : '';
foreach ($recipes as $recipe) {
    $recipes_html .= '
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">' . htmlspecialchars($recipe["Recipe_Name"]) . '</h5>
                    <p class="card-text">Porsiyon: ' . htmlspecialchars($recipe["Portion_Count"]) . '</p>
                    <a href="view_recipe.php?id=' . $recipe["Recipe_ID"] . '" class="btn btn-info">Görüntüle</a>
                    <a href="edit_recipe.php?id=' . $recipe["Recipe_ID"] . '" class="btn btn-warning">Düzenle</a>
                    <a href="delete_recipe.php?id=' . $recipe["Recipe_ID"] . '" class="btn btn-danger" onclick="return confirm(\'Bu tarifi silmek istediğinizden emin misiniz?\')">Sil</a>
                </div>
            </div>
        </div>';
}

$template = file_get_contents('templates/dashboard.html');
$template = str_replace('{{NAVBAR_CONTENT}}', $navbar_content, $template);
$template = str_replace('{{USER_NAME}}', $user_name, $template);
$template = str_replace('{{RECIPES}}', $recipes_html, $template);
$template = str_replace('{{AVATAR_SRC}}', $avatar_src, $template);
echo $template;
?>