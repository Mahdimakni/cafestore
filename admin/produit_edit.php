<?php
$pageTitle = isset($_GET['id']) ? 'Modifier un produit' : 'Ajouter un produit';
require_once __DIR__ . '/header.php';

$db = getDB();
$id = intval($_GET['id'] ?? 0);
$produit = [
    'nom' => '',
    'categorie_id' => 0,
    'description' => '',
    'prix' => '',
    'stock' => 0,
    'origine' => '',
    'intensite' => 5,
    'image' => ''
];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $produit_db = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($produit_db) {
        $produit = $produit_db;
    } else {
        setFlash("Produit introuvable.", "error");
        header("Location: produits.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $categorie_id = intval($_POST['categorie_id']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $stock = intval($_POST['stock']);
    $origine = trim($_POST['origine']);
    $intensite = intval($_POST['intensite']);
    $image = trim($_POST['image']);

    if (empty($nom) || empty($prix)) {
        setFlash("Le nom et le prix sont obligatoires.", "error");
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE produits SET nom=?, categorie_id=?, description=?, prix=?, stock=?, origine=?, intensite=?, image=? WHERE id=?");
            $params = [$nom, $categorie_id, $description, $prix, $stock, $origine, $intensite, $image, $id];
            $msg = "Produit modifié avec succès.";
        } else {
            $stmt = $db->prepare("INSERT INTO produits (nom, categorie_id, description, prix, stock, origine, intensite, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $params = [$nom, $categorie_id, $description, $prix, $stock, $origine, $intensite, $image];
            $msg = "Produit ajouté avec succès.";
        }

        if ($stmt->execute($params)) {
            setFlash($msg, "success");
            header("Location: produits.php");
            exit;
        } else {
            setFlash("Erreur lors de l'enregistrement.", "error");
        }
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="background:var(--white); border-radius:var(--radius); padding:2rem; box-shadow:0 2px 10px var(--shadow); max-width:800px; margin:0 auto;">
    <form method="POST" action="">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div class="form-group">
                <label>Nom du produit <span style="color:red;">*</span></label>
                <input type="text" name="nom" value="<?= htmlspecialchars($produit['nom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Catégorie</label>
                <select name="categorie_id">
                    <option value="0">-- Aucune --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $produit['categorie_id'] == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Prix (TND) <span style="color:red;">*</span></label>
                <input type="number" step="0.01" name="prix" value="<?= htmlspecialchars($produit['prix']) ?>" required>
            </div>

            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($produit['stock']) ?>" min="0">
            </div>

            <div class="form-group">
                <label>Origine</label>
                <input type="text" name="origine" value="<?= htmlspecialchars($produit['origine']) ?>" placeholder="Ex: Éthiopie, Colombie...">
            </div>

            <div class="form-group">
                <label>Intensité (1-10)</label>
                <input type="number" name="intensite" value="<?= htmlspecialchars($produit['intensite']) ?>" min="1" max="10">
            </div>
            
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Image (URL de l'image ou nom du fichier)</label>
                <input type="text" name="image" value="<?= htmlspecialchars($produit['image']) ?>" placeholder="Ex: https://example.com/image.jpg">
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Description <span style="color:red;">*</span></label>
                <textarea name="description" rows="5" required><?= htmlspecialchars($produit['description']) ?></textarea>
            </div>
        </div>

        <div style="margin-top:2rem; display:flex; gap:1rem; justify-content:flex-end;">
            <a href="produits.php" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
