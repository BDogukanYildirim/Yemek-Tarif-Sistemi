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
                   <li class="nav-item"><a class="nav-link text-success" href="logout.php">Çıkış Yap</a></li>';

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("location: dashboard.php");
    exit;
}

$recipe_id = (int)$_GET["id"];
$sql = "SELECT Recipe_Name, Ingredients, Preparation_Method, Portion_Count 
        FROM recipes WHERE Recipe_ID = :recipe_id AND User_ID = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":recipe_id", $recipe_id, PDO::PARAM_INT);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    header("location: dashboard.php");
    exit;
}

// Mevcut tarif verilerini al
$recipe_name = $recipe["Recipe_Name"];
$ingredients = $recipe["Ingredients"];
$preparation_method = $recipe["Preparation_Method"];
$portion_count = $recipe["Portion_Count"];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini doğrula
    $recipe_name = trim($_POST["name"] ?? "");
    if (empty($recipe_name)) {
        $errors[] = "Lütfen tarif adı girin.";
    }

    $ingredients = trim($_POST["ingredients"] ?? "");
    if (empty($ingredients)) {
        $errors[] = "Lütfen malzemeleri girin.";
    }

    $preparation_method = trim($_POST["preparation_method"] ?? "");
    if (empty($preparation_method)) {
        $errors[] = "Lütfen hazırlanış yöntemini girin.";
    }

    $portion_count = isset($_POST["portion_count"]) ? (int)$_POST["portion_count"] : 0;
    if ($portion_count <= 0) {
        $errors[] = "Lütfen geçerli bir porsiyon sayısı girin.";
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE recipes 
                    SET Recipe_Name = :name, Ingredients = :ingredients, 
                        Preparation_Method = :preparation_method, Portion_Count = :portion_count 
                    WHERE Recipe_ID = :recipe_id AND User_ID = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":name", $recipe_name, PDO::PARAM_STR);
            $stmt->bindParam(":ingredients", $ingredients, PDO::PARAM_STR);
            $stmt->bindParam(":preparation_method", $preparation_method, PDO::PARAM_STR);
            $stmt->bindParam(":portion_count", $portion_count, PDO::PARAM_INT);
            $stmt->bindParam(":recipe_id", $recipe_id, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header("location: dashboard.php");
                exit;
            } else {
                $errors[] = "Tarif güncellenemedi. Lütfen tekrar deneyin.";
            }
        } catch (PDOException $e) {
            $errors[] = "Veritabanı hatası: " . htmlspecialchars($e->getMessage());
            error_log("edit_recipe.php güncelleme hatası: " . $e->getMessage());
        }
    }
}

// Şablonu yükle ve yer tutucuları doldur
$template = file_get_contents('templates/edit_recipe.html');
$template = str_replace('{{AVATAR_SRC}}', htmlspecialchars($avatar_src), $template);
$template = str_replace('{{NAVBAR_CONTENT}}', $navbar_content, $template);
$template = str_replace('{{ERRORS}}', empty($errors) ? '' : '<div class="alert alert-danger">' . implode('<br>', array_map('htmlspecialchars', $errors)) . '</div>', $template);
$template = str_replace('{{RECIPE_NAME}}', htmlspecialchars($recipe_name), $template);
$template = str_replace('{{INGREDIENTS}}', htmlspecialchars($ingredients), $template);
$template = str_replace('{{PREPARATION_METHOD}}', htmlspecialchars($preparation_method), $template);
$template = str_replace('{{PORTION_COUNT}}', htmlspecialchars($portion_count), $template);

echo $template;
?>
