<?php
session_start();

if (!isset($_SESSION['2fa_code'])) {
    header("Location: /admin/login.php");
    exit();
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_saisi = trim($_POST['code']);
    
    // Verification expiration 10 minutes
    if (time() > $_SESSION['2fa_expire']) {
        session_destroy();
        header("Location: /admin/login.php?expire=1");
        exit();
    }
    
    // Verification du code
    if ($code_saisi === $_SESSION['2fa_code']) {
        unset($_SESSION['2fa_code']);
        unset($_SESSION['2fa_expire']);
        $_SESSION['admin'] = $_SESSION['2fa_login'];
        $_SESSION['last_activity'] = time();
        unset($_SESSION['2fa_login']);
        header("Location: /admin/admin.php");
        exit();
    } else {
        $erreur = "Code incorrect. Reessayez.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification 2FA</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header class="hero">
        <div class="hero-overlay">
            <h1>Verification</h1>
            <p>Code envoye par email</p>
        </div>
    </header>

    <section class="contact">
        <div class="formulaire-wrapper">
            <h2 style="text-align:center;margin-bottom:1rem;color:var(--couleur-primaire)">
                Code de verification
            </h2>
            <p style="text-align:center;margin-bottom:2rem;color:#888;">
                Un code a 6 chiffres a ete envoye a votre adresse email.<br>
                Il expire dans <strong>10 minutes</strong>.
            </p>

            <?php if ($erreur): ?>
                <div style="background:#e74c3c;color:white;padding:1rem;border-radius:8px;margin-bottom:1.5rem;text-align:center;">
                    ❌ <?php echo $erreur; ?>
                </div>
            <?php endif; ?>

            <form action="/admin/verification.php" method="POST">
                <div class="champ">
                    <label for="code">Code a 6 chiffres</label>
                    <input type="text" id="code" name="code" required 
                           placeholder="123456" maxlength="6"
                           style="font-size:2rem;text-align:center;letter-spacing:0.5rem;">
                </div>
                <button type="submit" class="bouton">Verifier</button>
            </form>
        </div>
    </section>

    <footer>
        <p>© 2026 MonSite — Tous droits reserves</p>
    </footer>

</body>
</html>