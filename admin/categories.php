<?php
$pageTitle = 'Gestion des Catégories';
require_once __DIR__ . '/header.php';

$db     = getDB();
$action = $_GET['action'] ?? 'list';
$id     = intval($_GET['id'] ?? 0);

// Suppression
if ($action === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    setFlash('success', 'Catégorie supprimée.');
    header('Location: categories.php');
    exit();
}

// Ajout / Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom  = trim($_POST['nom'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if (empty($nom)) {
        setFlash('error', 'Le nom est obligatoire.');
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE categories SET nom=?, description=? WHERE id=?");
            $stmt->bind_param("ssi", $nom, $desc, $id);
            $msg = 'Catégorie modifiée.';
        } else {
            $stmt = $db->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $nom, $desc);
            $msg = 'Catégorie ajoutée.';
        }
        $stmt->execute();
        setFlash('success', $msg);
        header('Location: categories.php');
        exit();
    }
}

$categorie = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $categorie = $stmt->get_result()->fetch_assoc();
}
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div style="max-width:500px;">
    <a href="categories.php" style="color:var(--gold);">← Retour</a>
    <div style="background:var(--white); padding:2rem; border-radius:var(--radius); box-shadow:0 4px 15px var(--shadow); margin-top:1rem;">
        <h2 style="margin-bottom:1.5rem;"><?= $action === 'edit' ? '✏️ Modifier la catégorie' : '➕ Nouvelle catégorie' ?></h2>
        <form method="POST" action="categories.php?action=<?= $action ?>&id=<?= $id ?>">
            <div class="form-group">
                <label>Nom *</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($categorie['nom'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?= htmlspecialchars($categorie['description'] ?? '') ?></textarea>
            </div>
            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                <a href="categories.php" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<div style="display:flex; justify-content:flex-end; margin-bottom:1.5rem;">
    <a href="categories.php?action=add" class="btn btn-primary">➕ Ajouter une catégorie</a>
</div>
<?php
$categories = $db->query("
    SELECT c.*, COUNT(p.id) AS nb_produits
    FROM categories c
    LEFT JOIN produits p ON p.categorie_id = c.id
    GROUP BY c.id ORDER BY c.nom
")->fetch_all(MYSQLI_ASSOC);
?>
<table class="data-table">
    <thead>
        <tr><th>#</th><th>Nom</th><th>Description</th><th>Produits</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $cat): ?>
    <tr>
        <td>#<?= $cat['id'] ?></td>
        <td><strong><?= htmlspecialchars($cat['nom']) ?></strong></td>
        <td style="font-size:0.9rem; color:var(--text-light);"><?= htmlspecialchars($cat['description'] ?? '—') ?></td>
        <td><span class="badge badge-success"><?= $cat['nb_produits'] ?> produit(s)</span></td>
        <td style="white-space:nowrap;">
            <a href="categories.php?action=edit&id=<?= $cat['id'] ?>" class="btn btn-outline btn-sm">✏️ Modifier</a>
            <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Supprimer cette catégorie ?')">🗑</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
