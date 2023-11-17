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
    
    $stmtCertificat = $pdo->prepare("SELECT * FROM certificat_ssl");
    $stmtCertificat->execute();
    $certificats = $stmtCertificat->fetchAll(PDO::FETCH_ASSOC);

    $results = array(
        "certificat_ssl" => $certificats
    );

    $jsonResults = json_encode($results);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
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
            <h1>Menu</h1>
            <div class="search">
                <input type="text" placeholder="Chercher" id="searchInput" />
                <button type="submit">
                    <i class="ph-magnifying-glass-bold"></i>
                </button>
            </div>
            <div class="content">
                <div class="content-panel">
                    <div class="vertical-tabs">
                        <a href="DetailSSL.php" style="background-color: var(--c-background-tertiary); color: var(--c-accent-primary);">Certificat SSL</a>
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
                const certificatList = certificates.certificat_ssl;

                certificatList.forEach((certificat) => {
                    const certificatItem = createCertificateItem("Certificat SSL: "+certificat.application_source,certificat.date_debut, certificat.date_fin);
                    certificateList.appendChild(certificatItem);
                    addItemClickEvent(certificatItem, certificat, "certificat_ssl");
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

            function addItemClickEvent(item, data, type) {
                item.addEventListener("click", () => {
                    showPopup(data, type);
                });
            }

            function showPopup(data, type) {
                let popupContent = "";
                    popupContent = `
                        <h3>Certificat SSL: ${data.application_source}</h3>
                        <p>Identifiant de certificat SSL: ${data.id_ssl}</p>
                        <p>Date debut: ${data.date_debut}</p>
                        <p>Date fin: ${data.date_fin}</p>
                        <p>Application source: ${data.application_source}</p>
                    `;
                

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
        </script>
    </div>
</body>

</html>
