<?php
session_start();

// Vérifier si le token est présent et valide
if (!isset($_GET['token']) || !isset($_SESSION['password_reset_token']) || $_GET['token'] !== $_SESSION['password_reset_token']) {
    die('Invalid or expired reset link.');
}

// Récupérer l'adresse e-mail enregistrée dans la session
$email = $_SESSION['password_reset_email'] ?? null;
if (!$email) {
    die('Error: Unable to retrieve email address.');
}

// Traitement du formulaire de changement de mot de passe
if (isset($_POST['reset_password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        echo '<script>alert("The passwords do not match.");</script>';
    } else {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=map-LA", $_SESSION["pdoUserName"], $_SESSION["pdoUserPassword"], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Crypter le nouveau mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Mettre à jour le mot de passe de l'utilisateur dans la base de données
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashedPassword, $email]);

            // Supprimer les variables de session après la mise à jour
            unset($_SESSION['password_reset_email']);
            unset($_SESSION['password_reset_token']);

            echo '<script>alert("Your password has been successfully updated.");</script>';
            header("Location: http://localhost/map-LA/");
            exit; 
        } catch (PDOException $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password</title>
    <link rel="icon" href="../../img/icon.png" type="image/png">
</head>
<style>
    body {
        background-color: #17202f;
        color: white;
        font: 16px / 1.5 "Helvetica Neue", Arial, Helvetica, sans-serif;
        font-weight: bold;
        text-align: center;
        display: flex;
        place-items: center;
        height: 100vh;
        margin: 0;
        flex-direction: column;
        justify-content: center;
        gap: 25px;
    }

    form {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 25px;
    }
    input {
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
        max-width: 300px;
    }
    button {
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    button:hover { background-color: #2980b9; }
</style>
<body>
    <h2>Reset password</h2>
    <form method="post">
        <label for="new_password">New password :</label>
        <input type="password" name="new_password" id="new_password" placeholder="Password" required>
        <label for="confirm_password">Confirm password :</label>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Password" required>
        <button type="submit" name="reset_password">Change Password</button>
    </form>
</body>
</html>
