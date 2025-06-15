<?php
require_once "config.php";
session_start();

$avatar_src = 'data:image/png;base64,' . base64_encode(file_get_contents('./avatar.png')); // Varsayılan avatar
$navbar_content = isset($_SESSION['user_id']) ?
    '<li class="nav-item"><a class="nav-link text-success" href="dashboard.php">Kontrol Paneli</a></li>
     <li class="nav-item"><a class="nav-link text-success" href="logout.php">Çıkış Yap</a></li>' :
    '<li class="nav-item"><a class="nav-link text-success" href="login.php">Giriş Yap</a></li>
     <li class="nav-item"><a class="nav-link text-success" href="register.php">Kayıt Ol</a></li>';

$username = $email = $first_name = $last_name = $gsm_no = $birth_date = $password = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $errors[] = "Lütfen kullanıcı adı girin.";
    } else {
        try {
            $sql = "SELECT User_ID FROM users WHERE User_Name = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $errors[] = "Bu kullanıcı adı zaten alınmış.";
            } else {
                $username = trim($_POST["username"]);
            }
        } catch (PDOException $e) {
            $errors[] = "Veritabanı hatası: Kullanıcı adı kontrol edilemedi.";
            error_log("register.php kullanıcı adı kontrol hatası: " . $e->getMessage());
        }
    }

    if (empty(trim($_POST["email"]))) {
        $errors[] = "Lütfen e-posta girin.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Lütfen geçerli bir e-posta girin.";
    } else {
        try {
            $sql = "SELECT User_ID FROM users WHERE E_Mail = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $param_email = trim($_POST["email"]);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $errors[] = "Bu e-posta zaten alınmış.";
            } else {
                $email = trim($_POST["email"]);
            }
        } catch (PDOException $e) {
            $errors[] = "Veritabanı hatası: E-posta kontrol edilemedi.";
            error_log("register.php e-posta kontrol hatası: " . $e->getMessage());
        }
    }

    if (empty(trim($_POST["first_name"]))) {
        $errors[] = "Lütfen adınızı girin.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    if (empty(trim($_POST["last_name"]))) {
        $errors[] = "Lütfen soyadınızı girin.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    if (empty(trim($_POST["gsm_no"]))) {
        $errors[] = "Lütfen telefon numaranızı girin.";
    } else {
        $gsm_no = trim($_POST["gsm_no"]);
    }

    if (empty(trim($_POST["birth_date"]))) {
        $errors[] = "Lütfen doğum tarihinizi girin.";
    } else {
        $birth_date = trim($_POST["birth_date"]);
    }

    if (empty(trim($_POST["password"]))) {
        $errors[] = "Lütfen şifre girin.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $errors[] = "Şifre en az 6 karakter olmalı.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($_FILES["avatar"]["name"])) {
        $errors[] = "Lütfen bir profil fotoğrafı yükleyin.";
    } else {
        $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
        $max_size = 4 * 1024 * 1024; // 4MB
        if (!in_array($_FILES["avatar"]["type"], $allowed_types)) {
            $errors[] = "Yalnızca PNG, JPG veya JPEG dosyaları kabul edilir.";
        } elseif ($_FILES["avatar"]["size"] > $max_size) {
            $errors[] = "Dosya boyutu 4MB'dan büyük olamaz.";
        } elseif ($_FILES["avatar"]["error"] !== UPLOAD_ERR_OK) {
            $errors[] = "Dosya yüklenirken bir hata oluştu.";
        } else {
            $avatar = file_get_contents($_FILES["avatar"]["tmp_name"]);
        }
    }

    if (empty($_POST["terms"])) {
        $errors[] = "Kullanım koşullarını kabul etmelisiniz.";
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO users (User_Name, E_Mail, First_Name, Last_Name, GSM_No, Birth_Date, Avatar, Password) 
                    VALUES (:username, :email, :first_name, :last_name, :gsm_no, :birth_date, :avatar, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":first_name", $param_first_name, PDO::PARAM_STR);
            $stmt->bindParam(":last_name", $param_last_name, PDO::PARAM_STR);
            $stmt->bindParam(":gsm_no", $param_gsm_no, PDO::PARAM_STR);
            $stmt->bindParam(":birth_date", $param_birth_date, PDO::PARAM_STR);
            $stmt->bindParam(":avatar", $avatar, PDO::PARAM_LOB);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);

            $param_username = $username;
            $param_email = $email;
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_gsm_no = $gsm_no;
            $param_birth_date = $birth_date;
            $param_password = password_hash($password, PASSWORD_DEFAULT);

            if ($stmt->execute()) {
                header("location: login.php");
                exit;
            } else {
                $errors[] = "Kayıt işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.";
            }
        } catch (PDOException $e) {
            $errors[] = "Veritabanı hatası: Kayıt işlemi tamamlanamadı. Hata: " . htmlspecialchars($e->getMessage());
            error_log("register.php kayıt hatası: " . $e->getMessage());
        }
    }
}

$errors_html = !empty($errors) ? '<div class="alert alert-danger">' . implode('', array_map(fn($e) => "<p>$e</p>", $errors)) . '</div>' : '';
$template = file_get_contents('templates/register.html');
$template = str_replace('{{AVATAR_SRC}}', $avatar_src, $template);
$template = str_replace('{{NAVBAR_CONTENT}}', $navbar_content, $template);
$template = str_replace('{{ERRORS}}', $errors_html, $template);
echo $template;
?>