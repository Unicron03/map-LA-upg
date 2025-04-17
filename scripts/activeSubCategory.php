
<?php
function getSubIdsGroupedByParent() {
    $pdo = Database::get();

    // Récupérer toutes les lignes
    $stmt = $pdo->prepare("SELECT id, subId FROM typemarker");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];

    foreach ($rows as $row) {
        $id = (int)$row['id'];
        $subId = $row['subId'] !== null ? (int)$row['subId'] : null;

        if ($subId !== null) {
            // Ajouter dans la liste du parent
            if (!isset($grouped[$subId])) {
                $grouped[$subId] = [];
            }
            $grouped[$subId][] = $id;
        }
    }

    // Générer le JS
    echo "<script>\n";
    echo "const subIdsByParent = " . json_encode($grouped, JSON_PRETTY_PRINT) . ";\n";
    echo "</script>\n";
}

getSubIdsGroupedByParent(); // Appel de la fonction pour générer le JS
?>

<script>
    let filters = []; // On filtre via les ids des catégories

    function activeSubCategory(id, expandImg) {
        const panel = document.getElementById('panel-icons');
        const rotation = expandImg.style.rotate === '180deg' ? '0deg' : '180deg';
        const display = expandImg.style.rotate === '180deg' ? 'none' : 'flex';
        
        if (!panel) return;

        const forms = panel.querySelectorAll('button');

        forms.forEach(form => {
            if (Number(form.id) === id) {
                form.style.setProperty('display', display);
            }
        });
        expandImg.style.setProperty('rotate', rotation);
    }

    function activeMarkerById(id) {
        const markerPane = document.querySelector('.leaflet-pane.leaflet-marker-pane');
        if (!markerPane) return;

        const markerDivs = markerPane.querySelectorAll('div');

        if (id === "all") {
            // Activer toutes les sous-catégories disponibles
            filters = [];

            // On ajoute toutes les subIds présentes dans subIdsByParent
            for (let parentId in subIdsByParent) {
                filters.push(...subIdsByParent[parentId]);
            }

            // Optionnel : on retire les doublons au cas où
            filters = [...new Set(filters)];
        } else if (id === "none") {
            // Désactiver toutes les catégories
            filters = [];

        } else {
            // Obtenir tous les subIds associés à cet id
            let idsToFilter = [];

            if (subIdsByParent.hasOwnProperty(id)) {
                idsToFilter = subIdsByParent[id]; // Catégorie mère => on récupère les subIds
            } else {
                idsToFilter = [id]; // Catégorie fille => on utilise l'id comme subId directement
            }

            // Gestion des filtres (toggle)
            const allAlreadyActive = idsToFilter.every(subId => filters.includes(subId));
            if (allAlreadyActive) {
                // Retirer tous les subIds de filters
                filters = filters.filter(f => !idsToFilter.includes(f));
            } else {
                // Ajouter ceux qui ne sont pas encore dans filters
                idsToFilter.forEach(subId => {
                    if (!filters.includes(subId)) {
                        filters.push(subId);
                    }
                });
            }
        }

        console.log("Filtres actifs :", filters);

        // Mise à jour de l'affichage
        markerDivs.forEach((div) => {
            if (div.dataset.id) {
                const datasetId = Number(div.dataset.id);
                const match = filters.includes(datasetId);
                div.style.setProperty('display', match ? 'block' : 'none');
            }
        });
    }
</script>