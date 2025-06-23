<?php
    require_once 'scripts/drawMarkers.php';
?>

<script>
    /**
     * Fonction permettant de trouver le un marqueur par son id unique et renvoi son affichage écran
    */
    function getMarkerElementByUnicId(unicId) {
        return document.querySelector(`.leaflet-marker-pane [data-unic-id="${unicId}"]`);
    }

    /**
     * Fonction formatant les données d'un nouveau marker pour validation et envoi
    */
    function createMarker(event, x, y, form) {
        event.preventDefault();  // Empêche le rechargement de la page

        var titre = form.elements['title'].value;
        var description = form.elements['description'].value;

        // Envoi des données au serveur via AJAX
        fetch('scripts/management/bdd/addMarker.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ x: x, y: y, titre: titre, description: description, typeMarker: 16 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                map.closePopup();
                location.reload();
            } else {
                alert("Erreur lors de l'ajout du marqueur.");
            }
        })
        .catch(error => console.error('Erreur:', error));
    }

    /**
     * Fonction formatant les données d'un marker à suprimer pour validation et envoi
    */
    function deleteMarker(event, id) {
        event.preventDefault();  // Empêche le rechargement de la page

        // Envoi des données au serveur via AJAX
        fetch('scripts/management/bdd/deleteMarker.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                map.closePopup();

                const element = getMarkerElementByUnicId(id);
                if (element) {
                    element.remove();
                } else {
                    console.warn('Élément non trouvé pour suppression.');
                }
            } else {
                // alert("Erreur lors de la suppression du marqueur.");
            }
        })
        .catch(error => console.error('Erreur:', error));
    }

    /**
     * Fonction formatant les données d'un marker existant à mettre à jour pour validation et envoi 
    */
    function updateMarker(event, id, form) {
        event.preventDefault();  // Empêche le rechargement de la page

        var titre = form.elements['title'].value;
        var description = form.elements['description'].value;

        // Envoi des données au serveur via AJAX
        fetch('scripts/management/bdd/updateMarker.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, titre: titre, description: description })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                map.closePopup();
                location.reload();
            } else {
                // alert("Erreur lors de la suppression du marqueur.");
            }
        })
        .catch(error => console.error('Erreur:', error));
    }

    /**
     * Fonction formatant les données d'un marker à marquer comme favoris pour validation et envoi 
    */
    function markAsFavorite(event, id) {
        event.preventDefault();

        fetch('scripts/management/bdd/markAsFavorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id })
        })
        .then(() => {
            const marker = getMarkerElementByUnicId(id.toString());
            if (!marker) return;

            const markerImgDiv = marker.querySelector('div'); // le div contenant l'image
            if (!markerImgDiv) return;

            const badge = markerImgDiv.querySelector("img[src='./img/like.png']");
            if (badge) {
                badge.remove(); // Supprime le badge s'il existe
            } else {
                const img = document.createElement("img");
                img.src = './img/like.png';
                img.style = 'position: absolute; bottom: -6px; left: -6px; width: 16px; height: 16px;';
                markerImgDiv.appendChild(img); // Ajoute le badge
            }

            map.closePopup(); // Ferme le popup
        })
        .catch(error => console.error('Erreur:', error));
    }

    /**
     * Fonction formatant les données d'un marker à marquer comme complété pour validation et envoi 
    */
    function markAsComplete(event, id) {
        event.preventDefault();  // Empêche le rechargement de la page

        // Envoi des données au serveur via AJAX
        fetch('scripts/management/bdd/markAsComplete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id }) // Envoi des données en format x-www-form-urlencoded
        })
        .then(() => {
            const marker = getMarkerElementByUnicId(id.toString());
            if (!marker) return;

            const markerImgDiv = marker.querySelector('div'); // le div contenant l'image
            if (!markerImgDiv) return;

            const badge = markerImgDiv.querySelector("img[src='./img/mark.png']");
            if (badge) {
                badge.remove(); // Supprime le badge s'il existe
            } else {
                const img = document.createElement("img");
                img.src = './img/mark.png';
                img.style = 'position: absolute; bottom: -4px; right: -6px; width: 16px; height: 16px;';
                markerImgDiv.appendChild(img); // Ajoute le badge
            }

            map.closePopup(); // Ferme le popup
        })
        .catch(error => console.error('Erreur:', error));
    }
</script>