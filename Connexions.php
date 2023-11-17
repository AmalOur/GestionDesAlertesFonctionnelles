<?php
require_once "login.php";

requireLogin();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_alerte";

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    $user_id = $_SESSION["user_id"];
    $user_name = $_SESSION["user_name"];
}

$stmtCompte = $pdo->prepare("SELECT tc.id_trace, tc.date_entree, tc.date_sortie, tc.heure_entree, tc.heure_sortie, c.id_compte, c.nom, c.role FROM trace_connexion tc INNER JOIN compte c ON tc.id_compte = c.id_compte ORDER BY DATE(tc.date_entree) DESC");
$stmtCompte->execute();
$Comptes = $stmtCompte->fetchAll(PDO::FETCH_ASSOC);

$rowsPerPage = 10;
$totalRows = count($Comptes);
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

$ComptesPerPage = array_slice($Comptes, $offset, $rowsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de connexions</title>
    <link rel="stylesheet" href="css/Header.css">
    <link rel="stylesheet" href="css/connexionsStyle.css">
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
        <h1>Trace de connexions</h1>
        <table id="myTable" class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Date d'entrée</th>
                    <th scope="col">Heure d'entrée</th>
                    <th scope="col">Date de sortie</th>
                    <th scope="col">Heure de sortie</th>
                    <th scope="col">ID Utilisateur</th>
                    <th scope="col">Nom de l'utilisateur</th>
                    <th scope="col">Role de l'utilisateur</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ComptesPerPage as $compte) { ?>
                    <tr>
                        <td><?php echo $compte['id_trace']; ?></td>
                        <td><?php echo $compte['date_entree']; ?></td>
                        <td><?php echo $compte['heure_entree']; ?></td>
                        <td><?php echo $compte['date_sortie']; ?></td>
                        <td><?php echo $compte['heure_sortie']; ?></td>
                        <td><?php echo $compte['id_compte']; ?></td>
                        <td><?php echo $compte['nom']; ?></td>
                        <td><?php echo $compte['role']; ?></td>
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
    </div>
    
    <script src='https://unpkg.com/phosphor-icons'></script>
    <script>
        function filterTable() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toLowerCase();
            var table = document.getElementById("myTable");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var row = rows[i];
                var showRow = false;

                for (var j = 0; j < row.cells.length; j++) {
                    var cell = row.cells[j];
                    var txtValue = cell.textContent || cell.innerText;

                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        showRow = true;
                        break;
                    }
                }

                row.style.display = showRow ? "" : "none";
            }
        }
    </script>
</body>
</html>