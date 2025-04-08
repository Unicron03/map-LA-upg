<?php
session_start();

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=localhost;dbname=map-LA", $_SESSION["pdoUserName"], $_SESSION["pdoUserPassword"], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Vérification de l'ID passé en paramètre POST
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        die('ID manquant'); // Si l'ID est absent, afficher un message d'erreur simple.
    }

    $id = $_POST['id'];

    // Préparer et exécuter la requête
    $stmt = $pdo->prepare("SELECT typeMarker FROM marker WHERE id = ?");
    $stmt->execute([$id]);
    $markerTypeId = $stmt->fetch(PDO::FETCH_ASSOC); // Récupère directement une ligne

    if ($markerTypeId['typeMarker'] == 16) {
        $stmt = $pdo->prepare("UPDATE marker SET complete = NOT complete WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("UPDATE userdata SET complete = NOT complete WHERE idMarker = ? AND userId = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
    }

    // Terminer simplement l'exécution après la mise à jour
    exit();
} catch (PDOException $e) {
    die('Erreur SQL : ' . $e->getMessage()); // En cas d'erreur, afficher un message simple.
}