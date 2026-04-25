<?php
session_start();

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupération du statut
$statut = $_GET['statut'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — MonSite</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body>

    <nav>
        <div class="logo">MonSite</div>
        <ul>
            <li><a href="index.html">Accueil</a></li>
            <li><a href="services.html">Services</a></li>
            <li><a href="apropos.html">À propos</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>

    <header class="hero">
        <div class="hero-overlay">
            <h1>Contact</h1>
            <p>On se parle ?</p>
        </div>
    </header>

    <section class="contact">
        <div class="formulaire-wrapper">

            <?php if ($statut === 'succes'): ?>
                <div style="background:#2ecc71;color:white;padding:1rem;border-radius:8px;margin-bottom:1.5rem;text-align:center;">
                    ✅ Message envoyé avec succès !
                </div>
            <?php elseif ($statut === 'erreur'): ?>
                <div style="background:#e74c3c;color:white;padding:1rem;border-radius:8px;margin-bottom:1.5rem;text-align:center;">
                    ❌ Erreur lors de l'envoi. Réessayez.
                </div>
            <?php endif; ?>

            <form action="traitement.php" method="POST">

                <!-- Token CSRF caché -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="champ">
                    <label for="nom">Votre nom *</label>
                    <input type="text" id="nom" name="nom" required placeholder="Jean Dupont">
                </div>

                <div class="champ">
                    <label for="email">Votre email *</label>
                    <input type="email" id="email" name="email" required placeholder="jean@exemple.fr">
                </div>

                <div class="champ">
                    <label for="sujet">Sujet *</label>
                    <input type="text" id="sujet" name="sujet" required placeholder="Demande de devis">
                </div>

                <div class="champ">
                    <label for="message">Votre message *</label>
                    <textarea id="message" name="message" required rows="6" placeholder="Décrivez votre projet..."></textarea>
                </div>

                <div class="h-captcha" data-sitekey="e342f1cc-a0c2-4096-b44c-3484084cf766"></div>
<br>
                <button type="submit" class="bouton">Envoyer le message</button>

            </form>
        </div>
    </section>

    <footer>
        <p>© 2026 MonSite — Tous droits réservés</p>
    </footer>

</body>
</html>