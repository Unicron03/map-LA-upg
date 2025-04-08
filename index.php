<?php
session_start();

$_SESSION["pdoUserName"] = "root";
$_SESSION["pdoUserPassword"] = "";

// Initialisation des variables de session
if (!isset($_SESSION['categoriesAll'])) {
    $_SESSION['categoriesAll'] = [];
}
if (!isset($_SESSION['categoriesMother'])) {
    $_SESSION['categoriesMother'] = [];
}
$_SESSION['complete'] = isset($_SESSION['complete']) ? $_SESSION['complete'] : true;
$_SESSION['favorite'] = isset($_SESSION['favorite']) ? $_SESSION['favorite'] : true;
$_SESSION['param'] = isset($_SESSION['param']) ? $_SESSION['param'] : null;

// Vérifie si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Import des scripts
include 'scripts/services/database.php';
include 'scripts/getTypeMarkers.php';
include 'scripts/management/account/inscription.php';
include 'scripts/management/account/connexion.php';
include 'scripts/management/account/deconnexion.php';
include 'scripts/phpMailer.php';
include 'scripts/drawMarkers.php';
include 'scripts/loadCatMarkers.php';
include 'scripts/markerManagement.php';

if (!isset($_SESSION['categories'])) {
    $_SESSION['categories'] = $_SESSION['categoriesAll'];
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Link's Awakening Interactive Map</title>
        <link rel="icon" href="https://github.com/Unicron03/map-LA/blob/main/img/icon.png" type="image/png">

        <!-- Import css -->
        <link rel="stylesheet" href="css/leaflet.css"/>
        <link rel="stylesheet" href="css/index.css?v=2.4"/>
        <link rel="stylesheet" href="css/panel.css?v=2.4"/>
        <link rel="stylesheet" href="css/formMarker.css?v=2.4"/>
        <link rel="stylesheet" href="css/popupMarker.css?v=2.4"/>
    </head>
    <body>
        <!-- ------------------------------------------Le Panel------------------------------------------ -->
        <div class="panel">       
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
                    <?php if (isLoggedIn()): ?>
                        <!-- ---------------------------Formulaire affichage/désaffichage complétés--------------------------- -->
                        <form class="form-filter" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" style="width: -webkit-fill-available; margin: 0;">
                            <input type="hidden" name="param" value="complete">
                            <button type="submit">
                                <?php if ($_SESSION['complete'] == true): ?>
                                    Show Completed
                                <?php else: ?>
                                    Hide Completed
                                <?php endif; ?>
                            </button>
                        </form>

                        <!-- ---------------------------Formulaire affichage/désaffichage favoris--------------------------- -->
                        <form class="form-filter" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" style="width: -webkit-fill-available; margin: 0;">
                            <input type="hidden" name="param" value="favorite">
                            <button type="submit">
                                <?php if ($_SESSION['favorite'] == true): ?>
                                    Show Favorites
                                <?php else: ?>
                                    Hide Favorites
                                <?php endif; ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- ----------------------------Formulaire Sélection/Déseléction tt catégories---------------------------- -->
                    <form class="form-filter" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" style="width: -webkit-fill-available; margin: 0;">
                        <input type="hidden" name="param" value="none">
                        <button type="submit">
                            Deselect All Categories
                        </button>
                    </form>
                    <form class="form-filter" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" style="width: -webkit-fill-available; margin: 0;">
                        <input type="hidden" name="param" value="all">
                        <button type="submit">
                            Select All Categories
                        </button>
                    </form>
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
            map.on('contextmenu', function(e) {
                <?php if (!isLoggedIn()): ?>
                    alert("To create personalised markers you need to log in!");
                    return;
                <?php endif; ?>

                if (bounds.contains(e.latlng)) {
                    var coords = e.latlng;
                    var formContent = `
                        <form class="form-marker" onsubmit="createMarker(event, ${coords.lng}, ${-coords.lat}, this);">
                            <label for="markerTitle">Title :</label><br>
                            <input type="text" id="markerTitle" name="title" required maxlength="20" size="15"><br><br>
                            <label for="markerDescription">Description :</label><br>
                            <textarea id="markerDescription" name="description" rows="3" cols="20"></textarea><br><br>
                            <button type="submit">Create Marker</button>
                        </form>
                    `;

                    L.popup()
                        .setLatLng(e.latlng)
                        .setContent(formContent)
                        .openOn(map);
                }
            });

            // Ouvre le formulaire de modification d'un marker perso
            function openEditForm(titre, description, x, y, id, buttonElement) {
                var editFormContent = `
                    <form class="form-marker" onsubmit="updateMarker(event, ${id}, this);">
                        <label for="editMarkerTitle">Title :</label><br>
                        <input type="text" id="editMarkerTitle" name="title" value="${titre}" required maxlength="20" size="15"><br><br>
                        <label for="editMarkerDescription">Description :</label><br>
                        <textarea id="editMarkerDescription" name="description" rows="3" cols="20">${description}</textarea><br><br>
                        <button type="submit">Validate</button>
                        <button type="button" onclick="deleteMarker(event, ${id})" style="background-color: red; color: white;">Delete</button>
                    </form>
                `;

                L.popup()
                    .setLatLng([-y, x])
                    .setContent(editFormContent)
                    .openOn(map);
            }

            // Permet l'ajout visuel de marker sur la map
            function addMarkersToMap(x, y, titre, iconUrl, popupContent) {
                // y+1 sur la SQL par rapport au local
                L.marker([-y, x], { icon: iconUrl, title: titre, riseOnHover: true })
                    .bindPopup(popupContent)
                    .addTo(map);
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
            // Gestion des commandes des filtres
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $param = $_POST['param']; // Catégorie envoyée depuis le formulaire
            
                if ($param == "complete") {
                    $_SESSION['complete'] = !$_SESSION['complete'];
                } elseif ($param == "favorite") {
                    $_SESSION['favorite'] = !$_SESSION['favorite'];
                } elseif ($param == "none") {
                    $_SESSION['categories'] = [];
                    $_SESSION['complete'] = true;
                    $_SESSION['favorite'] = true;
                } elseif ($param == "all") {
                    $_SESSION['categories'] = $_SESSION['categoriesAll'];
                    $_SESSION['complete'] = true;
                    $_SESSION['favorite'] = true;
                } else {
                    $parCat = isCatAGroup(Database::get(), $param);
                    if ($param != "Favorites" && $param != "Completed" && !$parCat[0]['subId']) {
                        $markers = getTypeMarkersBySubID($parCat[0]['id']);

                        foreach ($markers as $marker) {
                            if (in_array($marker['nom'], $_SESSION['categories'])) {
                                // Suppression
                                $_SESSION['categories'] = array_filter($_SESSION['categories'], function($category) use ($marker) {
                                    return $category !== $marker['nom'];
                                });                            
                            } else {
                                // Ajout
                                $_SESSION['categories'][] = $marker['nom'];
                            }
                        }
                    } else {
                        // Vérifie si la catégorie existe déjà dans la session
                        if (in_array($param, $_SESSION['categories'])) {
                            // Suppression
                            $_SESSION['categories'] = array_filter($_SESSION['categories'], function($category) use ($param) {
                                return $category !== $param;
                            });
                        } else {
                            // Ajout
                            $_SESSION['categories'][] = $param;
                        }
                    }
                }
            }

            // On affiche toutes les catégories séléctionner
            foreach ($_SESSION['categories'] as $category) {
                if ($category == "Favorites" || $category == "Completed") {
                    renderMarkers($category, true, true);
                } else {
                    renderMarkers(htmlspecialchars($category), $_SESSION['complete'], $_SESSION['favorite']);
                }
            }

            loadCatMarkers();
        ?>

        <script src="scripts/toggleForm.js"></script>
    </body>
</html>

<!-- NE PAS OUBLIER DE LANCER SERVEUR PYTHON python -m http.server -->