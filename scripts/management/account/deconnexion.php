<?php

// ------------------------Déconnexion-----------------------------------------------------------------------------------
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}