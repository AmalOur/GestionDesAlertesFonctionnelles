<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_alerte";

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code = $_POST["code"];
    $email = $_GET["email"];
    // Check if the email and code combination exist in the recuperation_mp table
    $stmt = $pdo->prepare("SELECT * FROM recuperation_mp WHERE code = :code AND STR_TO_DATE(heure_envoi, '%H:%i:%s') + INTERVAL 3 MINUTE >= NOW()");
    $stmt->bindParam(':code', $code);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        header("Location: reset_password.php?email=" . urlencode($email) . "&code=" . urlencode($code));
        exit();
    } else {
        $message = "Code expiré veuillez générer un nouveau.";
        echo '<script type="text/javascript">alert("' . $message . '");</script>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de mot de passe oublié</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body style="background-color: #484851;">
    <div class="login-page" id="form">
        <div class="form">
            <h2>Confirmation de mot de passe oublié</h2>
            <form class="login-form" method="post">
                <label for="code">Entrez le code de réinitialisation :</label>
                <input type="text" id="code" name="code" style="border: 1px solid #484851;" required>
                <button type="submit" name="submit">Confirmer</button>
            </form>
        </div>
    </div>
</body>
</html>
