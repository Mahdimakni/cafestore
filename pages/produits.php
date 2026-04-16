<?php
$pageTitle = 'Nos Cafés';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

// Filtres
$cat_id  = intval($_GET['categorie'] ?? 0);
$search  = trim($_GET['q'] ?? '');
$sort    = $_GET['tri'] ?? 'date_ajout';

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
                    📦 <?= $produit['stock'] ?> unités
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
            <?php if (isLoggedIn()): ?>
                <form method="GET" action="panier.php" style="display:flex; gap:1rem;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?= $produit['id'] ?>">

                    <input type="number" name="qty" value="1" min="1" max="<?= $produit['stock'] ?>">

                    <button type="submit" class="btn btn-primary">
                        Ajouter 🛒
                    </button>
                </form>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-full">
                    Se connecter
                </a>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php else: ?>

<!-- =========================
     LISTE PRODUITS
========================= -->
<div class="page-content">

    <!-- FILTRES -->
    <form method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:2rem;">

        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Recherche...">

        <select name="categorie">
            <option value="0">Toutes</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="tri">
            <option value="date_ajout" <?= $sort == 'date_ajout' ? 'selected' : '' ?>>Nouveautés</option>
            <option value="prix" <?= $sort == 'prix' ? 'selected' : '' ?>>Prix</option>
            <option value="nom" <?= $sort == 'nom' ? 'selected' : '' ?>>Nom</option>
            <option value="intensite" <?= $sort == 'intensite' ? 'selected' : '' ?>>Intensité</option>
        </select>

        <button type="submit" class="btn btn-primary">Filtrer</button>
    </form>

    <!-- PRODUITS -->
    <div class="products-grid">

        <?php foreach ($produits as $p): ?>
            <div class="product-card">

                <!-- IMAGE PROPRE -->
                <div style="overflow:hidden; height:200px;">
                    <?= productImage($p['image'], $p['nom']) ?>
                </div>

                <div class="product-body">

                    <div class="product-cat">
                        <?= htmlspecialchars($p['categorie_nom'] ?? 'Café') ?>
                    </div>

                    <div class="product-name">
                        <?= htmlspecialchars($p['nom']) ?>
                    </div>

                    <div class="product-desc">
                        <?= htmlspecialchars(mb_substr($p['description'], 0, 100)) ?>...
                    </div>

                    <div class="product-price">
                        <?= number_format($p['prix'], 2) ?> TND
                    </div>

                </div>

                <div class="product-actions">
                    <a href="produits.php?id=<?= $p['id'] ?>">Détails</a>
                    <a href="panier.php?action=add&id=<?= $p['id'] ?>">🛒 Ajouter</a>
                </div>

            </div>
        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>