<?php
require_once "login.php";

requireLogin();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_alerte";

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    $user_id = $_SESSION["user_id"];
    $user_name = $_SESSION["user_name"];
}

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("
    SELECT 
        n.id_notif_contrat,
        n.date_envoi,
        n.heure_envoi, 
        n.id_contrat,
        n.id_ssl,
        u.nom,
        dm.id_maintenance,
        u.email AS user_email,
        n.heure_envoi AS heure_envoi_ssl 
    FROM
        (
            SELECT 
                id_notif_contrat, 
                date_envoi, 
                id_contrat, 
                heure_envoi, 
                NULL AS id_ssl, 
                id_compte, 
                NULL AS id_maintenance 
            FROM notification_contrat
            UNION ALL
            SELECT 
                id_notif_maintenance, 
                date_envoi, 
                NULL AS id_contrat, 
                heure_envoi, 
                NULL AS id_ssl, 
                id_compte, 
                id_maintenance 
            FROM notification_maintenance
            UNION ALL
            SELECT 
                id_notif_ssl, 
                date_envoi, 
                NULL AS id_contrat, 
                heure_envoi, 
                id_ssl, 
                id_compte, 
                NULL AS id_maintenance 
            FROM notification_ssl
        ) n
    LEFT JOIN compte u ON n.id_compte = u.id_compte
    LEFT JOIN contrat c ON n.id_contrat = c.id_contrat
    LEFT JOIN certificat_ssl s ON n.id_ssl = s.id_ssl
    LEFT JOIN details_maintenance dm ON n.id_maintenance = dm.id_maintenance
    ORDER BY DATE(n.date_envoi) ASC, TIME(n.heure_envoi) DESC
");

$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rowsPerPage = 10; 
$totalRows = count($notifications);
$totalPages = ceil($totalRows / $rowsPerPage);

if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $currentPage = (int)$_GET['page'];
} else {
    $currentPage = 1;
}

if ($currentPage < 1) {
    $currentPage = 1;
} elseif ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $rowsPerPage;

$notificationsPerPage = array_slice($notifications, $offset, $rowsPerPage);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de notifications</title>
    <link rel="stylesheet" href="css/Header.css">
    <link rel="stylesheet" href="css/NotificationStyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>
<header class="header">
    <div class="header-content responsive-wrapper">
            <div class="header-logo">
                    <div>
                        <img src="image/logo2.png" />
                    </div>
            </div>
            <div class="main-header">
                <div class="search">
                <input type="text" placeholder="Chercher" id="searchInput" onkeyup="filterTable()"/>
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
                        ?>  
                    </a>
                    <a href="logout.php" class="button">
                        Deconnexion &nbsp;<span class="ph ph-sign-out"></span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    <div class="container">
        <h1>Liste de notifications</h1>
        <table id="myTable" class="table table-hover" style="width:100%">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Date d'envoi</th>
                    <th scope="col">Heure envoi</th>
                    <th scope="col">Nom</th>
                    <th scope="col">Email</th>
                    <th scope="col">Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notificationsPerPage as $notification) { ?>
                    <tr>
                        <td><?php echo isset($notification['id_notif_contrat']) ? $notification['id_notif_contrat'] : $notification['id_notif_ssl']; ?></td>
                        <td><?php echo $notification['date_envoi']; ?></td>
                        <td><?php echo $notification['heure_envoi']; ?></td>
                        <td><?php echo $notification['nom']; ?></td>
                        <td><?php echo $notification['user_email']; ?></td>
                        <td>
                            <?php 
                            if (isset($notification['id_maintenance'])) {
                                echo 'Contrat de maintenance';
                            } elseif (isset($notification['id_contrat'])) {
                                echo 'Contrat';
                            } elseif (isset($notification['id_ssl'])) {
                                echo 'Certificat SSL';
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center" style="background-color: transparent;">
                <?php if ($totalPages > 1) { ?>
                    <?php if ($currentPage > 1) { ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" style="background-color: var(--c-background-tertiary);color: var(--c-accent-primary);">Precedent</a></li>
                    <?php } ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>" style="background-color: var(--c-background-tertiary);color: var(--c-accent-primary);"><?php echo $i; ?></a></li>
                    <?php } ?>
                    <?php if ($currentPage < $totalPages) { ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" style="background-color: var(--c-background-tertiary);color: var(--c-accent-primary);">Suivant</a></li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </nav>


<script src='https://unpkg.com/phosphor-icons'></script>
<script>
        function filterTable() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toLowerCase();
            var table = document.getElementById("myTable");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var displayRow = false;
                var columns = rows[i].getElementsByTagName("td");

                for (var j = 0; j < columns.length; j++) {
                    var td = columns[j];
                    if (td) {
                        var txtValue = td.textContent || td.innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            displayRow = true;
                            break;
                        }
                    }
                }

                rows[i].style.display = displayRow ? "" : "none";
            }
        }
    </script>
</body>
</html>