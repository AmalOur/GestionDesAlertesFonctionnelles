<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_alerte";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_GET["email"]) || !isset($_GET["code"])) {
        header("Location: forgot_password.php");
        exit();
    }
    
    $email = $_GET["email"];
    $code = $_GET["code"];
    $newPassword = $_POST["new_password"];

    $hashed_password = md5($newPassword);
    $stmtUpdatePassword = $pdo->prepare("UPDATE compte SET mot_de_passe = :hashed_password WHERE email = :email");
    $stmtUpdatePassword->bindParam(':hashed_password', $hashed_password);
    $stmtUpdatePassword->bindParam(':email', $email);

    try {
        if ($stmtUpdatePassword->execute()) {
            $message = "Votre mot de passe a été réinitialisé avec succès.";
            //header("Location: index.php");
        } else {
            $message = "Erreur lors de la mise à jour du mot de passe.";
        }
    } catch (PDOException $e) {
        $message = "Erreur de mise à jour : " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Réinitialisation de mot de passe</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body style="background-color: #484851;">
    <div class="login-page" id="form">
        <div class="form">
            <h2>Réinitialisation de mot de passe</h2>
            <form class="login-form" method="post">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
                <label for="new_password">Entrez votre nouveau mot de passe :</label>
                <input type="password" pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$" title="Le mot de passe doit contenir au moins une lettre, un chiffre et un caractère spécial (@$!%*#?&), et faire au moins 8 caractères." id="new_password" name="new_password" style="border: 1px solid #484851;" required>
                <button type="submit" name="submit">Réinitialiser le mot de passe</button>
            </form>
            <?php if ($message !== ""): ?>
                <script type="text/javascript">
                    alert("<?php echo $message; ?>");
                    window.location.href = "index.php";
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
