<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Expiration session après inactivité
$timeout = 900; // 900 sec = 15 minutes
if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > $timeout) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

//CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

//Connexion à la base
$pdo = Database::get();

//Limitation des tentatives de connexion par IP
$ip = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

//Nettoyage des tentatives vieilles de +15 min
$_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function ($time) {
    return $time > time() - 900;
});

//Si trop de tentatives
if (count($_SESSION['login_attempts']) >= 5) {
    die("Trop de tentatives. Veuillez réessayer dans quelques minutes.");
}

//Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF token invalide.");
    }

    //Validation email
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $password = $_POST['password'] ?? '';

    if (!$email) {
        echo "<script>alert('Email invalide.');window.location.href = 'index.php';</script>";
        exit;
    }

    if (strlen($password) < 8) {
        echo "<script>alert('Mot de passe trop court.');window.location.href = 'index.php';</script>";
        exit;
    }

    //Recherche de l'utilisateur
    $stmt = $pdo->prepare("SELECT id, password, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Connexion réussie

        //Sécurité : regénérer ID de session
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');

        echo "<script>window.location.href = 'index.php';</script>";
        exit;
    } else {
        //Connexion échouée : enregistrer tentative
        $_SESSION['login_attempts'][] = time();
        echo "<script>alert('Échec de la connexion. Vérifiez vos identifiants.');window.location.href = 'index.php';</script>";
        exit;
    }
}
?>
