<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

$cat_id = intval($_GET['categorie'] ?? 0);
$intensite = intval($_GET['intensite'] ?? 0);
$origine = $_GET['origine'] ?? '';
$search = trim($_GET['q'] ?? '');
$sort = $_GET['tri'] ?? 'date_ajout';

$allowed_sorts = [
    'date_ajout' => 'p.date_ajout',
    'prix_asc'   => 'p.prix ASC',
    'prix_desc'  => 'p.prix DESC',
    'nom'        => 'p.nom ASC'
];

$order_by = $allowed_sorts[$sort] ?? 'p.date_ajout DESC';
if ($sort === 'date_ajout') $order_by = 'p.date_ajout DESC';

$where = [];
$params = [];
$types = '';

if ($cat_id > 0) {
    $where[] = "p.categorie_id = ?";
    $params[] = $cat_id;
    $types .= 'i';
}

if ($intensite > 0) {
    $where[] = "p.intensite = ?";
    $params[] = $intensite;
    $types .= 'i';
}

if (!empty($origine)) {
    $where[] = "p.origine = ?";
    $params[] = $origine;
    $types .= 's';
}

if (!empty($search)) {
    $where[] = "(p.nom LIKE ? OR p.description LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

$query = "
    SELECT p.*, c.nom AS categorie_nom
    FROM produits p
    LEFT JOIN categories c ON p.categorie_id = c.id
    $where_sql
    ORDER BY $order_by
";

$stmt = $db->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$produits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($produits)) {
    echo '<div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-light);">Aucun produit trouvé avec ces critères.</div>';
    exit;
}

if (!isset($_SESSION['favorites'])) $_SESSION['favorites'] = [];

foreach ($produits as $p): 
    $is_fav = in_array($p['id'], $_SESSION['favorites']);
?>
    <div class="product-card">
        <div class="product-img-wrapper" style="position:relative; overflow:hidden; height:200px;">
            <?= productImage($p['image'], $p['nom']) ?>
            <button class="btn-fav <?= $is_fav ? 'active' : '' ?>" onclick="toggleFavorite(<?= $p['id'] ?>, this)" title="Favoris">
                <?= $is_fav ? '❤️' : '🤍' ?>
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
                <input type="number" class="qty-input" id="qty-<?= $p['id'] ?>" value="1" min="1" max="<?= $p['stock'] ?>" readonly style="flex:2; border:none; text-align:center; font-family:'DM Sans'; font-weight:bold; background:var(--white); -moz-appearance: textfield;">
                <button type="button" class="qty-btn" onclick="updateQty(this, 1)" style="flex:1; border:none; background:var(--cream); cursor:pointer;">+</button>
            </div>
            <div style="display:flex; gap:0.5rem; width:100%;">
                <a href="produits.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="flex:1; text-align:center; padding: 0.5rem;">Détails</a>
                <?php if ($p['stock'] > 0): ?>
                    <button class="btn btn-primary btn-sm" style="flex:2; padding: 0.5rem;" onclick="addToCart(<?= $p['id'] ?>, document.getElementById('qty-<?= $p['id'] ?>').value)">Ajouter 🛒</button>
                <?php else: ?>
                    <button class="btn btn-outline btn-sm" style="flex:2; opacity:0.5; cursor:not-allowed; padding: 0.5rem;" disabled>Épuisé</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
