<?php
$pageTitle = 'Mes Favoris';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

if (session_status() === PHP_SESSION_NONE) session_start();
$fav_ids = $_SESSION['favorites'] ?? [];

$produits = [];
if (!empty($fav_ids)) {
    $ids = implode(',', array_map('intval', $fav_ids));
    $produits = $db->query("
        SELECT p.*, c.nom AS categorie_nom
        FROM produits p
        LEFT JOIN categories c ON p.categorie_id = c.id
        WHERE p.id IN ($ids)
    ")->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="page-hero">
    <h1>❤️ Mes Favoris</h1>
    <p>Retrouvez vos cafés préférés</p>
</div>

<div class="page-content">
    <?php if (empty($produits)): ?>
        <div class="alert-box">
            <div class="icon" style="font-size: 3rem;">🤍</div>
            <h3 style="margin-top: 1rem;">Vous n'avez pas de favoris</h3>
            <p style="margin:1rem 0;">Parcourez notre boutique et ajoutez des cafés à vos favoris.</p>
            <a href="produits.php" class="btn btn-primary">Découvrir nos cafés</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($produits as $p): ?>
                <div class="product-card">
                    <div class="product-img-wrapper" style="position:relative; overflow:hidden; height:200px;">
                        <?= productImage($p['image'], $p['nom']) ?>
                        <button class="btn-fav active" onclick="toggleFavorite(<?= $p['id'] ?>, this); setTimeout(() => location.reload(), 500);" title="Retirer des favoris">
                            ❤️
                        </button>
                    </div>
                    <div class="product-body">
                        <div class="product-cat"><?= htmlspecialchars($p['categorie_nom'] ?? 'Café') ?></div>
                        <div class="product-name"><?= htmlspecialchars($p['nom']) ?></div>
                        <div class="product-desc"><?= htmlspecialchars(mb_substr($p['description'], 0, 90)) ?>...</div>
                        
                        <div class="product-meta">
                            <div class="product-price"><?= number_format($p['prix'], 2) ?> <span>TND</span></div>
                            <div class="intensity-bar" title="Intensité: <?= $p['intensite'] ?>/10">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <div class="intensity-dot <?= $i <= $p['intensite'] ? 'filled' : '' ?>"></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div style="margin-top: 1rem; display:flex; justify-content:space-between; font-size:0.8rem; color:var(--text-light);">
                            <span>🌍 <?= htmlspecialchars($p['origine']) ?></span>
                            <span><?= getStockDisplay($p['stock']) ?></span>
                        </div>
                    </div>
                    <div class="product-actions" style="display:flex; gap:0.5rem; flex-direction:column;">
                        <div class="qty-selector" style="display:flex; width:100%; border:1px solid #ddd; border-radius:50px; overflow:hidden;">
                            <button type="button" class="qty-btn" onclick="updateQty(this, -1)" style="flex:1; border:none; background:var(--cream); cursor:pointer;">-</button>
                            <input type="number" class="qty-input" id="qty-fav-<?= $p['id'] ?>" value="1" min="1" max="<?= $p['stock'] ?>" readonly style="flex:2; border:none; text-align:center; font-family:'DM Sans'; font-weight:bold; background:var(--white); -moz-appearance: textfield;">
                            <button type="button" class="qty-btn" onclick="updateQty(this, 1)" style="flex:1; border:none; background:var(--cream); cursor:pointer;">+</button>
                        </div>
                        <div style="display:flex; gap:0.5rem; width:100%;">
                            <a href="produits.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="flex:1; text-align:center; padding: 0.5rem;">Détails</a>
                            <?php if ($p['stock'] > 0): ?>
                                <button class="btn btn-primary btn-sm" style="flex:2; padding: 0.5rem;" onclick="addToCart(<?= $p['id'] ?>, document.getElementById('qty-fav-<?= $p['id'] ?>').value)">Ajouter 🛒</button>
                            <?php else: ?>
                                <button class="btn btn-outline btn-sm" style="flex:2; opacity:0.5; cursor:not-allowed; padding: 0.5rem;" disabled>Épuisé</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
