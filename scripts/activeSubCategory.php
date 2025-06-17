
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
    let additionnalFilters = []; // Filtres additionels de like et mark
    let switchAllCategories = "all"; // Filtres bascule permettant d'activer/désactiver tous les marqueurs

    function eyeBtnToggle(button, enability) {
        const icon = button.querySelector('i');

        if (enability) {
            button.classList.remove('active');
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            button.classList.add('active');
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }

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

    function getIdBySubId() {
        const parentBySubId = {};
        
        for (const parentId in subIdsByParent) {
            subIdsByParent[parentId].forEach(subId => {
                parentBySubId[subId] = Number(parentId);
            });
        }

        return parentBySubId;
    }

    function activeLikeMarkMarkers(param) {
        const markerPane = document.querySelector('.leaflet-pane.leaflet-marker-pane');
        if (!markerPane) return;

        // Ajoute ou retire le filtre
        const index = additionnalFilters.indexOf(param);
        console.log(additionnalFilters, index);
        if (index === -1) {
            additionnalFilters.push(param);
        } else {
            additionnalFilters.splice(index, 1);
        }

        markerPane.querySelectorAll('div').forEach((div) => {
            const markerImgDiv = div.querySelector('div'); // div contenant l'image
            if (!markerImgDiv || markerImgDiv.children.length == 1) return;
            let hasDisplay = true;

            for (let filter of additionnalFilters) {
                if (markerImgDiv.querySelector(`img[src='./img/${filter}.png']`)) {
                    div.style.setProperty('display', 'none');
                    hasDisplay = false;
                    break;
                }
            }

            // console.log(filters.includes(div.dataset.catId), div.dataset.catId, typeof div.dataset.catId);
            if (hasDisplay) {
                if (filters.length === 0 || filters.includes(Number(div.dataset.catId))) {
                    div.style.setProperty('display', 'block');
                }
            }
        });

        // Toggle filtre
        eyeBtnToggle(document.getElementById(`toggle-${param}-button`), !additionnalFilters.includes(param));
    }

    function activeMarkerById(id, categoryClicked) {
        const panelFilter = document.getElementById('panel-icons');
        const markerPane = document.querySelector('.leaflet-pane.leaflet-marker-pane');
        if (!markerPane) return;

        const markerDivs = markerPane.querySelectorAll('div');

        // Switcher l'état de sélection générale 'all' ou 'none'
        if (id === "switch") id = switchAllCategories = switchAllCategories == "all" ? "none" : "all";
        
        // Toggle filtre
        if (id === "all") eyeBtnToggle(document.getElementById(`toggle-all-button`), true);
        else if (id === "none") eyeBtnToggle(document.getElementById(`toggle-all-button`), false);

        if (id === "all") {
            // Activer toutes les sous-catégories disponibles
            filters = [];

            // On ajoute toutes les subIds présentes dans subIdsByParent
            for (let parentId in subIdsByParent) {
                filters.push(...subIdsByParent[parentId]);
            }

            // Optionnel : on retire les doublons au cas où
            filters = [...new Set(filters)];

            panelFilter.querySelectorAll('button').forEach((filter) => {
                filter.style.opacity = "1";
            });
        } else if (id === "none") {
            // Désactiver toutes les catégories
            filters = [];
            
            panelFilter.querySelectorAll('button.panel-icons-element').forEach((filter) => {
                filter.style.opacity = '0.4';
            });
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

            // Active/Désactive les sous-catégories
            panelFilter.querySelectorAll('button').forEach((filter) => {
                if (filter.id) {
                    const opacity = filters.includes(Number(filter.dataset.catId)) ? "1" : "0.4";
                    filter.style.opacity = opacity;
                }
            });

            // Active/Désactive la catégorie mère cliquée si l'une de ses filles est active
            const subIds = subIdsByParent[id];
            if (subIds) { // S'active si on clic sur une catégorie
                const hasInvalidFilter = subIds.some(subId => !filters.includes(subId));
                categoryClicked.style.opacity = hasInvalidFilter ? "0.4" : "1";
            }

            // Active/Désactive la catégorie mère d'une catégorie fille cliquée si l'une des filles est active
            const parentId = getIdBySubId()[id];
            panelFilter.querySelectorAll('button').forEach((filter) => {
                if (Number(filter.dataset.id) === parentId) {
                    filter.style.opacity = subIdsByParent[parentId].some(subId => filters.includes(subId)) ? "1" : "0.4";
                }
            });
        }

        // console.log("Filtres actifs :", filters);

        // Mise à jour de l'affichage
        markerDivs.forEach((div) => {
            if (div.dataset.catId) {
                const datasetId = Number(div.dataset.catId);
                const match = filters.includes(datasetId);
                div.style.setProperty('display', match ? 'block' : 'none');
            }
        });

        // Toggle filtre
        // if (id === "all") {
            // additionnalFilters = ["like", "mark"];
            // eyeBtnToggle(document.getElementById(`toggle-like-button`), true);
            // eyeBtnToggle(document.getElementById(`toggle-mark-button`), true);
        // } else if (id === "none") {
            // additionnalFilters = [];
            // eyeBtnToggle(document.getElementById(`toggle-like-button`), false);
            // eyeBtnToggle(document.getElementById(`toggle-mark-button`), false);
        // }
    }
</script>