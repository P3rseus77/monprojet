<?php
session_start();

// Si déjà connecté, rediriger vers le back office
if (isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'config.php';
    require 'connexion.php';

    $login    = htmlspecialchars(trim($_POST['login']));
    $password = $_POST['password'];

    // Recherche de l'admin en BDD
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE login = :login");
    $stmt->execute([':login' => $login]);
    $admin = $stmt->fetch();

    // Vérification du mot de passe hashé
    if ($admin && hash('sha256', $password) === $admin['password']) {
        $_SESSION['admin'] = $admin['login'];
        header("Location: admin.php");
        exit();
    } else {
        $erreur = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="hero">
        <div class="hero-overlay">
            <h1>Administration</h1>
            <p>Accès réservé</p>
        </div>
    </header>

    <section class="contact">
        <div class="formulaire-wrapper">
            <h2 style="text-align:center;margin-bottom:2rem;color:var(--couleur-primaire)">Connexion</h2>

            <?php if ($erreur): ?>
                <div style="background:#e74c3c;color:white;padding:1rem;border-radius:8px;margin-bottom:1.5rem;text-align:center;">
                    ❌ <?php echo $erreur; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="champ">
                    <label for="login">Identifiant</label>
                    <input type="text" id="login" name="login" required placeholder="admin">
                </div>
                <div class="champ">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="bouton">Se connecter</button>
            </form>
        </div>
    </section>

    <footer>
        <p>© 2026 MonSite — Tous droits réservés</p>
    </footer>

</body>
</html>