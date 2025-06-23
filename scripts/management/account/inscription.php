<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

//Limitation des tentatives d'inscription (15 min)
$ip = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['register_attempts'])) {
    $_SESSION['register_attempts'] = [];
}
$_SESSION['register_attempts'] = array_filter($_SESSION['register_attempts'], function ($time) {
    return $time > time() - 900;
});
if (count($_SESSION['register_attempts']) >= 5) {
    die("Trop de tentatives. Veuillez réessayer dans quelques minutes.");
}

//Fonctions de validation
function isPasswordStrong($password) {
    return (
        strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[\W]/', $password)
    );
}

function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username);
}

function validateFullname($fullname) {
    return preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]{3,60}$/u', $fullname);
}

//Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    //Vérification du CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF token invalide.");
    }

    $pdo = Database::get();

    //Validation et nettoyage
    $username = trim($_POST['username'] ?? '');
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $fullname = trim($_POST['fullname'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    if (!$username || !validateUsername($username)) {
        die("Nom d'utilisateur invalide.");
    }
    if (!$email) {
        die("Email invalide.");
    }
    if (!$fullname || !validateFullname($fullname)) {
        die("Nom complet invalide.");
    }
    if (!isPasswordStrong($passwordInput)) {
        echo "<script>Add commentMore actions
            alert('Le mot de passe doit avoir au moins 8 caractères, avec une majuscule, un chiffre et un caractère spécial.');
            window.location.href = 'index.php';
        </script>";
        exit;
    }

    //Échapper les données pour la session
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $fullname = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');

    $password = password_hash($passwordInput, PASSWORD_BCRYPT);

    //Vérification email unique
    $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $checkEmail->execute([$email]);
    if ($checkEmail->fetchColumn()) {
        $_SESSION['register_attempts'][] = time(); // Tentative échouée
        echo "<script>alert('Email déjà utilisé.');window.location.href = 'index.php';</script>";
        exit;
    }

    //Insertion en base
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, fullname) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $password, $email, $fullname])) {
        $userId = $pdo->lastInsertId();

        // Lier les markers libres
        $markerId = $pdo->query("SELECT id FROM marker WHERE userID IS NULL");
        $markerResults = $markerId->fetchAll(PDO::FETCH_ASSOC);

        $insertUserdata = $pdo->prepare("INSERT INTO userdata (idMarker, userId, favorite, complete) VALUES (?, ?, 0, 0)");
        foreach ($markerResults as $marker) {
            $insertUserdata->execute([$marker['id'], $userId]);
        }

        //Sécurité : regénérer ID de session
        session_regenerate_id(true);

        echo "<script>alert('Inscription réussie ! Veuillez vous connecter.'); window.location.href = 'index.php';</script>";
        exit;
    } else {
        error_log("Erreur d'inscription : " . implode(", ", $stmt->errorInfo()));
        $_SESSION['register_attempts'][] = time(); // Tentative échouée
        echo "<script>alert('Erreur lors de l\'inscription.'); window.location.href = 'index.php';</script>";
        exit;
    }
}
?>
