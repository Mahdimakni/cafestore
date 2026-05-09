<?php
$pageTitle = 'Nos Cafés';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

// Filtres
$cat_id    = intval($_GET['categorie'] ?? 0);
$intensite = intval($_GET['intensite'] ?? 0);
$origine   = $_GET['origine'] ?? '';
$search    = trim($_GET['q'] ?? '');
$sort      = $_GET['tri'] ?? 'date_ajout';

/* =========================
   TRI SECURISÉ
========================= */
$allowed_sorts = [
    'date_ajout' => 'p.date_ajout',
    'prix'       => 'p.prix',
    'nom'        => 'p.nom',
    'intensite'  => 'p.intensite'
];

if (!array_key_exists($sort, $allowed_sorts)) {
    $sort = 'date_ajout';
}

$order_by = $allowed_sorts[$sort];

/* =========================
   PRODUIT UNIQUE
========================= */
$produit_id = intval($_GET['id'] ?? 0);

if ($produit_id > 0) {
    $stmt = $db->prepare("
        SELECT p.*, c.nom AS categorie_nom
        FROM produits p
        LEFT JOIN categories c ON p.categorie_id = c.id
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $produit_id);
    $stmt->execute();
    $produit = $stmt->get_result()->fetch_assoc();
} else {
    $produit = null;
}

/* =========================
   CATEGORIES
========================= */
$categories = $db->query("SELECT * FROM categories ORDER BY nom")
                 ->fetch_all(MYSQLI_ASSOC);

/* =========================
   PRODUITS LISTE
========================= */
$where  = [];
$params = [];
$types  = '';

if ($cat_id > 0) {
    $where[]  = "p.categorie_id = ?";
    $params[] = $cat_id;
    $types   .= 'i';
}

if (!empty($search)) {
    $where[] = "(p.nom LIKE ? OR p.description LIKE ? OR p.origine LIKE ?)";
    $like = "%$search%";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;

    $types .= 'sss';
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

$query = "
    SELECT p.*, c.nom AS categorie_nom
    FROM produits p
    LEFT JOIN categories c ON p.categorie_id = c.id
    $where_sql
    ORDER BY $order_by ASC
";

$stmt = $db->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$produits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!-- =========================
     HERO
========================= -->
<div class="page-hero">
    <h1>Nos Cafés</h1>
    <p>Sélection de <?= count($produits) ?> cafés d'exception</p>
</div>

<?php if ($produit): ?>

<!-- =========================
     DÉTAIL PRODUIT
========================= -->
<div class="page-content">

    <a href="produits.php" style="color:var(--gold); display:inline-flex; gap:0.5rem; margin-bottom:2rem;">
        ← Retour à la liste
    </a>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:3rem;">

        <!-- IMAGE -->
        <div style="overflow:hidden; border-radius:var(--radius); height:350px; box-shadow:0 4px 15px var(--shadow);">
            <?= productImage($produit['image'], $produit['nom'], 'product-img') ?>
            <style>
                .product-img { height:350px !important; }
            </style>
        </div>

        <!-- INFOS -->
        <div>

            <div class="product-cat">
                <?= htmlspecialchars($produit['categorie_nom'] ?? '') ?>
            </div>

            <h1><?= htmlspecialchars($produit['nom']) ?></h1>

            <div style="font-size:2rem; font-weight:900; margin:1rem 0;">
                <?= number_format($produit['prix'], 2) ?> TND
            </div>

            <p style="line-height:1.8; color:var(--text-mid);">
                <?= htmlspecialchars($produit['description']) ?>
            </p>

            <!-- INFOS -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin:2rem 0;">

                <div style="background:var(--cream); padding:1rem; border-radius:8px;">
                    🌍 <?= htmlspecialchars($produit['origine']) ?>
                </div>

                <div style="background:var(--cream); padding:1rem; border-radius:8px;">
                    <?= getStockDisplay($produit['stock']) ?>
                </div>

                <div style="background:var(--cream); padding:1rem; border-radius:8px;">
                    <div class="intensity-bar">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <div class="intensity-dot <?= $i <= $produit['intensite'] ? 'filled' : '' ?>"></div>
                        <?php endfor; ?>
                        <span><?= $produit['intensite'] ?>/10</span>
                    </div>
                </div>

            </div>

            <!-- ACTION -->
            <div style="display:flex; gap:1rem; align-items:center;">
                <div class="qty-selector" style="display:flex; border:1px solid #ddd; border-radius:50px; overflow:hidden; width:120px;">
                    <button type="button" class="qty-btn" onclick="updateQty(this, -1)" style="flex:1; border:none; background:var(--cream); cursor:pointer;">-</button>
                    <input type="number" class="qty-input" id="qty-detail-<?= $produit['id'] ?>" value="1" min="1" max="<?= $produit['stock'] ?>" readonly style="flex:2; border:none; text-align:center; font-family:'DM Sans'; font-weight:bold; background:var(--white); -moz-appearance: textfield;">
                    <button type="button" class="qty-btn" onclick="updateQty(this, 1)" style="flex:1; border:none; background:var(--cream); cursor:pointer;">+</button>
                </div>

                <?php if ($produit['stock'] > 0): ?>
                    <button class="btn btn-primary" onclick="addToCart(<?= $produit['id'] ?>, document.getElementById('qty-detail-<?= $produit['id'] ?>').value)" style="flex:1;">
                        Ajouter au panier 🛒
                    </button>
                <?php else: ?>
                    <button class="btn btn-outline" style="flex:1; opacity:0.5; cursor:not-allowed;" disabled>Épuisé</button>
                <?php endif; ?>
                
                <?php 
                if (session_status() === PHP_SESSION_NONE) session_start();
                $is_fav = in_array($produit['id'], $_SESSION['favorites'] ?? []);
                ?>
                <button class="btn-fav <?= $is_fav ? 'active' : '' ?>" style="position:static; margin-left:0.5rem;" onclick="toggleFavorite(<?= $produit['id'] ?>, this)" title="Favoris">
                    <?= $is_fav ? '❤️' : '🤍' ?>
                </button>
            </div>

        </div>
    </div>
</div>

<?php else: ?>

<!-- =========================
     LISTE PRODUITS AVEC FILTRES
========================= -->
<div class="page-content page-layout" style="display:flex; gap:2rem;">

    <!-- SIDEBAR FILTRES -->
    <aside class="filter-sidebar">
        <form id="filter-form" onsubmit="event.preventDefault(); applyFilters();">
            <div class="filter-group">
                <label>Recherche</label>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Ex: Espresso...">
            </div>

            <div class="filter-group">
                <label>Catégorie</label>
                <select name="categorie">
                    <option value="0">Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat_id == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Origine</label>
                <select name="origine">
                    <option value="">Toutes les origines</option>
                    <?php 
                    $origines = $db->query("SELECT DISTINCT origine FROM produits WHERE origine IS NOT NULL AND origine != '' ORDER BY origine")->fetch_all(MYSQLI_ASSOC);
                    foreach ($origines as $org): ?>
                        <option value="<?= htmlspecialchars($org['origine']) ?>" <?= $origine == $org['origine'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($org['origine']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Intensité (max: 10)</label>
                <input type="range" name="intensite" min="0" max="10" value="<?= $intensite ?>" oninput="this.nextElementSibling.value = this.value == 0 ? 'Toutes' : this.value">
                <output style="display:block; text-align:center; font-weight:bold; margin-top:0.5rem; color:var(--brown-dark);"><?= $intensite == 0 ? 'Toutes' : $intensite ?></output>
            </div>

            <div class="filter-group">
                <label>Trier par</label>
                <select name="tri">
                    <option value="date_ajout" <?= $sort == 'date_ajout' ? 'selected' : '' ?>>Nouveautés</option>
                    <option value="prix_asc" <?= $sort == 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                    <option value="prix_desc" <?= $sort == 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                    <option value="nom" <?= $sort == 'nom' ? 'selected' : '' ?>>Nom (A-Z)</option>
                </select>
            </div>
        </form>
    </aside>

    <!-- GRILLE PRODUITS -->
    <div style="flex:1;">
        <div class="products-grid" id="dynamic-products-grid" style="transition: opacity 0.3s ease;">
            <?php 
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (!isset($_SESSION['favorites'])) $_SESSION['favorites'] = [];
            
            if (empty($produits)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-light);">Aucun produit trouvé avec ces critères.</div>
            <?php else: ?>
                <?php foreach ($produits as $p): 
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
                            <input type="number" class="qty-input" id="qty-list-<?= $p['id'] ?>" value="1" min="1" max="<?= $p['stock'] ?>" readonly style="flex:2; border:none; text-align:center; font-family:'DM Sans'; font-weight:bold; background:var(--white); -moz-appearance: textfield;">
                            <button type="button" class="qty-btn" onclick="updateQty(this, 1)" style="flex:1; border:none; background:var(--cream); cursor:pointer;">+</button>
                        </div>
                        <div style="display:flex; gap:0.5rem; width:100%;">
                            <a href="produits.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="flex:1; text-align:center; padding: 0.5rem;">Détails</a>
                            <?php if ($p['stock'] > 0): ?>
                                <button class="btn btn-primary btn-sm" style="flex:2; padding: 0.5rem;" onclick="addToCart(<?= $p['id'] ?>, document.getElementById('qty-list-<?= $p['id'] ?>').value)">Ajouter 🛒</button>
                            <?php else: ?>
                                <button class="btn btn-outline btn-sm" style="flex:2; opacity:0.5; cursor:not-allowed; padding: 0.5rem;" disabled>Épuisé</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>