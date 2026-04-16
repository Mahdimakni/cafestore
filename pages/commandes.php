<?php
$pageTitle = 'Mes Commandes';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDB();
$stmt = $db->prepare("
    SELECT c.*, COUNT(lc.id) AS nb_articles
    FROM commandes c
    LEFT JOIN lignes_commande lc ON lc.commande_id = c.id
    WHERE c.utilisateur_id = ?
    GROUP BY c.id
    ORDER BY c.date_commande DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$commandes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statut_labels = [
    'en_attente' => ['label' => 'En attente',  'class' => 'badge-warning'],
    'confirmee'  => ['label' => 'Confirmée',   'class' => 'badge-success'],
    'expediee'   => ['label' => 'Expédiée',    'class' => 'badge-success'],
    'livree'     => ['label' => 'Livrée ✓',    'class' => 'badge-success'],
    'annulee'    => ['label' => 'Annulée',      'class' => 'badge-danger'],
];
?>

<div class="page-hero">
    <h1>Mes Commandes</h1>
    <p><?= count($commandes) ?> commande(s) passée(s)</p>
</div>

<div class="page-content">
<?php if (empty($commandes)): ?>
    <div class="alert-box">
        <div class="icon">📦</div>
        <h3>Aucune commande</h3>
        <p style="margin:1rem 0;">Vous n'avez pas encore passé de commande.</p>
        <a href="produits.php" class="btn btn-primary">Découvrir nos cafés</a>
    </div>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Articles</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Adresse</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commandes as $c):
                $s = $statut_labels[$c['statut']] ?? ['label' => $c['statut'], 'class' => 'badge-warning'];
            ?>
            <tr>
                <td><strong>#<?= $c['id'] ?></strong></td>
                <td><?= date('d/m/Y H:i', strtotime($c['date_commande'])) ?></td>
                <td><?= $c['nb_articles'] ?> article(s)</td>
                <td><strong><?= number_format($c['total'], 2) ?> TND</strong></td>
                <td><span class="badge <?= $s['class'] ?>"><?= $s['label'] ?></span></td>
                <td style="font-size:0.85rem; max-width:200px;"><?= htmlspecialchars($c['adresse_livraison']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
