<?php

/**
 * Renvoi l'id et le subId de la catégorie (pour évaluation si c'est un catégorie mère ou non).
*/
function isCatAGroup($pdo, $category) {
    if ($category == "Favorites" || $category == "Completed") {
        return false;
    }

    $query = "
        SELECT id, subId
        FROM typemarker
        WHERE nom LIKE :category
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':category', "%$category%", PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Récupère les marqueurs depuis la base de données.
*/
function fetchMarkers($pdo, $userId, $category, $complete, $favorite) {
    $parCat = isCatAGroup($pdo, $category);

    // Début de la requête SQL
    $query = "
        SELECT 
            m.id, m.x, m.y, m.titre, m.description, m.image, 
            COALESCE(u.favorite, m.favorite) AS favorite, 
            COALESCE(u.complete, m.complete) AS complete, 
            m.source, m.userID,
            t.image AS imgCat, t.nom AS nomCat, t.id AS typeId, t.subId
        FROM marker m
        JOIN typemarker t ON m.typeMarker = t.id
        LEFT JOIN userdata u ON u.idMarker = m.id AND u.userId = :userId
        WHERE (m.userID = :userId OR m.userID IS NULL)
    ";

    // Ajout de filtres selon la catégorie
    if ($category == "Favorites") {
        $query .= " AND COALESCE(u.favorite, m.favorite) = 1";
    } elseif ($category == "Completed") {
        $query .= " AND COALESCE(u.complete, m.complete) = 1";
    } else {
        if (!$parCat[0]['subId']) {
            $query .= " AND t.subId = :id";
        } else {
            $query .= " AND t.nom LIKE :category";
        }
    }

    // Ajout des filtres pour complete et favorite
    if ($complete == false) {
        $query .= " AND COALESCE(u.complete, m.complete) = 0";
    }
    if ($favorite == false) {
        $query .= " AND COALESCE(u.favorite, m.favorite) = 0";
    }

    // Préparation de la requête
    $stmt = $pdo->prepare($query);

    // Liaison des paramètres
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    if ($category != "Favorites" && $category != "Completed") {
        if (!$parCat[0]['subId']) {
            $stmt->bindValue(':id', $parCat[0]['id'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':category', "%$category%", PDO::PARAM_STR);
        }
    }

    // Exécution et retour des résultats
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Génère et affiche les marqueurs sous forme de script JavaScript.
*/
function renderMarkers($category = null, $complete = false, $favorite = false) {
    $pdo = Database::get();
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $markers = fetchMarkers($pdo, $userId, $category, $complete, $favorite);

    echo "<script>";
    foreach ($markers as $marker) {
        generateMarkerScript($marker);
    }
    echo "</script>";
}

/**
 * Génère le script JavaScript pour un marqueur.
*/
function generateMarkerScript($marker) {
    $id = $marker['id'];
    $x = $marker['x'];
    $y = $marker['y'];
    $titre = addslashes(strval($marker['titre']));
    $description = addslashes(strval($marker['description']));
    $sourceLink = addslashes(strval($marker['source']));
    $nomCat = addslashes(strval($marker['nomCat']));
    $typeId = $marker['typeId'];
    $favorite = $marker['favorite'];
    $complete = $marker['complete'];
    $popupImage = !empty($marker['image']) ? 'data:image/png;base64,' . base64_encode($marker['image']) : '';
    $iconBase64 = 'data:image/png;base64,' . base64_encode($marker['imgCat']);

    echo "
        var customIcon = L.icon({
            iconUrl: '$iconBase64',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32],
        });

        var popupContent = `<div class='popupMarker' " . ($typeId == 16 ? "style='min-width: 280px;'" : "") . ">
            <h1 class='subtitle'>$titre</h1>
            " . (!empty($popupImage) ? "<img src='$popupImage' style='max-width: 382px;'>" : "") . "
            <div id='info-popup-marker' " . (empty($description) && empty($sourceLink) ? "style='display: none;'" : "") . ">
                " . (!empty($description) ? "<p>$description</p>" : "") . "
                " . (!empty($sourceLink) ? "<p>Source: <a href='$sourceLink' target='_blank'>$nomCat Guide</a></p>" : "") . "
            </div>
            <div id='btn-popup-marker'>
                " . ($sourceLink ? "<a href='$sourceLink' target='_blank' class='button-link'>
                    <button>
                        <img class='icon-template' src='./img/icon-globe.png' title='Go to Source'/>
                    </button>
                </a>" : "") . "
                <form onsubmit='markAsFavorite(event, $id);'>
                    <button " . ($favorite == 1 ? "class='popupMarker-button-checked'" : "") . (isLoggedIn() ? "" : "disabled") . ">
                        <img class='icon-template' 
                            src='./img/icon-favorite.png' 
                            title='" . (isLoggedIn() ? "Mark as Favorite" : "Log in to use this feature") . "'
                        />
                    </button>
                </form>
                <form onsubmit='markAsComplete(event, $id);'>
                    <button 
                        " . ($complete == 1 ? "class='popupMarker-button-checked'" : "") . (isLoggedIn() ? "" : "disabled") . ">
                        <img class='icon-template' 
                            src='./img/icon-mark.png' 
                            title='" . (isLoggedIn() ? "Mark as Complete" : "Log in to use this feature") . "'
                        />
                    </button>
                </form>
                " . ($typeId == 16 ? "<button onclick=\"openEditForm('$titre', '$description', $x, $y, $id, this)\">
                    <img class='icon-template' src='./img/icon-modif.png' title='Modify the Marker'/>
                </button>" : "") . "
            </div>
        </div>`;

        addMarkersToMap($x, $y, '$titre', customIcon, popupContent);
    ";
}
?>