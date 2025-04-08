<?php
header('Content-Type: application/json');  // Retour JSON
session_start();

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=localhost;dbname=map-LA", $_SESSION["pdoUserName"], $_SESSION["pdoUserPassword"], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Récupération des données JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    $complete = $data['complete'];

    // Préparer et exécuter la requête
    $stmt = $pdo->prepare("INSERT INTO marker (x, y, titre, description, typeMarker, userID) VALUES (:x, :y, :titre, :description, :typeMarker, :userID)");
    if ($stmt->execute([':x' => $x, ':y' => $y, ':titre' => $titre, ':description' => $description, ':typeMarker' => $typeMarker, ':userID' => $userID])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'insertion']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>