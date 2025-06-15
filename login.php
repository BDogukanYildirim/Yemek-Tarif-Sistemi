<?php
session_start();
require_once "config.php";
$avatar_src = 'data:image/png;base64,' . base64_encode(file_get_contents('./avatar.png')); // Varsayılan avatar

if (isset($_SESSION["user_id"])) {
    header("location: dashboard.php");
    exit;
}

$navbar_content = isset($_SESSION['user_id']) ?
    '<li class="nav-item"><a class="nav-link text-success" href="dashboard.php">Kontrol Paneli</a></li>
     <li class="nav-item"><a class="nav-link text-success" href="logout.php">Çıkış Yap</a></li>' :
    '<li class="nav-item"><a class="nav-link text-success" href="login.php">Giriş Yap</a></li>
     <li class="nav-item"><a class="nav-link text-success" href="register.php">Kayıt Ol</a></li>';

$user_name = $password = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user_name = trim($_POST["user_name"]);
    
    if (empty(trim($_POST["password"]))) {
        $errors[] = "Lütfen şifre girin.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($errors)) {
        $sql = "SELECT User_ID, User_Name, Password FROM users WHERE User_Name = :user_name";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":user_name", $param_user_name, PDO::PARAM_STR);
            $param_user_name = $user_name;
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch();
                    if (password_verify($password, $row["Password"])) {
                        $_SESSION["user_id"] = $row["User_ID"];
                        header("location: dashboard.php");
                        exit;
                    } else {
                        $errors[] = "Geçersiz kullanici adi veya şifre.";
                    }
                } else {
                    $errors[] = "kullanici adi veya şifre yanlış.";
                }
            }
        }
    }
}

$errors_html = !empty($errors) ? '<div class="alert alert-danger">' . implode('', array_map(fn($e) => "<p>$e</p>", $errors)) . '</div>' : '';
$template = file_get_contents('templates/login.html');
$template = str_replace('{{NAVBAR_CONTENT}}', $navbar_content, $template);
$template = str_replace('{{ERRORS}}', $errors_html, $template);
$template = str_replace('{{AVATAR_SRC}}', $avatar_src, $template);
echo $template;
?>