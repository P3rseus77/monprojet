<?php
session_start();
require 'config.php';
require 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verification du token CSRF
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur : requete invalide.");
    }
    unset($_SESSION['csrf_token']);

    // Verification hCaptcha
    if (empty($_POST['h-captcha-response'])) {
        die("Erreur : merci de valider le captcha.");
    }

    $secret   = HCAPTCHA_SECRET;
    $response = $_POST['h-captcha-response'];
    $verify   = file_get_contents("https://api.hcaptcha.com/siteverify?secret={$secret}&response={$response}");
    $result   = json_decode($verify);

    if (!$result->success) {
        die("Erreur : captcha invalide.");
    }

    // Recuperation et nettoyage des champs
    $nom     = htmlspecialchars(trim($_POST['nom']));
    $email   = htmlspecialchars(trim($_POST['email']));
    $sujet   = htmlspecialchars(trim($_POST['sujet']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Verification des champs vides
    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        die("Erreur : tous les champs sont obligatoires.");
    }

    // Verification email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Erreur : adresse email invalide.");
    }

    // Enregistrement en base de donnees
    $sql = "INSERT INTO messages (nom, email, sujet, message, date_envoi) 
            VALUES (:nom, :email, :sujet, :message, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom'     => $nom,
        ':email'   => $email,
        ':sujet'   => $sujet,
        ':message' => $message
    ]);

    // Envoi des emails
    require 'email.php';

    // Email de confirmation au visiteur
    $sujet_visiteur = "Votre message a bien ete recu";
    $corps_visiteur  = "Bonjour " . $nom . ",\n\n";
    $corps_visiteur .= "Nous avons bien recu votre message concernant : " . $sujet . "\n\n";
    $corps_visiteur .= "Nous vous repondrons dans les plus brefs delais.\n\n";
    $corps_visiteur .= "Cordialement,\n";
    $corps_visiteur .= "L'equipe MonSite";
    envoyerEmail($email, $sujet_visiteur, $corps_visiteur);

    // Email de notification a l'admin
    $sujet_admin  = "Nouveau message de contact : " . $sujet;
    $corps_admin  = "Nouveau message recu sur le site.\n\n";
    $corps_admin .= "Nom : " . $nom . "\n";
    $corps_admin .= "Email : " . $email . "\n";
    $corps_admin .= "Sujet : " . $sujet . "\n\n";
    $corps_admin .= "Message :\n" . $message;
    envoyerEmail(SMTP_USER, $sujet_admin, $corps_admin);

    header("Location: contact.php?statut=succes");
    exit();

} else {
    header("Location: contact.php");
    exit();
}
?>