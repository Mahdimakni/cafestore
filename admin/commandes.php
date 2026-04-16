<?php
$pageTitle = 'Gestion des Commandes';
require_once __DIR__ . '/header.php';

$db = getDB();

// Changer le statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['statut'], $_POST['commande_id'])) {
    $statuts_valides = ['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'];
    $nouveau_statut  = $_POST['statut'];
    $commande_id     = intval($_POST['commande_id']);
    if (in_array($nouveau_statut, $statuts_valides)) {
        $stmt = $db->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
        $stmt->bind_param("si", $nouveau_statut, $commande_id);
        $stmt->execute();
        setFlash('success', "Statut de la commande #$commande_id mis à jour.");
    }
    header('Location: commandes.php');
    exit();
}

// Détail d'une commande
$detail_id = intval($_GET['id'] ?? 0);
if ($detail_id > 0) {
    $stmt = $db->prepare("
        SELECT c.*, CONCAT(u.prenom,' ',u.nom) AS client, u.email, u.telephone
        FROM commandes c JOIN utilisateurs u ON u.id = c.utilisateur_id
        WHERE c.id = ?
    ");
    $stmt->bind_param("i", $detail_id);
    $stmt->execute();
    $commande = $stmt->get_result()->fetch_assoc();

    $stmt2 = $db->prepare("
        SELECT lc.*, p.nom AS produit_nom FROM lignes_commande lc
        JOIN produits p ON p.id = lc.produit_id
        WHERE lc.commande_id = ?
    ");
    $stmt2->bind_param("i", $detail_id);
    $stmt2->execute();
    $lignes = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
}

$statut_labels = [
    'en_attente' => 'badge-warning',
    'confirmee'  => 'badge-success',
    'expediee'   => 'badge-success',
    'livree'     => 'badge-success',
    'annulee'    => 'badge-danger',
];
?>

<?php if ($detail_id > 0 && $commande): ?>
<!-- DÉTAIL COMMANDE -->
<a href="commandes.php" style="color:var(--gold); display:inline-block; margin-bottom:1.5rem;">← Retour aux commandes</a>
<div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; margin-bottom:2rem;">
    <div style="background:var(--white); padding:1.5rem; border-radius:var(--radius); box-shadow:0 2px 10px var(--shadow);">
        <h3 style="margin-bottom:1rem;">📋 Commande #<?= $commande['id'] ?></h3>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></p>
        <p><strong>Statut :</strong> <span class="badge <?= $statut_labels[$commande['statut']] ?>"><?= $commande['statut'] ?></span></p>
        <p><strong>Total :</strong> <?= number_format($commande['total'], 2) ?> TND</p>
        <p><strong>Adresse :</strong> <?= htmlspecialchars($commande['adresse_livraison']) ?></p>
    </div>
    <div style="background:var(--white); padding:1.5rem; border-radius:var(--radius); box-shadow:0 2px 10px var(--shadow);">
        <h3 style="margin-bottom:1rem;">👤 Client</h3>
        <p><strong>Nom :</strong> <?= htmlspecialchars($commande['client']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($commande['email']) ?></p>
        <p><strong>Tél :</strong> <?= htmlspecialchars($commande['telephone'] ?? '—') ?></p>
        <!-- Changer statut -->
        <form method="POST" action="commandes.php" style="margin-top:1rem; display:flex; gap:0.5rem; align-items:center;">
            <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
            <select name="statut" style="padding:0.5rem; border:2px solid #E8DDD0; border-radius:8px; font-family:'DM Sans',sans-serif;">
                <?php foreach (array_keys($statut_labels) as $s): ?>
                <option value="<?= $s ?>" <?= $s === $commande['statut'] ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Mettre à jour</button>
        </form>
    </div>
</div>

<h3 style="margin-bottom:1rem;">🛒 Articles commandés</h3>
<table class="data-table">
    <thead><tr><th>Produit</th><th>Prix unitaire</th><th>Quantité</th><th>Sous-total</th></tr></thead>
    <tbody>
    <?php foreach ($lignes as $l): ?>
    <tr>
        <td><?= htmlspecialchars($l['produit_nom']) ?></td>
        <td><?= number_format($l['prix_unitaire'], 2) ?> TND</td>
        <td><?= $l['quantite'] ?></td>
        <td><strong><?= number_format($l['prix_unitaire'] * $l['quantite'], 2) ?> TND</strong></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background:var(--cream);">
        <td colspan="3" style="text-align:right; font-weight:700;">TOTAL</td>
        <td style="font-weight:900; font-family:'Playfair Display',serif;"><?= number_format($commande['total'], 2) ?> TND</td>
    </tr>
    </tbody>
</table>

<?php else: ?>
<!-- LISTE COMMANDES -->
<?php
$filtre_statut = $_GET['statut'] ?? '';
$where = $filtre_statut ? "WHERE c.statut = '$filtre_statut'" : "";
$commandes = $db->query("
    SELECT c.*, CONCAT(u.prenom,' ',u.nom) AS client
    FROM commandes c JOIN utilisateurs u ON u.id = c.utilisateur_id
    $where
    ORDER BY c.date_commande DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <a href="commandes.php" class="btn btn-sm <?= !$filtre_statut ? 'btn-primary' : 'btn-outline' ?>">Toutes</a>
    <?php foreach (['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'] as $s): ?>
    <a href="commandes.php?statut=<?= $s ?>" class="btn btn-sm <?= $filtre_statut === $s ? 'btn-primary' : 'btn-outline' ?>"><?= ucfirst($s) ?></a>
    <?php endforeach; ?>
</div>

<table class="data-table">
    <thead>
        <tr><th>#</th><th>Client</th><th>Date</th><th>Total</th><th>Statut</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($commandes as $c): ?>
    <tr>
        <td><strong>#<?= $c['id'] ?></strong></td>
        <td><?= htmlspecialchars($c['client']) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($c['date_commande'])) ?></td>
        <td><?= number_format($c['total'], 2) ?> TND</td>
        <td><span class="badge <?= $statut_labels[$c['statut']] ?>"><?= $c['statut'] ?></span></td>
        <td>
            <a href="commandes.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">👁 Détail</a>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($commandes)): ?>
    <tr><td colspan="6" style="text-align:center; color:var(--text-light);">Aucune commande.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
