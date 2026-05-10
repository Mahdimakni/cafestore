<?php
$pageTitle = 'Gestion des Utilisateurs';
require_once __DIR__ . '/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id     = intval($_GET['id'] ?? 0);

// Suppression (impossible de se supprimer soi-même)
if ($action === 'delete' && $id > 0) {
    if ($id === intval($_SESSION['user_id'])) {
        setFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
    } else {
        $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Utilisateur supprimé.');
    }
    header('Location: utilisateurs.php');
    exit();
}

// Changer le rôle
if ($action === 'role' && $id > 0) {
    $role = $_GET['role'] ?? 'client';
    if (in_array($role, ['client', 'admin']) && $id !== intval($_SESSION['user_id'])) {
        $stmt = $db->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
        setFlash('success', 'Rôle mis à jour.');
    }
    header('Location: utilisateurs.php');
    exit();
}

$utilisateurs = $db->query("
    SELECT u.*, COUNT(c.id) AS nb_commandes
    FROM utilisateurs u
    LEFT JOIN commandes c ON c.utilisateur_id = u.id
    GROUP BY u.id
    ORDER BY u.date_inscription DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="margin-bottom:1.5rem;">
    <span style="color:var(--text-light);"><?= count($utilisateurs) ?> utilisateur(s) inscrits</span>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Commandes</th>
            <th>Rôle</th>
            <th>Inscrit le</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($utilisateurs as $u): ?>
    <tr>
        <td>#<?= $u['id'] ?></td>
        <td>
            <strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong>
            <?php if ($u['id'] == $_SESSION['user_id']): ?>
            <span style="font-size:0.7rem; background:var(--gold); color:var(--brown-dark); padding:0.1rem 0.4rem; border-radius:4px; margin-left:0.3rem;">Vous</span>
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['telephone'] ?? '—') ?></td>
        <td style="text-align:center;"><?= $u['nb_commandes'] ?></td>
        <td>
            <span class="badge <?= $u['role'] === 'admin' ? 'badge-warning' : 'badge-success' ?>">
                <?= $u['role'] === 'admin' ? '⚡ Admin' : '👤 Client' ?>
            </span>
        </td>
        <td style="font-size:0.85rem;"><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
        <td style="white-space:nowrap;">
            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <?php if ($u['role'] === 'client'): ?>
                <a href="utilisateurs.php?action=role&id=<?= $u['id'] ?>&role=admin" class="btn btn-outline btn-sm"
                   onclick="return confirm('Passer en Admin ?')">→ Admin</a>
                <?php else: ?>
                <a href="utilisateurs.php?action=role&id=<?= $u['id'] ?>&role=client" class="btn btn-outline btn-sm"
                   onclick="return confirm('Passer en Client ?')">→ Client</a>
                <?php endif; ?>
                <a href="utilisateurs.php?action=delete&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Supprimer cet utilisateur et toutes ses données ?')">🗑</a>
            <?php else: ?>
                <span style="color:var(--text-light); font-size:0.8rem;">—</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/footer.php'; ?>
