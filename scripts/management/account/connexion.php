<?php
// Récupération de la connexion à la bdd
$pdo = Database::get();

//-------------------------Connexion------------------------------------------------------------------------------------
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    //---------------------Récupération des données de l'adresse email entrée par l'utilisateur--------------------------
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    //--------------------Vérification que le mot de passe entré par l'utilisateur est correct---------------------------
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    } else {
        // Cas d'erreur
        echo "<script>alert('Connection failed. Please check your login details.');</script>";
    }
}