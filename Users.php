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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id_compte = $_POST['delete'];

        $stmtDelete = $pdo->prepare("DELETE FROM compte WHERE id_compte = :id_compte");
        $stmtDelete->bindParam(':id_compte', $id_compte);
        $stmtDelete->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['edit_id'])) {
        $id_compte = $_POST['edit_id'];
        $prenom = $_POST['edit_prenom'];
        $nom = $_POST['edit_nom'];
        $email = $_POST['edit_email'];
        $role = $_POST['edit_role'];

        $stmtUpdate = $pdo->prepare("UPDATE compte SET prenom = :prenom, nom = :nom, email = :email, role = :role WHERE id_compte = :id_compte");
        $stmtUpdate->bindParam(':id_compte', $id_compte);
        $stmtUpdate->bindParam(':prenom', $prenom);
        $stmtUpdate->bindParam(':nom', $nom);
        $stmtUpdate->bindParam(':email', $email);
        $stmtUpdate->bindParam(':role', $role);
        $stmtUpdate->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_id']) && isset($_POST['add_prenom']) && isset($_POST['add_nom']) && isset($_POST['add_email']) && isset($_POST['add_role']) && isset($_POST['add_password'])) {
            $id = $_POST['add_id'];
            $prenom = $_POST['add_prenom'];
            $nom = $_POST['add_nom'];
            $email = $_POST['add_email'];
            $role = $_POST['add_role'];
            $password = $_POST['add_password'];
    
            $hashed_password = md5($password);
    
            $stmtCheckID = $pdo->prepare("SELECT * FROM compte WHERE id_compte = :id");
            $stmtCheckID->bindParam(':id', $id);
            $stmtCheckID->execute();
            $existingAccount = $stmtCheckID->fetch(PDO::FETCH_ASSOC);
    
            if ($existingAccount) {
                $errorMessage = "ID déjà existant";
            } else {
                $stmtAdd = $pdo->prepare("INSERT INTO compte (id_compte, prenom, nom, email, role, mot_de_passe) VALUES (:id, :prenom, :nom, :email, :role, :password)");
                $stmtAdd->bindParam(':id', $id);
                $stmtAdd->bindParam(':prenom', $prenom);
                $stmtAdd->bindParam(':nom', $nom);
                $stmtAdd->bindParam(':email', $email);
                $stmtAdd->bindParam(':role', $role);
                $stmtAdd->bindParam(':password', $hashed_password);
                $stmtAdd->execute();
    
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        }
    }    
}

$stmtCompte = $pdo->prepare("SELECT * FROM compte");
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

$stmtCompte = $pdo->prepare("SELECT * FROM compte LIMIT :offset, :rowsPerPage");
$stmtCompte->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtCompte->bindValue(':rowsPerPage', $rowsPerPage, PDO::PARAM_INT);
$stmtCompte->execute();
$Comptes = $stmtCompte->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="css/Header.css">
    <link rel="stylesheet" href="css/usersStyle.css">
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
                <a class="add" href="#AddPopUp">
                  <i class="ph ph-plus"></i>
                </a>
                <div class="search">
                <input type="text" placeholder="Chercher" id="searchInput"/>
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
                            <a href="Administration.php" style="margin-left: 0px">Gestion des Utilisateurs</a>
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
      <h1>Liste des utilisateurs</h1>
        <table id="myTable" class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Prénom</th>
                    <th scope="col">Nom</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($Comptes as $index => $compte) {
                echo "<tr>";
                echo "<td>" . $compte['id_compte'] . "</td>";
                echo "<td>" . $compte['prenom'] . "</td>";
                echo "<td>" . $compte['nom'] . "</td>";
                echo "<td>" . $compte['email'] . "</td>";
                echo "<td>" . $compte['role'] . "</td>";
                echo "<td><a class='edit-icon' onclick='editRow(this)' href='#ModifyPopUp'><i class='ph ph-pencil'></i></a>
                    <form method='post' style='display: inline;' onsubmit='return confirm(\"Vous êtes sûr de vouloir supprimer cet utilisateur?\")'>
                        <input type='hidden' name='delete' value='" . $compte['id_compte'] . "'>
                        <button type='submit' class='delete-icon'><i class='ph ph-trash'></i></button>
                    </form>
                </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
        </table>
    </div>

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


    <div class="overlay" id="AddPopUp">
        <div class="wrapper">
            <h2>Ajouter utilisateur</h2>
            <a href="#" class="close">&times;</a>
            <div class="cont">
                <div class="form">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <label>ID Utilisateur</label>
                        <input type="number" name="add_id" placeholder="ID..." required>
                        <label>Prénom</label>
                        <input type="text" name="add_prenom" placeholder="Prénom" required>
                        <label>Nom</label>
                        <input type="text" name="add_nom" placeholder="Nom" required>
                        <label>Email</label>
                        <input type="email" name="add_email" placeholder="Email" required>
                        <label>Mot de passe</label>
                        <input type="password" name="add_password" pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$" 
                        title="Le mot de passe doit contenir au moins une lettre, un chiffre et un caractère spécial (@$!%*#?&), et faire au moins 8 caractères." 
                        placeholder="Mot de passe" required>                    
                        <label>Role</label>
                        <select name="add_role" id="add_role" required>
                            <option value="">Sélectionner un rôle</option>
                            <option value="Admin">Admin</option>
                            <option value="Consultant">Consultant</option>
                        </select>
                        <input type="submit" value="Ajouter" class="formButton">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay" id="ModifyPopUp">
        <div class="wrapper">
            <h2>Modifier utilisateur</h2>
            <a href="#" class="close">&times;</a>
            <div class="cont">
                <div class="form">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <label>Prénom</label>
                        <input type="text" name="edit_prenom" placeholder="Prénom" required>
                        <label>Nom</label>
                        <input type="text" name="edit_nom" placeholder="Nom" required>
                        <label>Email</label>
                        <input type="email" name="edit_email" placeholder="Email" required>
                        <label>Role</label>
                        <select name="edit_role" id="edit_role" required>
                            <option value="">Sélectionner un rôle</option>
                            <option value="Admin">Admin</option>
                            <option value="Consultant">Consultant</option>
                        </select>
                        <input type="hidden" name="edit_id" id="edit_id">
                        <input type="submit" value="Modifier" class="formButton">
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

<script src='https://unpkg.com/phosphor-icons'></script>
<script>
    function filterTable() {
        var searchInput = document.getElementById("searchInput").value.toLowerCase();

        var table = document.getElementById("myTable");
        var rows = table.getElementsByTagName("tr");

        for (var i = 1; i < rows.length; i++) {
            var row = rows[i];
            var firstName = row.cells[1].textContent.toLowerCase();
            var lastName = row.cells[2].textContent.toLowerCase();
            var email = row.cells[3].textContent.toLowerCase();
            var role = row.cells[4].textContent.toLowerCase();

            if (
                firstName.includes(searchInput) ||
                lastName.includes(searchInput) ||
                email.includes(searchInput) ||
                role.includes(searchInput)
            ) {
                row.style.display = "";
                table.style.height = "auto";
            } else {
                row.style.display = "none";
                
            } 
        }
    }

    document.getElementById("searchInput").addEventListener("input", filterTable);

    function editRow(element) {
        var row = element.closest('tr');
        var id = row.cells[0].textContent;
        var prenom = row.cells[1].textContent;
        var nom = row.cells[2].textContent;
        var email = row.cells[3].textContent;
        var role = row.cells[4].textContent;

        document.getElementById("edit_id").value = id;
        document.querySelector("#ModifyPopUp input[name='edit_prenom']").value = prenom;
        document.querySelector("#ModifyPopUp input[name='edit_nom']").value = nom;
        document.querySelector("#ModifyPopUp input[name='edit_email']").value = email;
        document.querySelector("#ModifyPopUp select[name='edit_role']").value = role;

        var modifyPopup = document.getElementById("ModifyPopUp");
        modifyPopup.style.display = "block";
    }

    function showAlert(message) {
        alert(message);
    }

    <?php
    if (!empty($errorMessage)) {
        echo "showAlert('$errorMessage');";
    }
    ?>

</script>
</html>