<?php
require_once "login.php";

requireLogin();

$message = '';

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    $user_id = $_SESSION["user_id"];
    $user_name = $_SESSION["user_name"];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['Nom'];
    $tel = $_POST['Tel'];
    $dateDebut = $_POST['DD'];
    $dateFin = $_POST['DF'];
    $appSource = $_POST['AS'];
    $periode = $_POST['Pdc'];
    $idcompte = $_POST['IdC'];

    if ($dateDebut > $dateFin) {
        $message = "Error: La date de début doit être antérieure à la date de fin.";
    } else {
        $servername = "localhost";
        $username = "root";
        $password = ""; 
        $dbname = "gestion_alerte";

        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

            $stmtCheckIdCompte = $pdo->prepare("SELECT COUNT(*) FROM compte WHERE id_compte = :idcompte");
            $stmtCheckIdCompte->bindParam(':idcompte', $idcompte);
            $stmtCheckIdCompte->execute();
            $count = $stmtCheckIdCompte->fetchColumn();

            if ($count === 0) {
                $message = "Erreur: L'identifiant du compte n'existe pas.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO contrat (nom_societe, tel, date_debut, date_fin, application_source, periodicite, id_compte) VALUES (:nom, :tel, :dateDebut, :dateFin, :appSource, :periode, :idcompte)");

                $stmt->bindParam(':nom', $nom);
                $stmt->bindParam(':tel', $tel);
                $stmt->bindParam(':dateDebut', $dateDebut);
                $stmt->bindParam(':dateFin', $dateFin);
                $stmt->bindParam(':appSource', $appSource);
                $stmt->bindParam(':periode', $periode);
                $stmt->bindParam(':idcompte', $idcompte);

                if ($stmt->execute()) {
                    $message = "Données insérées avec succès";
                } else {
                    $message = "Erreur: Impossible d'insérer les données.";
                }
            }
        } catch (PDOException $e) {
            $message = "Erreur: " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/Form.css">
    <link rel="stylesheet" type="text/css" href="css/Header.css">
    <title>Contrat</title>
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
<div class="responsive-wrapper">
    <div class="main-header">
            <div class="content-main">
                <div class="certificate-grid" id="certificateList">
                </div>
                <div class="button-container">
                    <a href="FormContratC.php" class="arrow-button">Contrat</a>
                    <a href="FormContratM.php" class="arrow-button">Contrat de maintenance</a>
                </div>
            </div>
        </div>
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
