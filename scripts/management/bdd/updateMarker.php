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
    
    $id = $data['id'];
    $titre = $data['titre'];
    $description = $data['description'];

    // Préparer et exécuter la requête
    $stmt = $pdo->prepare("UPDATE marker SET titre = :titre, description = :description WHERE id = :id");
    if ($stmt->execute([':id' => $id, ':titre' => $titre, ':description' => $description])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
