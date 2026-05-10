<?php
$pageTitle = 'Tableau de bord';
require_once __DIR__ . '/header.php';

$db = getDB();
$nb_produits   = $db->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$nb_clients    = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role='client'")->fetchColumn();
$nb_commandes  = $db->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$ca_total      = $db->query("SELECT COALESCE(SUM(total),0) FROM commandes WHERE statut != 'annulee'")->fetchColumn();

$commandes_recentes = $db->query("
    SELECT c.*, CONCAT(u.prenom, ' ', u.nom) AS client
    FROM commandes c JOIN utilisateurs u ON u.id = c.utilisateur_id
    ORDER BY c.date_commande DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$produits_stock = $db->query("SELECT * FROM produits ORDER BY stock ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $nb_produits ?></div>
        <div class="stat-label">☕ Produits</div>
    </div>
    <div class="stat-card" style="border-color:#2D6A4F;">
        <div class="stat-number"><?= $nb_clients ?></div>
        <div class="stat-label">👥 Clients inscrits</div>
    </div>
    <div class="stat-card" style="border-color:#5C3317;">
        <div class="stat-number"><?= $nb_commandes ?></div>
        <div class="stat-label">📦 Commandes</div>
    </div>
    <div class="stat-card" style="border-color:#C8962A;">
        <div class="stat-number"><?= number_format($ca_total, 0) ?></div>
        <div class="stat-label">💰 CA Total (TND)</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem;">
    <!-- Dernières commandes -->
    <div>
        <h2 style="margin-bottom:1rem; font-size:1.2rem;">Dernières commandes</h2>
        <table class="data-table">
            <thead><tr><th>#</th><th>Client</th><th>Total</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach ($commandes_recentes as $c): ?>
            <tr>
                <td>#<?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['client']) ?></td>
                <td><?= number_format($c['total'], 2) ?> TND</td>
                <td><span class="badge <?= $c['statut'] === 'livree' ? 'badge-success' : ($c['statut'] === 'annulee' ? 'badge-danger' : 'badge-warning') ?>"><?= $c['statut'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a href="commandes.php" style="display:block; margin-top:0.5rem; font-size:0.85rem; color:var(--gold);">Voir toutes les commandes →</a>
    </div>

    <!-- Stocks faibles -->
    <div>
        <h2 style="margin-bottom:1rem; font-size:1.2rem;">⚠️ Stocks les plus faibles</h2>
        <table class="data-table">
            <thead><tr><th>Produit</th><th>Stock</th></tr></thead>
            <tbody>
            <?php foreach ($produits_stock as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nom']) ?></td>
                <td>
                    <span class="badge <?= $p['stock'] <= 5 ? 'badge-danger' : ($p['stock'] <= 15 ? 'badge-warning' : 'badge-success') ?>">
                        <?= $p['stock'] ?> unités
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a href="produits.php" style="display:block; margin-top:0.5rem; font-size:0.85rem; color:var(--gold);">Gérer les produits →</a>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
