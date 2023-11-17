<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_alerte";

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $mot_de_passe = $_POST["mot_de_passe"];
    $hashed_password = md5($mot_de_passe);

    $stmt = $pdo->prepare("SELECT * FROM compte WHERE email = :email AND mot_de_passe = :password");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION["user_id"] = $user['id_compte']; 
        $_SESSION["user_role"] = $user['role']; 
        $_SESSION["user_name"] = $user['nom'];
        $_SESSION["logged_in"] = true; 

        $userId = $user['id_compte'];
        $loginDate = date("Y-m-d H:i:s"); 
        $stmtInsertLogin = $pdo->prepare("INSERT INTO trace_connexion (id_compte, date_entree, heure_entree) VALUES (:id_compte, :date_entree, CURTIME())");
        $stmtInsertLogin->bindParam(':id_compte', $userId);
        $stmtInsertLogin->bindParam(':date_entree', $loginDate);
        $stmtInsertLogin->execute();

        if ($user['role'] === "Admin") {
            header("Location: Dashboard.php");
            exit();
        } elseif ($user['role'] === "Consultant") {
            header("Location: C_Dashboard.php");
            exit();
        }
    } else {
        $message = "email ou mot de passe incorrect";
    }
}

if (!empty($message)) {
    echo '<script>alert("' . $message . '");</script>';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="logo-container">
        <img src="image/logo.png">
    </div>
    <br>
    <div class="login-page" id="form">
        <div class="form">
            <form class="login-form" method="post">
                <h2><i class="fas fa-lock"></i>Connexion</h2>
                <br/><br/>
                <input type="email" name="email" placeholder="email" required />
                <div class="password-container">
                    <input type="password" name="mot_de_passe" placeholder="mot de passe" id="password" required class="password-input" />
                    <img src="image/passwd.png" alt="Show/Hide Password" class="toggle-password" onclick="togglePasswordVisibility()" />
                </div>
                <button type="submit" name="send2" style="margin-bottom:15px">connexion</button>
                <a href="https://localhost/forgot_password.php" style="color:black">Mot de passe oublié ?</a>
            </form>
            <br>
        </div>
        <br/><br/>
    </div>
    <script>
        function changeBg() {
            var form = document.getElementById('form');
            var scrollValue = window.scrollY;
            console.log(scrollValue);
            if (scrollValue < 50) {
                form.classList.remove('bgColor');
            } else {
                form.classList.add('bgColor');
            }
        }
        window.addEventListener('scroll', changeBg)

        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("password");
            var passwordToggle = document.querySelector(".toggle-password");
            var passwordError = document.getElementById("password-error");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordToggle.src = "image/passwd.png";
            } else {
                passwordInput.type = "password";
                passwordToggle.src = "image/passwd.png";
            }
        }

    </script>
</body>
</html>
