<?php
require 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom     = htmlspecialchars(trim($_POST['nom']));
    $email   = htmlspecialchars(trim($_POST['email']));
    $sujet   = htmlspecialchars(trim($_POST['sujet']));
    $message = htmlspecialchars(trim($_POST['message']));

    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        die("Erreur : tous les champs sont obligatoires.");
    }

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

    header("Location: contact.html?statut=succes");
    exit();

} else {
    header("Location: contact.html");
    exit();
}
?>