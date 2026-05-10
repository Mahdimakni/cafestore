<?php
$pageTitle = 'Gestion des Produits';
require_once __DIR__ . '/header.php';

$db = getDB();

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Optionnel: vérifier si des commandes contiennent ce produit avant de le supprimer, 
    // ou supprimer l'image associée. Pour faire simple, on supprime.
    $stmt = $db->prepare("DELETE FROM produits WHERE id = ?");
    if ($stmt->execute([$id])) {
        setFlash("Produit supprimé avec succès.", "success");
    } else {
        setFlash("Erreur lors de la suppression.", "error");
    }
    header("Location: produits.php");
    exit;
}

// Handle Quick Stock Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = intval($_POST['id']);
    $stock = intval($_POST['stock']);
    $stmt = $db->prepare("UPDATE produits SET stock = ? WHERE id = ?");
    if ($stmt->execute([$stock, $id])) {
        setFlash("Stock mis à jour avec succès.", "success");
    } else {
        setFlash("Erreur lors de la mise à jour.", "error");
    }
    header("Location: produits.php");
    exit;
}

// Fetch products
$produits = $db->query("
    SELECT p.*, c.nom AS categorie_nom 
    FROM produits p 
    LEFT JOIN categories c ON p.categorie_id = c.id 
    ORDER BY p.date_ajout DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display:flex; justify-content:flex-end; margin-bottom:1rem;">
    <a href="produit_edit.php" class="btn btn-primary">+ Ajouter un produit</a>
</div>

<div style="background:var(--white); border-radius:var(--radius); box-shadow:0 2px 10px var(--shadow); overflow:hidden;">
    <table class="data-table" style="width:100%; border-collapse:collapse; text-align:left;">
        <thead style="background:var(--cream); color:var(--brown-dark);">
            <tr>
                <th style="padding:1rem;">ID</th>
                <th style="padding:1rem;">Image</th>
                <th style="padding:1rem;">Nom</th>
                <th style="padding:1rem;">Catégorie</th>
                <th style="padding:1rem;">Prix</th>
                <th style="padding:1rem;">Stock</th>
                <th style="padding:1rem; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $p): ?>
            <tr style="border-bottom:1px solid var(--cream);">
                <td style="padding:1rem;"><?= $p['id'] ?></td>
                <td style="padding:1rem;">
                    <div style="width:50px; height:50px; border-radius:8px; overflow:hidden;">
                        <?= productImage($p['image'], $p['nom']) ?>
                    </div>
                </td>
                <td style="padding:1rem;"><strong><?= htmlspecialchars($p['nom']) ?></strong></td>
                <td style="padding:1rem;"><?= htmlspecialchars($p['categorie_nom'] ?? 'N/A') ?></td>
                <td style="padding:1rem;"><?= number_format($p['prix'], 2) ?> TND</td>
                <td style="padding:1rem;">
                    <form method="POST" action="produits.php" style="display:flex; gap:0.5rem; align-items:center;">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="number" name="stock" value="<?= $p['stock'] ?>" min="0" style="width:70px; padding:0.3rem; border:1px solid #ddd; border-radius:4px; font-family:'DM Sans';">
                        <button type="submit" name="update_stock" class="btn btn-outline btn-sm" style="padding:0.3rem 0.6rem;" title="Mettre à jour le stock">OK</button>
                    </form>
                </td>
                <td style="padding:1rem; text-align:right;">
                    <a href="produit_edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="margin-right:0.5rem;">✏️</a>
                    <a href="produits.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');">🗑</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>