<?php
require_once "login.php";

requireLogin();

$message = '';

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    $user_id = $_SESSION["user_id"];
    $user_name = $_SESSION["user_name"];
}

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "gestion_alerte";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmtContratIds = $pdo->prepare("SELECT id_contrat FROM contrat");
    $stmtContratIds->execute();
    $contratIds = $stmtContratIds->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateEtablie = $_POST['DateEtablie'];
    $dateFin = $_POST['DF'];
    $joursAvantExpiration = $_POST['JAE'];
    $resultat = $_POST['Resultat'];
    $idContrat = $_POST['IdContrat'];

    if ($dateFin <= $dateEtablie) {
        $message = "Erreur: La date de fin doit être après la date établie.";
    } else {
        try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    SELECT 1
                    FROM contrat
                    WHERE id_contrat = :idContrat AND :dateEtablie >= date_debut AND :dateFin <= date_fin
                ");

                $stmt->bindParam(':dateEtablie', $dateEtablie);
                $stmt->bindParam(':dateFin', $dateFin);
                $stmt->bindParam(':idContrat', $idContrat);

                $stmt->execute();
                $validRange = $stmt->fetchColumn();

                if (!$validRange) {
                    $message = "Erreur: La date doit être entre date debut et date fin du contrat.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO details_maintenance (date_etablie, date_fin, jour_avant_expiration, resultat, id_contrat)
                        VALUES (:dateEtablie, :dateFin, :joursAvantExpiration, :resultat, :idContrat)
                    ");

                    $stmt->bindParam(':dateEtablie', $dateEtablie);
                    $stmt->bindParam(':dateFin', $dateFin);
                    $stmt->bindParam(':joursAvantExpiration', $joursAvantExpiration);
                    $stmt->bindParam(':resultat', $resultat);
                    $stmt->bindParam(':idContrat', $idContrat);

                    if ($stmt->execute()) {
                        $pdo->commit();
                        $message = "Données insérées avec succès";
                    } else {
                        $message = "Erreur: Impossible d'insérer les données.";
                    }
                }
            }
        catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Erreur: " . $e->getMessage();
        }
    }
}

?>




<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/Form.css">
    <link rel="stylesheet" type="text/css" href="css/dbStyle.css">
    <link rel="stylesheet" type="text/css" href="css/Header.css">
    <title>Contrat de maintenance</title>
</head>
<body>

<header class="header">
    <div class="header-content responsive-wrapper">
        <div class="header-logo">
                <div>
                    <img src="image/logo2.png" />
                </div>
        </div>
        <div class="header-navigation">
                <nav class="header-navigation-links">
                    <a href="Dashboard.php"> Accueil </a>
                    <div class="dropdown">
                        <a class="dropbtn">Trace</a>
                        <div class="dropdown-content">
                            <a href="Connexions.php">Gestion de connexions</a>
                            <a href="Notification.php" style="margin-left: 0px">Gestion de notifications</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a class="dropbtn">Administration</a>
                        <div class="dropdown-content">
                            <a href="FormSSL.php">Gestion de Certificat SSL</a>
                            <a href="Users.php" style="margin-left: 0px">Gestion des Utilisateurs</a>
                            <a href="FormContrat.php" style="margin-left: 0px">Cestion de Contrat</a>
                        </div>
                    </div>
                </nav>
                <div class="header-navigation-actions">
                    <a href="#" class="button">
                        <?php
                            if (isset($_SESSION["user_id"])) {
                                echo '<span class="ph ph-user"></span> &nbsp' . $_SESSION["user_name"] ;
                            } else {
                                echo '<span class="ph ph-user"></span>';
                            }
                        ?>                    </a>
                    <a href="logout.php" class="button">
                        Deconnexion &nbsp;<span class="ph ph-sign-out"></span>
                    </a>
                </div>
            </div>
    </div>
</header>

<div class="form-container">
    <h2 class="form-title">Formulaire de Contrat de Maintenance</h2>

    <form action="" method="post" id="myForm">
        <label for="DateEtablie">Date établie</label>
        <input type="date" id="DateEtablie" name="DateEtablie" required>

        <label for="DF">Date fin</label>
        <input type="date" id="DF" name="DF" required>

        <label for="JAE">Jours avant expiration</label>
        <input type="number" id="JAE" placeholder="Jours" name="JAE" required>

        <label for="Resultat">Résultat</label>
        <input type="text" id="Resultat" placeholder="Résultat" name="Resultat" required>

        <label for="IdContrat">Identifiant du contrat</label>
        <select id="IdContrat" name="IdContrat" required>
            <option value="" disabled selected>Choisir un identifiant de contrat</option>
            <?php foreach ($contratIds as $contratId): ?>
                <option value="<?php echo $contratId; ?>"><?php echo $contratId; ?></option>
            <?php endforeach; ?>
        </select>


        <?php if ($message): ?>
            <input type="hidden" id="message" value="<?php echo $message; ?>">
        <?php endif; ?>

        <input class="formButton" type="submit" id="submit" value="Valider">
        <input class="formButton" type="reset" value="Annuler">
    </form>

    <a href="FormContrat.php" class="redirect-button">
        <span class="ph ph-arrow-left"></span>
    </a>
</div>

<script src='https://unpkg.com/phosphor-icons'></script>
<script>
    const messageInput = document.getElementById('message');
    const message = messageInput ? messageInput.value : '';

    if (message) {
        alert(message);
    }
</script>
</body>
</html>
