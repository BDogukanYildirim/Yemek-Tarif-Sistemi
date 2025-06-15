<?php
session_start();
require_once "config.php";
if (isset($_SESSION["user_id"])) {
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
} else {
    $avatar_src = 'data:image/png;base64,' . base64_encode(file_get_contents('./avatar.png')); // Varsayılan avatar
    $user_name = '';
}

$navbar_content = isset($_SESSION['user_id']) ?//giris durumuna göre navbar yapısı
    '<li class="nav-item"><a class="nav-link text-success" href="dashboard.php">Kontrol Paneli</a></li>
     <li class="nav-item"><a class="nav-link text-success" href="logout.php">Çıkış Yap</a></li>' :
    '<li class="nav-item"><a class="nav-link text-success" href="login.php">Giriş Yap</a></li>
     <li class="nav-item"><a class="nav-link text-success" href="register.php">Kayıt Ol</a></li>';
if (isset($_SESSION['user_id'])) {//giris yapıldıktan sonraki durumun yazısı
    $logged_in = "<h1>Hoşgeldiniz, {$user_name}!</h1>
    <p>Şef {$user_name} bir işlem yapmak için aşağıdan yapacağınız işlemi seçiniz.</p>
    <p><a href=\"add_recipe.php\" class=\"btn btn-primary\">Tarif Ekle</a></p>
    <p><a href=\"dashboard.php\" class=\"btn btn-secondary\">Tariflerinizi Görüntüleyin</a></p>
    <p><a href=\"all_recipes.php\" class=\"btn btn-info\">Tüm Tarifleri Görüntüle</a></p>
    <p><a href=\"logout.php\" class=\"btn btn-danger\">Çıkış Yap</a></p>";
} else {
    $logged_in = "<h1>Hoşgeldiniz!</h1>";
}
$template = file_get_contents('templates/index.html');
$template = str_replace('{{LOGGED_IN}}', $logged_in, $template);
$template = str_replace('{{NAVBAR_CONTENT}}', $navbar_content, $template);
$template = str_replace('{{AVATAR_SRC}}', $avatar_src, $template);
echo $template;
?>