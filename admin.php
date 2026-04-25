<?php
session_start();

// Protection — si pas connecté, retour au login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

require 'connexion.php';

// Récupération de tous les messages
$stmt = $pdo->query("SELECT * FROM messages ORDER BY date_envoi DESC");
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-wrapper {
            max-width: 1100px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .admin-header h2 {
            color: var(--couleur-primaire);
            font-size: 1.8rem;
        }
        .table-messages {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .table-messages th {
            background: var(--couleur-primaire);
            color: white;
            padding: 1rem;
            text-align: left;
        }
        .table-messages td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .table-messages tr:hover td {
            background: #f8f9fa;
        }
        .badge-date {
            font-size: 0.8rem;
            color: #888;
        }
        .aucun-message {
            text-align: center;
            padding: 3rem;
            color: #888;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo">MonSite — Admin</div>
        <ul>
            <li><a href="index.html">Voir le site</a></li>
            <li><a href="logout.php" style="color:var(--couleur-accent)">Déconnexion</a></li>
        </ul>
    </nav>

    <div class="admin-wrapper">
        <div class="admin-header">
            <h2>Messages reçus (<?php echo count($messages); ?>)</h2>
            <span>Connecté en tant que <strong><?php echo $_SESSION['admin']; ?></strong></span>
        </div>

        <?php if (empty($messages)): ?>
            <div class="aucun-message">📭 Aucun message pour l'instant.</div>
        <?php else: ?>
            <table class="table-messages">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Sujet</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                    <tr>
                        <td><?php echo $msg['id']; ?></td>
                        <td><?php echo $msg['nom']; ?></td>
                        <td><?php echo $msg['email']; ?></td>
                        <td><?php echo $msg['sujet']; ?></td>
                        <td><?php echo nl2br($msg['message']); ?></td>
                        <td class="badge-date"><?php echo $msg['date_envoi']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer>
        <p>© 2026 MonSite — Tous droits réservés</p>
    </footer>

</body>
</html>