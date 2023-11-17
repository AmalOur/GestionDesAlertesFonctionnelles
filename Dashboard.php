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

$stmtContrat = $pdo->prepare("SELECT * FROM contrat WHERE DATEDIFF(date_fin, CURDATE()) <= jour_avant_expiration");
$stmtContrat->execute();
$contrats = $stmtContrat->fetchAll(PDO::FETCH_ASSOC);

$stmtCertificat = $pdo->prepare("SELECT * FROM certificat_ssl WHERE DATEDIFF(date_fin, CURDATE()) <= jour_avant_expiration");
$stmtCertificat->execute();
$certificats = $stmtCertificat->fetchAll(PDO::FETCH_ASSOC);

$stmtContratMaintenance = $pdo->prepare("SELECT m.*, c.application_source FROM details_maintenance m INNER JOIN contrat c ON m.id_contrat = c.id_contrat WHERE DATEDIFF(m.date_fin, CURDATE()) <= m.jour_avant_expiration");
$stmtContratMaintenance->execute();
$contrats_maintenance = $stmtContratMaintenance->fetchAll(PDO::FETCH_ASSOC);

$results = array(
    "contrat" => $contrats,
    "certificat_ssl" => $certificats,
    "details_maintenance" => $contrats_maintenance
);

$jsonResults = json_encode($results);
?>

<?php
$stmtContrat = $pdo->prepare("SELECT date_fin, application_source, 'Contrat' as record_type FROM contrat WHERE DATEDIFF(date_fin, CURDATE()) <= jour_avant_expiration");
$stmtContrat->execute();
$contrats = $stmtContrat->fetchAll(PDO::FETCH_ASSOC);

$stmtCertificat = $pdo->prepare("SELECT date_fin, application_source, 'Certificat SSL' as record_type FROM certificat_ssl WHERE DATEDIFF(date_fin, CURDATE()) <= jour_avant_expiration");
$stmtCertificat->execute();
$certificats = $stmtCertificat->fetchAll(PDO::FETCH_ASSOC);

$stmtContratMaintenance = $pdo->prepare("SELECT m.date_fin, c.application_source, 'Contrat de maintenance' as record_type FROM details_maintenance m INNER JOIN contrat c ON m.id_contrat = c.id_contrat WHERE DATEDIFF(m.date_fin, CURDATE()) <= m.jour_avant_expiration");
$stmtContratMaintenance->execute();
$contrats_maintenance = $stmtContratMaintenance->fetchAll(PDO::FETCH_ASSOC);

$allData = array_merge($contrats, $certificats, $contrats_maintenance);

function generateCsvFromArray($data) {
    $csv = '';
    if (!empty($data)) {
        $csv .= implode(',', array_keys($data[0])) . "\n"; 
        foreach ($data as $row) {
            $csv .= implode(',', $row) . "\n";
        }
    }
    return $csv;
}

$csvData = generateCsvFromArray($allData);

$filePath = 'C:\xampp\htdocs\Records_alert.csv';

file_put_contents($filePath, $csvData);
?>



<!DOCTYPE html>
<html>

<head>
    <title>Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/dbStyle.css">
    <link rel="stylesheet" type="text/css" href="css/Header.css">
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
                        ?>
                     </a>
                    <a href="logout.php" class="button">
                        Deconnexion &nbsp;<span class="ph ph-sign-out"></span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    <div class="responsive-wrapper">
        <div class="main-header">
            <h1>Menu</h1>
            <div class="search">
                <input type="text" placeholder="Chercher" id="searchInput" />
                <button id="generateCSVBtn" onclick="generateCSV()">
                    <i class="ph-bold ph-chart-bar"></i>
                </button>
                <button type="submit">
                    <i class="ph-magnifying-glass-bold"></i>
                </button>
            </div>
            <div class="content">
                <div class="content-panel">
                <div class="vertical-tabs">
                    <a href="DetailSSL.php">Certificat SSL</a>
                    <a href="DetailMaintenance.php">Contrat de maintenance</a>
                    <a href="DetailContrat.php">Contrat</a>
                </div>
                </div>
                <div class="content-main">
                    <div class="certificate-grid" id="certificateList">
                    </div>
                </div>
            </div>
        </div>
        
        <script src="js/FilterItems.js"></script>
        <script src='https://unpkg.com/phosphor-icons'></script>
        <script>
            const certificates = <?php echo $jsonResults; ?>;

            function displayCertificates() {
                const certificateList = document.getElementById("certificateList");

                const contratList = certificates.contrat;
                const certificatList = certificates.certificat_ssl;
                const contratMaintenanceList = certificates.details_maintenance;

                contratList.forEach((contrat) => {
                    const contratItem = createCertificateItem("Contrat: "+contrat.application_source, contrat.date_debut, contrat.date_fin);
                    certificateList.appendChild(contratItem);
                    addItemClickEvent(contratItem, contrat, "contrat");
                });

                certificatList.forEach((certificat) => {
                    const certificatItem = createCertificateItem("Certificat SSL: "+certificat.application_source,certificat.date_debut, certificat.date_fin);
                    certificateList.appendChild(certificatItem);
                    addItemClickEvent(certificatItem, certificat, "certificat_ssl");
                });

                contratMaintenanceList.forEach((maintenance) => {
                    const maintenanceItem = createMaintenanceItem("Contrat de maintenance: "+maintenance.application_source, maintenance.date_etablie, maintenance.resultat);
                    certificateList.appendChild(maintenanceItem);
                    addItemClickEvent(maintenanceItem, maintenance, "details_maintenance");
                });
            }

            function createCertificateItem(applicationSource, dateDebut, dateFin) {
                const certificateItem = document.createElement("div");
                certificateItem.classList.add("certificate");

                const certificateName = document.createElement("h3");
                certificateName.textContent = applicationSource;
                certificateName.style.cursor = "pointer";

                const dateD = document.createElement("p");
                dateD.textContent = "Date debut: " + dateDebut;

                const dateF = document.createElement("p");
                dateF.textContent = "Date fin: " + dateFin;

                certificateItem.appendChild(certificateName);
                certificateItem.appendChild(dateD);
                certificateItem.appendChild(dateF);

                return certificateItem;
            }

            function createMaintenanceItem(idMaintenance, dateEtablie, resultat) {
                const maintenanceItem = document.createElement("div");
                maintenanceItem.classList.add("certificate");

                const maintenanceId = document.createElement("h3");
                maintenanceId.textContent = idMaintenance;
                maintenanceId.style.cursor = "pointer";

                const dateE = document.createElement("p");
                dateE.textContent = "Date établie: " + dateEtablie;

                maintenanceItem.appendChild(maintenanceId);
                maintenanceItem.appendChild(dateE);

                return maintenanceItem;
            }

            function addItemClickEvent(item, data, type) {
                item.addEventListener("click", () => {
                    showPopup(data, type);
                });
            }

            function showPopup(data, type) {
                let popupContent = "";
                if (type === "contrat") {
                    popupContent = `
                        <h3>Contrat: ${data.application_source}</h3>
                        <p>Identifiant de contrat: ${data.id_contrat}</p>
                        <p>Date debut: ${data.date_debut}</p>
                        <p>Date fin: ${data.date_fin}</p>
                        <p>Application source: ${data.application_source}</p>
                        <p>Téléphone: ${data.tel}</p>
                        <p>Nom société: ${data.nom_societe}</p>
                    `;
                } else if (type === "details_maintenance") {
                    popupContent = `
                        <h3>Contrat de maintenance: ${data.application_source}</h3>
                        <p>Identifiant de contrat de maintenance: ${data.id_maintenance}</p>
                        <p>Date établie: ${data.date_etablie}</p>
                        <p>Résultat: ${data.resultat}</p>
                        <p>Identifiant du contrat: ${data.id_contrat}</p>
                    `;
                } else {
                    popupContent = `
                        <h3>Certificat SSL: ${data.application_source}</h3>
                        <p>Identifiant de certificat SSL: ${data.id_ssl}</p>
                        <p>Date debut: ${data.date_debut}</p>
                        <p>Date fin: ${data.date_fin}</p>
                        <p>Application source: ${data.application_source}</p>
                    `;
                }

                const modal = document.createElement("div");
                modal.classList.add("modal");

                const modalContent = document.createElement("div");
                modalContent.classList.add("modal-content");

                const modalClose = document.createElement("span");
                modalClose.classList.add("modal-close");
                modalClose.innerHTML = "&times;";
                modalClose.addEventListener("click", () => {
                    modal.remove();
                });

                const modalBody = document.createElement("div");
                modalBody.classList.add("modal-body");
                modalBody.innerHTML = popupContent;

                modalContent.appendChild(modalClose);
                modalContent.appendChild(modalBody);
                modal.appendChild(modalContent);

                document.body.appendChild(modal);
            }

            window.onload = function() {
                displayCertificates();
            };

            function generateCSV() {
                fetch('generate_csv.php', {
                    method: 'POST'
                })
                .then(response => response.text())
                .catch(error => console.error('Error:', error));
            }
        </script>
    </div>
</body>

</html>