<?php
session_start();
require 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification du token CSRF
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur : requête invalide.");
    }

    // On invalide le token après usage — un token = un seul envoi
    unset($_SESSION['csrf_token']);

    // Vérification hCaptcha
if (empty($_POST['h-captcha-response'])) {
    die("Erreur : merci de valider le captcha.");
}

$secret   = 'ES_52940f897a9248b0bbe7018b819b843b';
$response = $_POST['h-captcha-response'];
$verify   = file_get_contents("https://api.hcaptcha.com/siteverify?secret={$secret}&response={$response}");
$result   = json_decode($verify);

if (!$result->success) {
    die("Erreur : captcha invalide.");
}

    // Récupération et nettoyage des champs
    $nom     = htmlspecialchars(trim($_POST['nom']));
    $email   = htmlspecialchars(trim($_POST['email']));
    $sujet   = htmlspecialchars(trim($_POST['sujet']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Vérification des champs vides
    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        die("Erreur : tous les champs sont obligatoires.");
    }

    // Vérification email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Erreur : adresse email invalide.");
    }

    // Enregistrement en base de données
    $sql = "INSERT INTO messages (nom, email, sujet, message, date_envoi) 
            VALUES (:nom, :email, :sujet, :message, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom'     => $nom,
        ':email'   => $email,
        ':sujet'   => $sujet,
        ':message' => $message
    ]);

    header("Location: contact.php?statut=succes");
    exit();

} else {
    header("Location: contact.php");
    exit();
}
?>