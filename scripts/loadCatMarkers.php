<?php

/**
 * Renvoi si une catégorie donné est affiché ou non
*/
function isCatEnable($catName) {
    return in_array($catName, $_SESSION['categories']);
}

/**
 * Renvoi les catégories sous forme JSON (pour le panel dédié)
*/
function loadCatMarkers() {
    try {
        $pdo = Database::get();

        $stmt = $pdo->query("SELECT m.id, m.subId, m.nom, m.image FROM typemarker m");
        $catMarkers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<script>";
        echo "let panelIcons = document.getElementById('panel-icons');";
        foreach ($catMarkers as $catMarker) {
            $id = $catMarker['id'];

            if (!isLoggedIn() && $id >= 16) {
                break;
            }

            $nom = addslashes($catMarker['nom']);
            $subID = $catMarker['subId'];
            $iconBase64 = 'data:image/png;base64,' . base64_encode($catMarker['image']);

            if (!in_array($nom, $_SESSION['categoriesMother'])) {
                $_SESSION['categoriesMother'][] = $catMarker['nom'];
            }

            $catStatus = isset($_SESSION['categories']) ? isCatEnable($nom) : true;
            $opacityStyle = $catStatus ? "opacity: 0.4;" : "1";
        
            echo "
                (() => {
                    let element = document.createElement('form');
                    element.className = 'panel-icons-element';
                    element.id = '$nom';
                    element.style = '$opacityStyle';
                    
                    element.method = 'post';
                    element.action = '" . $_SERVER['PHP_SELF'] . "';
                    
                    let hiddenInput = document.createElement('input');
                    
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'param';
                    hiddenInput.value = '$nom';
                    
                    let img = document.createElement('img');
                    img.src = '$iconBase64';
                    img.title = '$nom';
                    
                    let span = document.createElement('span');
                    span.textContent = '$nom';
                    
                    element.appendChild(hiddenInput);
                    element.appendChild(img);
                    element.appendChild(span);
                    
                    element.addEventListener('click', () => {
                        element.submit();
                    });
                    
                    panelIcons.appendChild(element);
                })();
            ";
        }

        if (isLoggedIn()) {
            $validCategories = array_filter(
                $_SESSION['categories'],
                fn($category) => in_array($category, ['Favorites', 'Completed'])
            );
    
            if (count($validCategories) === count($_SESSION['categories'])) {
                echo "
                    (() => {
                        let element = document.createElement('form');
                        element.className = 'panel-icons-element';
                        element.id = 'Favorites';
                        element.style = '$opacityStyle';
                        
                        element.method = 'post';
                        element.action = '" . $_SERVER['PHP_SELF'] . "';
                        
                        let hiddenInput = document.createElement('input');
                        
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'param';
                        hiddenInput.value = 'Favorites';
                        
                        let img = document.createElement('img');
                        img.src = 'img/markers/favorite.png';
                        img.title = 'Favorites';
                        
                        let span = document.createElement('span');
                        span.textContent = 'Favorites';
                        
                        element.appendChild(hiddenInput);
                        element.appendChild(img);
                        element.appendChild(span);
                        
                        element.addEventListener('click', () => {
                            element.submit();
                        });
                        
                        panelIcons.appendChild(element);
                    })();
                ";

                echo "
                    (() => {
                        let element = document.createElement('form');
                        element.className = 'panel-icons-element';
                        element.id = 'Completed';
                        element.style = '$opacityStyle';
                        
                        element.method = 'post';
                        element.action = '" . $_SERVER['PHP_SELF'] . "';
                        
                        let hiddenInput = document.createElement('input');
                        
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'param';
                        hiddenInput.value = 'Completed';
                        
                        let img = document.createElement('img');
                        img.src = 'img/markers/complete.png';
                        img.title = 'Completed';
                        
                        let span = document.createElement('span');
                        span.textContent = 'Completed';
                        
                        element.appendChild(hiddenInput);
                        element.appendChild(img);
                        element.appendChild(span);
                        
                        element.addEventListener('click', () => {
                            element.submit();
                        });
                        
                        panelIcons.appendChild(element);
                    })();
                ";
            }
        }

        echo "</script>";
    } catch (PDOException $e) {
        echo "<script>console.error('Erreur : " . $e->getMessage() . "');</script>";
    }
}
?>

<!-- $_SESSION['class'] = $_SESSION['class'] == 'panel-icons-element' ? 'panel-icons-element-active' : 'panel-icons-element'; -->
