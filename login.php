<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'config.php';
    require 'connexion.php';

    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM tentatives_login 
        WHERE ip = :ip 
        AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $stmt->execute([':ip' => $ip]);
    $nb_tentatives = $stmt->fetchColumn();

    if ($nb_tentatives >= 5) {
        $erreur = "Trop de tentatives. Reessayez dans 15 minutes.";
    } else {

        $login    = htmlspecialchars(trim($_POST['login']));
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE login = :login");
        $stmt->execute([':login' => $login]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $pdo->prepare("DELETE FROM tentatives_login WHERE ip = :ip")
                ->execute([':ip' => $ip]);
            $_SESSION['admin'] = $admin['login'];
            header("Location: admin.php");
            exit();
        } else {
            $pdo->prepare("INSERT INTO tentatives_login (ip, created_at) VALUES (:ip, NOW())")
                ->execute([':ip' => $ip]);
            $erreur = "Identifiants incorrects. Tentative " . ($nb_tentatives + 1) . "/5";
        }
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
            <p>Acces reserve</p>
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

            <?php if (isset($_GET['expire'])): ?>
    <div style="background:#e67e22;color:white;padding:1rem;border-radius:8px;margin-bottom:1.5rem;text-align:center;">
        ⏱️ Session expiree. Reconnectez-vous.
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