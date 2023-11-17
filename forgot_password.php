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

    $stmt = $pdo->prepare("SELECT * FROM compte WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $resetCode = rand(100000, 999999);

        // Get the current date and time
        $currentDateTime = date("Y-m-d H:i:s");

        $stmtInsertCode = $pdo->prepare("INSERT INTO recuperation_mp (`code`, `id_compte`, `date_envoi`, `heure_envoi`) VALUES (:code, :id_compte, :date_envoi, CURTIME())");
        $stmtInsertCode->bindParam(':code', $resetCode);
        $stmtInsertCode->bindParam(':id_compte', $user['id_compte']);
        $stmtInsertCode->bindParam(':date_envoi', $currentDateTime);
        $stmtInsertCode->execute();

        $subject = "Récupération de mot de passe";
        $message = "Bonjour,";
        $message .= "\nVoici votre code de réinitialisation: " . $resetCode;
        $message .= "\nCordialement,\nVotre équipe CDG.";
        $headers = "From: ourajimamal@gmail.com"; 
        mail($email, $subject, $message, $headers);

        header("Location: forgot_password_confirmation.php?email=" . urlencode($email));
    } else {
        $message = "Adresse e-mail non trouvée.";
        echo '<script type="text/javascript">alert("' . $message . '");</script>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body style="background-color: #484851;">
    <div class="login-page" id="form">
        <div class="form">
            <h2>Mot de passe oublié</h2>
            <form class="login-form" method="post">
                <label for="email">Entrez votre adresse e-mail :</label>
                <input type="email" id="email" name="email" style="border: 1px solid #484851;" required>
                <button type="submit" name="submit">Envoyer le code de réinitialisation</button>
            </form>
        </div>
    </div>
</body>
</html>