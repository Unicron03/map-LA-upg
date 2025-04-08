<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inclure les fichiers PHPMailer
require 'libs/phpmailer/PHPMailer-master/src/PHPMailer.php';
require 'libs/phpmailer/PHPMailer-master/src/SMTP.php';
require 'libs/phpmailer/PHPMailer-master/src/Exception.php';

// Connexion à la base de données
$pdo = Database::get(); // Assurez-vous que Database::get() est bien défini

if (isset($_POST['change'])) {
    // Récupérer l'email du formulaire
    $emailchange = $_POST['email'];

    // Vérifier si l'email existe dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$emailchange]);
    $user = $stmt->fetch();

    if ($user) {
        // Si l'email existe, générer un token sécurisé pour le lien de réinitialisation
        $token = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux

        // Enregistrer le token dans une session pour validation ultérieure
        $_SESSION['password_reset_email'] = $emailchange;
        $_SESSION['password_reset_token'] = $token;

        // Lien de réinitialisation pour l'environnement local
        $resetLink = "http://localhost/map-LA/scripts/management/account/changepassword.php?token=$token";

        // Créer une instance de PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuration des destinataires et de l'encodage
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Serveur SMTP Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'assistancezeldala@gmail.com'; // Votre adresse Gmail
            $mail->Password = 'buuh uigp zddk neki'; // Mot de passe spécifique à l'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Activer le chiffrement TLS
            $mail->Port = 587; // Port SMTP Gmail

            // Configuration des destinataires
            $mail->setFrom('assistancezeldala@gmail.com', "Assistance - Zelda: Link's Awakening"); // Adresse et nom de l'expéditeur
            $mail->addAddress($emailchange); // Ajouter l'adresse du destinataire

            // Définir l'encodage UTF-8
            $mail->CharSet = 'UTF-8';

            // Contenu de l'email
            $mail->isHTML(true); // Activer le format HTML
            $mail->Subject = 'Demande de réinitialisation de mot de passe';
            $mail->Body    = "<p>Vous avez demandé à réinitialiser votre mot de passe.</p>
                              <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                              <a href='$resetLink'>Réinitialiser mon mot de passe</a>";
            $mail->AltBody = "Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien suivant pour définir un nouveau mot de passe : $resetLink";

            // Envoyer l'email
            $mail->send();
            echo "<script>alert('Un e-mail de réinitialisation a été envoyé à " . htmlspecialchars($emailchange) . "');</script>";
        } catch (Exception $e) {
            echo "<script>alert('Le message n\'a pas pu être envoyé. Erreur: " . htmlspecialchars($mail->ErrorInfo) . "');</script>";
        }
    } else {
        // Si l'email n'existe pas dans la base de données
        echo "<script>alert('Cette adresse e-mail n\'est pas enregistrée dans notre base de données.');</script>";
    }
}
