<?php
    session_start();

    $_SESSION["pdoUserName"] = "root";
    $_SESSION["pdoUserPassword"] = "";

    // Vérifie si l'utilisateur est connecté
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Import des scripts
    include 'scripts/services/database.php';
    include 'scripts/management/account/inscription.php';
    include 'scripts/management/account/connexion.php';
    include 'scripts/management/account/deconnexion.php';
    include 'scripts/phpMailer.php';
    include 'scripts/drawMarkers.php';
    include 'scripts/loadCatMarkers.php';
    include 'scripts/markerManagement.php';
    include 'scripts/activeSubCategory.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Link's Awakening Interactive Map</title>
        <link rel="icon" href="https://github.com/Unicron03/map-LA/blob/main/img/icon.png" type="image/png">

        <!-- Import css -->
        <link rel="stylesheet" href="css/leaflet.css"/>
        <link rel="stylesheet" href="css/index.css?v=2.4"/>
        <link rel="stylesheet" href="css/formMarker.css?v=2.4"/>
        <link rel="stylesheet" href="css/panel.css?v=2.4"/>
        <link rel="stylesheet" href="css/popupMarker.css?v=2.4"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    </head>
    <body>
        <!-- ------------------------------------------Permet de lancer autoexportbdd a chaque fois qu'un utilisateur se connecte------------------------------------------ -->
        <img src="/SAE/MAP-LA-UPG-MAIN/scripts/management/bdd/autoexportbdd.php" style="display: none;">
        <!-- ------------------------------------------Le Panel------------------------------------------ -->
        <div class="panel" onclick="closePopup()">       
            <!-- --------------------------------Section bandeau du panel-------------------------------- -->
            <div class="panel-flag">
                <div class="panel-flag-bandeau">
                    <!-- ------------------------------Bonton Connexion------------------------------ -->
                    <button onclick="toggleForm('login-form')">
                        <?php if (isLoggedIn()): ?>
                            <img src="img/icon-user-check.png" alt="icon-user" title=<?= "Welcome&nbsp;" . $_SESSION['username'];?>>
                        <?php else: ?>
                            <img src="img/icon-user.png" alt="icon-user"/>
                        <?php endif; ?>
                    </button>

                    <!-- --------------Bouton info (affiche le document de fonctionnement)-------------- -->
                    <button onclick="window.open('https://unicron03.github.io/map-LA/#fonctionnement', '_blank')">
                        <img id="icon-info" src="img/icon-info.png" alt="icon-info" title="Go to documentation" />
                    </button>
                    
                    <!-- -----------------------Bouton Minimiser (Cacher 'change')----------------------- -->
                    <button onclick="adjustPanelHeight()">
                        <img id="icon-maxi-mini" src="img/icon-minimise.png" alt="icon-maxi-mini" title="Minimise panel"/>
                    </button>
                </div>

                <h2 class="subtitle">Zelda: Link's Awakening Interactive Map</h2>
            </div>

            <!-- -----------------------------Section des filtres cachable----------------------------- -->
            <div id="change">
                <!-- ------------------------------Section filtres------------------------------ -->
                <div class="panel-controls" id="panel-controls">
                    <!-- ----------------------------Boutons Sélection/Déseléction tt catégories---------------------------- -->
                    <button id="toggle-all-button" onclick="activeMarkerById('switch')" class="icon-toggle">
                        Select/Deselect All Categories
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <!-- <button onclick="activeMarkerById('all')">Select All Categories</button> -->

                    <?php if (isLoggedIn()): ?>
                        <button id="toggle-like-button" onclick="activeLikeMarkMarkers('like')" class="icon-toggle">
                            Show/Hide Favorites
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button id="toggle-mark-button" onclick="activeLikeMarkMarkers('mark')" class="icon-toggle">
                            Show/Hide Completed
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- --------------------Section catégories filtres-------------------- -->
                <div id="panel-icons" class="scrollable-panel"></div>

                <!-- -----------------Formulaire connexion/deconnexion------------------------------------------------------------------------ -->
                <div id="login-form" class="form-container">
                    <?php if (!isLoggedIn()): ?>
                        <h2>Hey, listen! Welcome back!</h2>
                        <form id="formconnex" method="POST">
                            <input id="emailco" type="email" name="email" placeholder="Email" required>
                            <input id="passco" type="password" name="password" placeholder="Password" required>
                            <button type="submit" id="btnconnect" name="login">Login</button>
                        </form>
                        <div class="login-form-buttons">
                            <button onclick="toggleForm('register-form')">Register</button>
                            <button onclick="toggleForm('changePass-form')">Change password</button>
                        </div>
                    <?php else: ?>
                        <h2>Don't leave us! We love cats!</h2>
                        <div class="panel-controls">
                            <a href="?logout=true">Disconnect</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- -----------------Formulaire inscription------------------------------------------------------------------------ -->
                <div id="register-form" class="form-container">
                    <h2>It's dangerous to go alone! We're glad you're here with us.</h2>
                    <form id="forminsc" method="POST">
                        <input id="usere" type="text" name="username" placeholder="Username" required>
                        <input id="passre" type="password" name="password" placeholder="Password" required>
                        <input id="fullre" type="text" name="fullname" placeholder="Full name" required>
                        <input id="emailre" type="email" name="email" placeholder="Email" required>
                        <button id="registerbtn" type="submit" name="register">Register</button>
                    </form>
                </div>

                <!-- -----------------Formulaire changer mot de passe------------------------------------------------------------------------ -->
                <div id="changePass-form" class="form-container">
                    <h2>An email will be share at your adress</h2>
                    <form id="formconnex" method="POST">
                        <input id="emailchange" type="email" name="email" placeholder="Email" required>
                        <button id="btnconnect" type="submit" name="change">Share</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----------------------------Section d'affichage de la map---------------------------- -->
        <div id="map">
            <!-- -----------------Section des contôles de la map (zoom, reset, etc.)----------------- -->
            <div class="leaflet-top leaflet-left" style="top: 10px; right: 10px">
                <div class="leaflet-control-zoom leaflet-bar leaflet-control">
                    <a id="leaflet-control-reset" href="#" title="Reset view" role="button" aria-label="Reset view">o</a>
                </div>
            </div>
        </div>
        
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

        <script>
            let selectedCategories = [];
            let markers = [];

            var map = L.map('map', {
                zoomSnap: 1, // Zoom par paliers entiers
                zoomDelta: 1 // Contrôle la vitesse du zoom (facultatif)
            });

            // Ajout de la couche de tuiles
            L.tileLayer("https://unicron03.github.io/map-LA/mapBoard/{z}/{x}/{y}.png", {
                attribution: "",
                minZoom: 0,
                maxZoom: 4,
                noWrap: true // Empêche la répétition des tuiles horizontalement
            }).addTo(map);

            // Centrer la vue de la carte
            map.setView([0, 0], 2);

            // Bornes de la map
            var southWest = L.latLng(-70.5, -180); // Coin en bas à gauche
            var northEast = L.latLng(70.5, 180);   // Coin en haut à droite
            var bounds = L.latLngBounds(southWest, northEast);

            // Gestion des clics droits pour l'ajout de marker perso si connecté
            map.on('contextmenu', function (e) {
                <?php if (!isLoggedIn()): ?>
                    alert("To create personalised markers you need to log in!");
                    return;
                <?php endif; ?>

                // Obtenir l'élément sous le curseur
                const elementUnderCursor = document.elementFromPoint(e.originalEvent.clientX, e.originalEvent.clientY);

                // Fallback : vérifie si le curseur est en mode "pointer"
                const cursorStyle = window.getComputedStyle(elementUnderCursor).cursor;
                const isCursorPointer = cursorStyle === 'pointer';

                // Bloquer l'ouverture du popup si clic sur marker
                if (isCursorPointer) return;

                // Formulaire de création d'un marqueur personnel
                if (bounds.contains(e.latlng)) {
                    const coords = e.latlng;
                    const formContent = `   
                        <form class="form-marker" onsubmit="createMarker(event, ${coords.lng}, ${-coords.lat}, this);">
                            <div>
                                <label for="markerTitle">Title :</label>
                                <input type="text" id="markerTitle" name="title" required maxlength="20" size="15">
                            </div>

                            <div>
                                <label for="markerDescription">Description :</label>
                                <textarea id="markerDescription" name="description" rows="3" cols="20"></textarea>
                            </div>

                            <button type="submit" title="Create the marker"><img class='icon-template' src='./img/icon-mark.png'/></button>
                        </form>
                    `;

                    L.popup()
                        .setLatLng(e.latlng)
                        .setContent(formContent)
                        .openOn(map);
                }
            });

            // Ferme tous les popups ouvert
            function closePopup() {
                map.closePopup();
            }

            // Ouvre le formulaire de modification d'un marker perso
            function openEditForm(titre, description, x, y, id, buttonElement) {
                var editFormContent = `
                    <form class="form-marker" onsubmit="updateMarker(event, ${id}, this);">
                        <div>
                            <label for="editMarkerTitle">Title :</label>
                            <input type="text" id="editMarkerTitle" name="title" value="${titre}" required maxlength="20" size="15">
                        </div>

                        <div>
                            <label for="editMarkerDescription">Description :</label>
                            <textarea id="editMarkerDescription" name="description" rows="3" cols="20">${description}</textarea>
                        </div>

                        <div style="display: flex;">
                            <button type="submit" title="Update the marker"><img class='icon-template' src='./img/icon-mark.png'/></button>
                            <button type="button" onclick="deleteMarker(event, ${id})" style="background: indianred;" title="Delete the marker">
                                <img class='icon-template' src='./img/icon-trash.png'/>
                            </button>
                        </div>
                    </form>
                `;

                L.popup()
                    .setLatLng([-y, x])
                    .setContent(editFormContent)
                    .openOn(map);
            }

            // Permet de rectifier la position du popup d'un marker lors de son ouverture, et d'initialiser les boutons de like et favoris
            function popupMarkerInit(id) {
                const popupPaneDiv = document.querySelector('.leaflet-pane.leaflet-popup-pane').lastElementChild;

                if (popupPaneDiv) {
                    const imgElement = popupPaneDiv.querySelector('img');

                    // Rectifier position si popup contient image de description
                    if (imgElement && imgElement.classList.contains('popupMarkerImg')) {
                        popupPaneDiv.style.left = '-211px'
                    }

                    // Vérifie si le badge "like" est présent sur le marker
                    const markerDiv = getMarkerElementByUnicId(id.toString());
                    const hasLikeBadge = markerDiv?.querySelector("img[src='./img/like.png']") !== null;

                    // Sélectionne le bouton "like" dans le popup
                    const likeButton = popupPaneDiv.querySelector("form[onsubmit*='markAsFavorite'] button");
                    if (likeButton) {
                        if (hasLikeBadge) {
                            likeButton.classList.add('popupMarker-button-checked');
                        } else {
                            likeButton.classList.remove('popupMarker-button-checked');
                        }
                    }

                    const hasMarkBadge = markerDiv?.querySelector("img[src='./img/mark.png']") !== null;

                    // Sélectionne le bouton "mark" dans le popup
                    const markButton = popupPaneDiv.querySelector("form[onsubmit*='markAsComplete'] button");
                    if (markButton) {
                        if (hasMarkBadge) {
                            markButton.classList.add('popupMarker-button-checked');
                        } else {
                            markButton.classList.remove('popupMarker-button-checked');
                        }
                    }
                }
            }

            // Permet l'ajout visuel de marker sur la map
            function addMarkersToMap(x, y, titre, iconUrl, popupContent, catId, catSubId, id) {
                const marker = L.marker([-y, x], { icon: iconUrl, title: titre, riseOnHover: true }).bindPopup(popupContent);
                marker.addTo(map);

                // Une fois le marqueur ajouté, tu peux accéder à son élément DOM
                const markerDiv = marker.getElement();
                if (markerDiv) {
                    markerDiv.dataset.catSubId = catSubId; // Stocke la catégorie dans un attribut HTML
                    markerDiv.dataset.catId = catId;
                    markerDiv.dataset.unicId = id;
                }

                // Recalcul la position du popup du marker lors du clic sur ce dernier pour eviter bug
                marker.on('click', function () {
                    popupMarkerInit(id);
                });
            }
        </script>

        <script>
            // Fonction pour ajuster la hauteur du panel
            function adjustPanelHeight() {
                if (document.getElementById('panel-controls').style.display == 'none') {
                    return;
                }

                if (document.getElementById('panel-icons').style.display == 'none') {
                    document.getElementById('panel-icons').style.display = 'flex';
                    document.getElementById("icon-maxi-mini").src = 'img/icon-minimise.png';
                    document.getElementById("icon-maxi-mini").title = 'Minimise panel';
                } else {
                    document.getElementById('panel-icons').style.display = 'none';
                    document.getElementById("icon-maxi-mini").src = 'img/icon-maximise.png';
                    document.getElementById("icon-maxi-mini").title = 'Maximise panel';
                }
            }

            // Réinitialise la vue de la carte
            document.getElementById('leaflet-control-reset').addEventListener('click', function(event) {
                event.preventDefault();
                map.setView([0, 0], 2);
            });
        </script>

        <?php
            renderMarkers();
            loadCatMarkers();
        ?>

        <script src="scripts/toggleForm.js"></script>
        <script>activeMarkerById("all")</script>

        <!-- Script pour activer/désactiver globalement les fonctions de console -->
        <script>
            // Sauvegarde des fonctions d'origine
            const originalConsole = {
                log: console.log,
                info: console.info,
                warn: console.warn,
                error: console.error,
                debug: console.debug
            };

            // Fonction pour basculer les logs
            function toggleConsoleLogs(enable) {
                consoleEnabled = enable;

                if (enable) {
                    console.log = originalConsole.log;
                    console.info = originalConsole.info;
                    console.warn = originalConsole.warn;
                    console.error = originalConsole.error;
                    console.debug = originalConsole.debug;
                } else {
                    console.log = console.info = console.warn = console.error = console.debug = function () {};
                }
            }

            toggleConsoleLogs(true);
        </script>
    </body>
</html>