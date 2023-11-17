<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_alerte";

$subject = "Alerte d'expiration";
$headers = "From: oumaima.dagoun@gmail.com\r\n";
$headers .= "Reply-To: oumaima.dagoun@gmail.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function fetchCompteEmails($conn) {
    $sql = "SELECT email FROM compte";
    $stmt = $conn->prepare($sql);
    $stmt->execute(); 
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function fetchExpiringCertificates($conn) {
    $sql = "SELECT cs.*, 'Certificate' as record_type FROM certificat_ssl cs
    WHERE DATEDIFF(cs.date_fin, CURDATE()) <= cs.jour_avant_expiration";

    $stmt = $conn->prepare($sql);
    $stmt->execute(); 
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchExpiringContracts($conn) {
    $sql = "SELECT co.*, 'Contract' as record_type FROM contrat co
            WHERE DATEDIFF(co.date_fin, CURDATE()) <= co.jour_avant_expiration";

    $stmt = $conn->prepare($sql);
    $stmt->execute(); 
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchExpiringContractMaintenance($conn) {
    $sql = "SELECT dm.*, co.application_source, co.nom_societe, 'Contract Maintenance' as record_type FROM details_maintenance dm
            INNER JOIN contrat co ON co.id_contrat = dm.id_contrat
            WHERE DATEDIFF(dm.date_fin, CURDATE()) <= dm.jour_avant_expiration";

    $stmt = $conn->prepare($sql);
    $stmt->execute(); 
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function combineResults() {
    $certificates = fetchExpiringCertificates($GLOBALS['conn']);
    $contracts = fetchExpiringContracts($GLOBALS['conn']);
    $contractMaintenance = fetchExpiringContractMaintenance($GLOBALS['conn']);

    $allExpiringRecords = array_merge($certificates, $contracts, $contractMaintenance);
    return $allExpiringRecords;
}

$message = "<p>Bonjour,</p>";
$message .= "<p>Ceci est un email automatique.</p>";
$message .= "<p>Les enregistrements ci-dessous expireront bientot :</p>";

function addRecordToMessage($record) {
    $recordType = $record['record_type'];

    switch ($recordType) {
        case 'Certificate':
            $id = isset($record['id_ssl']) ? $record['id_ssl'] : "N/A";
            $date_fin = isset($record['date_fin']) ? $record['date_fin'] : "N/A";
            $application_source = isset($record['application_source']) ? $record['application_source'] : "N/A";
            return "<p>Certificat SSL: <br> ID: {$id} <br> Date d'expiration: {$date_fin} <br> Application Source: {$application_source}</p>";

        case 'Contract':
            $id = isset($record['id_contrat']) ? $record['id_contrat'] : "N/A";
            $date_fin = isset($record['date_fin']) ? $record['date_fin'] : "N/A";
            $nom_societe = isset($record['nom_societe']) ? $record['nom_societe'] : "N/A";
            $tel = isset($record['tel']) ? $record['tel'] : "N/A";
            return "<p>Contrat: <br> ID: {$id} <br> Date d'expiration: {$date_fin} <br> Nom de la société: {$nom_societe} <br> Téléphone: {$tel}</p>";

        case 'Contract Maintenance':
            $id = isset($record['id_maintenance']) ? $record['id_maintenance'] : "N/A";
            $date_fin = isset($record['date_fin']) ? $record['date_fin'] : "N/A";
            $resultat = isset($record['resultat']) ? $record['resultat'] : "N/A";
            $nom_societe = isset($record['nom_societe']) ? $record['nom_societe'] : "N/A";
            $application_source = isset($record['application_source']) ? $record['application_source'] : "N/A";
            return "<p>Contrat de maintenance: <br> ID: {$id} <br> Date d'expiration: {$date_fin} <br> Résultat: {$resultat} <br> Nom de la société: {$nom_societe} <br> L'application source : {$application_source} </p>";

        default:
            return "<p>N/A</p>";
    }}

$allExpiringRecords = combineResults();

if (!empty($allExpiringRecords)) {
    $compteEmails = fetchCompteEmails($conn);
    $groupedRecords = array();

    foreach ($compteEmails as $email) {
        $groupedRecords[$email] = $allExpiringRecords;
    }

    function insertNotificationContrat($conn, $idContrat, $email) {
        $sql = "SELECT id_compte FROM compte WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $idCompte = $result['id_compte'];
    
        $sql = "INSERT INTO notification_contrat (date_envoi, id_contrat, heure_envoi, id_compte) VALUES (CURDATE(), :idContrat, CURTIME(), :idCompte)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idContrat', $idContrat);
        $stmt->bindParam(':idCompte', $idCompte);
        $stmt->execute();
    }

    function insertNotificationMaintenance($conn, $idMaintenance, $email) {
        $sql = "SELECT id_compte FROM compte WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $idCompte = $result['id_compte'];
    
        $sql = "INSERT INTO notification_maintenance (date_envoi, heure_envoi, id_maintenance, id_compte) VALUES (CURDATE(), CURTIME(), :idMaintenance, :idCompte)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idMaintenance', $idMaintenance);
        $stmt->bindParam(':idCompte', $idCompte);
        $stmt->execute();
    }
    

    function insertNotificationSSL($conn, $idSSL, $email) {
        $sql = "SELECT id_compte FROM compte WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $idCompte = $result['id_compte'];
    
        $sql = "INSERT INTO notification_ssl (date_envoi, id_ssl, heure_envoi, id_compte) VALUES (CURDATE(), :idSSL, CURTIME(), :idCompte)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idSSL', $idSSL);
        $stmt->bindParam(':idCompte', $idCompte);
        $stmt->execute();
    }
    

    foreach ($groupedRecords as $email => $records) {
        $emailMessage = $message;
        $emailMessage .= "<p>Enregistrements pour {$email}:</p>";
        foreach ($records as $record) {
            $emailMessage .= addRecordToMessage($record);

            $recordType = $record['record_type'];
            switch ($recordType) {
                case 'Certificate':
                    $idSSL = isset($record['id_ssl']) ? $record['id_ssl'] : null;
                    if ($idSSL) {
                        insertNotificationSSL($conn, $idSSL, $email);
                    }
                    break;
    
                case 'Contract':
                    $idContrat = isset($record['id_contrat']) ? $record['id_contrat'] : null;
                    if ($idContrat) {
                        insertNotificationContrat($conn, $idContrat, $email); 
                    }
                    break;

                case 'Contract Maintenance':
                    $idMaintenance = isset($record['id_maintenance']) ? $record['id_maintenance'] : "N/A";        
                    if ($idMaintenance) {
                        insertNotificationMaintenance($conn, $idMaintenance, $email); 
                    }
                    break;
        
                default:
                    return "<p>N/A</p>";
            }
        }

        sendEmailToCompte($email, $emailMessage, $subject, $headers);
    }
}

function sendEmailToCompte($email, $message, $subject, $headers) {
    $message.= "<p>Cordialement,<br>Votre équipe CDG.</p>";
    return mail($email, $subject, $message, $headers);
}

$conn = null;
?>