<?php

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

            $nom = addslashes($catMarker['nom']);
            $subID = $catMarker['subId'];
            $iconBase64 = 'data:image/png;base64,' . base64_encode($catMarker['image']);

            if (!isLoggedIn() && $id == $subID) {
                break;
            }

            $displayStyle = $subID && $subID != $id ? "none" : "flex";
        
            echo "
                (() => {
                    let element = document.createElement('button');
                    element.className = 'panel-icons-element';
                    element.id = '$subID';
                    element.dataset.catId = '$id';
                    element.style.display = '$displayStyle';
                    
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
                    
                    element.onclick = () => activeMarkerById($id, element);
                    
                    panelIcons.appendChild(element);
                })();
            ";

            if (!$subID && $id != $subID) {
                echo "
                    (() => {
                        let element = document.createElement('button');
                        element.style.height = '96px';
                        element.style.background = 'none';
                        element.style.border = 'none';
                        element.style.display = 'block';
    
                        let expandImg = document.createElement('img');
                        expandImg.src = './img/icon-chevron2.png';
                        expandImg.style.height = '36px';
                        expandImg.style.rotate = '0deg';
                        expandImg.onclick = () => activeSubCategory($id, expandImg);
    
                        element.appendChild(expandImg);
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