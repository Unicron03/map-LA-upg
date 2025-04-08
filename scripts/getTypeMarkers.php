<?php

try {
    // Récupération de la connexion à la base de données
    $pdo = Database::get();

    // Récupération des catégories mères
    $stmt = $pdo->query("SELECT nom FROM typemarker WHERE subId IS NOT NULL");
    $marqueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_SESSION['categoriesAll'] = array_map(function ($marqueur) {
        return $marqueur['nom'];
    }, $marqueurs);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Récupère les catégories d'une catégorie mère donnée
*/
function getTypeMarkersBySubID($id) {
    $pdo = Database::get();

    $query = "
        SELECT nom
        FROM typemarker
        WHERE subId = :id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->fetchAll();
}
?>
