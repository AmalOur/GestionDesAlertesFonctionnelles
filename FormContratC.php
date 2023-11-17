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
    $joursAvantExpiration = $_POST['JAE'];
    $appSource = $_POST['AS'];
    $periode = $_POST['Pdc'];

    if ($dateDebut > $dateFin) {
        $message = "Erreur: La date de fin doit être après la date de debut.";
    } else {
        $servername = "localhost";
        $username = "root";
        $password = ""; 
        $dbname = "gestion_alerte";

        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

            $stmt = $pdo->prepare("INSERT INTO contrat (nom_societe, tel, date_debut, date_fin, jour_avant_expiration, application_source, periodicite) VALUES (:nom, :tel, :dateDebut, :dateFin, :joursAvantExpiration, :appSource, :periode)");

            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':tel', $tel);
            $stmt->bindParam(':dateDebut', $dateDebut);
            $stmt->bindParam(':dateFin', $dateFin);
            $stmt->bindParam(':joursAvantExpiration', $joursAvantExpiration);
            $stmt->bindParam(':appSource', $appSource);
            $stmt->bindParam(':periode', $periode);

            if ($stmt->execute()) {
                $message = "Données insérées avec succès";
            } else {
                $message = "Erreur: Impossible d'insérer les données.";
            }
        }
        catch (PDOException $e) {
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

<div class="form-container">
    <h2 class="form-title">Formulaire de Contrat</h2>

    <form action="" method="post" id="myForm">
        <label for="Nom">Nom société</label>
        <input type="text" id="Nom" placeholder="Nom" name="Nom" required>

        <label for="Tel">Téléphone</label>
        <input type="tel" id="Tel" placeholder="(+212)123 456-789" name="Tel" maxlength="17" required pattern="\+212[0-9]{9}">

        <label for="DD">Date debut</label>
        <input type="date" id="DD" name="DD" required>

        <label for="DF">Date fin</label>
        <input type="date" id="DF" name="DF" required>

        <label for="JAE">Jours avant expiration</label>
        <input type="number" id="JAE" placeholder="Jours" name="JAE" required>

        <label for="AS">Application source </label>
        <input type="text" id="AS" placeholder="Source" name="AS" required>

        <label for="Pdc">Périodicité (mois) </label>
        <input type="number" id="Pdc" placeholder="123" name="Pdc" required>

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