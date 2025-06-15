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

$navbar_content = isset($_SESSION['user_id']) ?
    '<li class="nav-item"><a class="nav-link text-success" href="dashboard.php">Kontrol Paneli</a></li>
     <li class="nav-item"><a class="nav-link text-success" href="logout.php">Çıkış Yap</a></li>' :
    '<li class="nav-item"><a class="nav-link text-success" href="login.php">Giriş Yap</a></li>
     <li class="nav-item"><a class="nav-link text-success" href="register.php">Kayıt Ol</a></li>';

$sql = "SELECT r.Recipe_ID, r.Recipe_Name, r.Portion_Count, u.User_Name 
        FROM recipes r 
        JOIN users u ON r.User_ID = u.User_ID";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$recipes = $stmt->fetchAll();

$recipes_html = empty($recipes) ? '<p class="text-muted">Henüz tarif bulunmuyor.</p>' : '';
foreach ($recipes as $recipe) {
    $recipes_html .= '
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">' . htmlspecialchars($recipe["Recipe_Name"]) . '</h5>
                    <p class="card-text">Porsiyon: ' . htmlspecialchars($recipe["Portion_Count"]) . '</p>
                    <p class="card-text">Ekleyen: ' . htmlspecialchars($recipe["User_Name"]) . '</p>
                    <a href="view_recipe.php?id=' . $recipe["Recipe_ID"] . '" class="btn btn-info">Görüntüle</a>
                </div>
            </div>
        </div>';
}

$template = file_get_contents('templates/all_recipes.html');
$template = str_replace('{{NAVBAR_CONTENT}}', $navbar_content, $template);
$template = str_replace('{{RECIPES}}', $recipes_html, $template);
$template = str_replace('{{AVATAR_SRC}}', $avatar_src, $template);
echo $template;
?>