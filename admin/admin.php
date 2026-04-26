<?php
session_start();

if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > 1800)) {
    session_destroy();
    header("Location: /admin/login.php?expire=1");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['admin'])) {
    header("Location: /admin/login.php");
    exit();
}

require '../connexion.php';

// Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if ($_GET['action'] === 'supprimer') {
        $pdo->prepare("DELETE FROM messages WHERE id = :id")
            ->execute([':id' => $id]);
    }
    
    if ($_GET['action'] === 'lu') {
        $pdo->prepare("UPDATE messages SET lu = 1 WHERE id = :id")
            ->execute([':id' => $id]);
    }

    if ($_GET['action'] === 'nonlu') {
        $pdo->prepare("UPDATE messages SET lu = 0 WHERE id = :id")
            ->execute([':id' => $id]);
    }
    
    header("Location: /admin/admin.php");
    exit();
}

// Compteurs
$total    = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$non_lus  = $pdo->query("SELECT COUNT(*) FROM messages WHERE lu = 0")->fetchColumn();

// Statistiques par mois
$stats = $pdo->query("
    SELECT 
        CONCAT(CASE MONTH(date_envoi)
            WHEN 1 THEN 'Janvier'
            WHEN 2 THEN 'Fevrier'
            WHEN 3 THEN 'Mars'
            WHEN 4 THEN 'Avril'
            WHEN 5 THEN 'Mai'
            WHEN 6 THEN 'Juin'
            WHEN 7 THEN 'Juillet'
            WHEN 8 THEN 'Aout'
            WHEN 9 THEN 'Septembre'
            WHEN 10 THEN 'Octobre'
            WHEN 11 THEN 'Novembre'
            WHEN 12 THEN 'Decembre'
        END, ' ', YEAR(date_envoi)) as mois,
        COUNT(*) as total
    FROM messages 
    GROUP BY YEAR(date_envoi), MONTH(date_envoi)
    ORDER BY YEAR(date_envoi) DESC, MONTH(date_envoi) DESC
    LIMIT 6
")->fetchAll();

// Messages
$stmt = $pdo->query("SELECT * FROM messages ORDER BY lu ASC, date_envoi DESC");
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-wrapper {
            max-width: 1200px;
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
        .compteurs {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .compteur {
            background: white;
            border-radius: 10px;
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            flex: 1;
        }
        .compteur .chiffre {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--couleur-primaire);
        }
        .compteur .label {
            color: #888;
            font-size: 0.9rem;
        }
        .compteur.non-lus .chiffre {
            color: var(--couleur-accent);
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
        .table-messages tr.non-lu td {
            background: #fff8f0;
            font-weight: 600;
        }
        .table-messages tr.lu td {
            background: white;
            color: #888;
        }
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.4rem 0.8rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .btn-lu {
            background: #2ecc71;
            color: white;
        }
        .btn-nonlu {
            background: #e67e22;
            color: white;
        }
        .btn-supprimer {
            background: #e74c3c;
            color: white;
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
            <li><a href="../index.html">Voir le site</a></li>
            <li><a href="/admin/logout.php" style="color:var(--couleur-accent)">Deconnexion</a></li>
        </ul>
    </nav>

    <div class="admin-wrapper">
        <div class="admin-header">
            <h2>Back Office</h2>
            <span>Connecte en tant que <strong><?php echo $_SESSION['admin']; ?></strong></span>
        </div>

        <div class="compteurs">
            <div class="compteur">
                <div class="chiffre"><?php echo $total; ?></div>
                <div class="label">Messages total</div>
            </div>
            <div class="compteur non-lus">
                <div class="chiffre"><?php echo $non_lus; ?></div>
                <div class="label">Non lus</div>
            </div>
            <div class="compteur">
                <div class="chiffre"><?php echo $total - $non_lus; ?></div>
                <div class="label">Lus</div>
            </div>
        </div>

        <!-- Statistiques -->
<div class="stats-section">
    <h3>Messages par mois</h3>
    <div class="stats-barres">
        <?php 
        $max = max(array_column($stats, 'total'));
        foreach ($stats as $stat): 
            $hauteur = $max > 0 ? ($stat['total'] / $max) * 100 : 0;
        ?>
        <div class="barre-wrapper">
            <div class="barre" style="height: <?php echo $hauteur; ?>%">
                <span class="barre-valeur"><?php echo $stat['total']; ?></span>
            </div>
            <div class="barre-label"><?php echo $stat['mois']; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                    <tr class="<?php echo $msg['lu'] ? 'lu' : 'non-lu'; ?>">
                        <td><?php echo $msg['id']; ?></td>
                        <td><?php echo $msg['nom']; ?></td>
                        <td><?php echo $msg['email']; ?></td>
                        <td><?php echo $msg['sujet']; ?></td>
                        <td><?php echo nl2br($msg['message']); ?></td>
                        <td class="badge-date"><?php echo $msg['date_envoi']; ?></td>
                        <td>
                            <div class="actions">
                                <?php if (!$msg['lu']): ?>
                                    <a href="/admin/admin.php?action=lu&id=<?php echo $msg['id']; ?>" class="btn btn-lu">✅ Lu</a>
                                <?php else: ?>
                                    <a href="/admin/admin.php?action=nonlu&id=<?php echo $msg['id']; ?>" class="btn btn-nonlu">↩️ Non lu</a>
                                <?php endif; ?>
                                <a href="/admin/admin.php?action=supprimer&id=<?php echo $msg['id']; ?>" 
                                   class="btn btn-supprimer"
                                   onclick="return confirm('Supprimer ce message ?')">🗑️ Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer>
        <p>© 2026 MonSite — Tous droits reserves</p>
    </footer>

</body>
</html>