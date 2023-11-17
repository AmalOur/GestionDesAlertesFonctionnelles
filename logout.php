<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_alerte";

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

$user_id = $_SESSION["user_id"];
$dateSortie = date("Y-m-d H:i:s");
$stmtUpdate = $pdo->prepare("UPDATE trace_connexion SET date_sortie = :date_sortie, heure_sortie = CURTIME() WHERE id_compte = :user_id");
$stmtUpdate->bindParam(':date_sortie', $dateSortie);
$stmtUpdate->bindParam(':user_id', $user_id);
$stmtUpdate->execute();

unset($_SESSION["logged_in"]);
unset($_SESSION["user_id"]);
unset($_SESSION["user_role"]);
unset($_SESSION["user_name"]);

header("Location: index.php");
exit();
?>
