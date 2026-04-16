<?php
$pageTitle = 'Accueil';
require_once __DIR__ . '/includes/header.php';

// Récupérer les produits vedettes
$db = getDB();
$produits_vedettes = $db->query("
    SELECT p.*, c.nom AS categorie_nom
    FROM produits p
    LEFT JOIN categories c ON p.categorie_id = c.id
    ORDER BY p.date_ajout DESC
    LIMIT 4
")->fetch_all(MYSQLI_ASSOC);
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-inner">
        <span class="hero-badge">☕ Torréfaction Artisanale</span>
        <h1>Le café d'exception,<br>livré à votre porte</h1>
        <p>Découvrez notre sélection de cafés single-origin soigneusement sourcés des meilleures régions du monde.</p>
        <div class="hero-btns">
            <a href="pages/produits.php" class="btn btn-primary">Découvrir nos cafés</a>
            <?php if (!isLoggedIn()): ?>
            <a href="pages/inscription.php" class="btn btn-outline">Créer un compte</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CARACTÉRISTIQUES -->
<section class="section" style="background: var(--white);">
    <div class="section-inner">
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🌍</span>
                <h3>Origine Traçable</h3>
                <p>Chaque café est sourcé directement auprès des producteurs, garantissant qualité et équité.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🔥</span>
                <h3>Torréfaction Fraîche</h3>
                <p>Torréfié à la commande et expédié sous 48h pour un arôme incomparable.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🚚</span>
                <h3>Livraison Rapide</h3>
                <p>Livraison gratuite en Tunisie pour toute commande supérieure à 50 TND.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">♻️</span>
                <h3>Eco-Responsable</h3>
                <p>Emballages recyclables et engagement pour un commerce durable et équitable.</p>
            </div>
        </div>
    </div>
</section>

<!-- PRODUITS VEDETTES -->
<section class="section">
    <div class="section-inner">
        <div class="section-header">
            <h2>Nos Coups de Cœur</h2>
            <div class="divider"></div>
            <p>Une sélection des cafés les plus appréciés par notre communauté</p>
        </div>

        <div class="products-grid">
            <?php foreach ($produits_vedettes as $p): ?>
                <div class="product-card">
                <div style="overflow:hidden; height:200px;">
                    <?= productImage($p['image'], $p['nom']) ?>
                </div>
                <div class="product-body">
                    <div class="product-cat"><?= htmlspecialchars($p['categorie_nom'] ?? 'Café') ?></div>
                    <div class="product-name"><?= htmlspecialchars($p['nom']) ?></div>
                    <div class="product-desc"><?= htmlspecialchars(substr($p['description'], 0, 90)) ?>...</div>
                    <div class="product-meta">
                        <div class="product-price"><?= number_format($p['prix'], 2) ?> <span>TND</span></div>
                        <div class="intensity-bar" title="Intensité: <?= $p['intensite'] ?>/10">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                            <div class="intensity-dot <?= $i <= $p['intensite'] ? 'filled' : '' ?>"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <div class="product-actions">
                    <a href="pages/produits.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;">Détails</a>
                    <?php if (isLoggedIn()): ?>
                    <a href="pages/panier.php?action=add&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm" style="flex:1;">Ajouter 🛒</a>
                    <?php else: ?>
                    <a href="pages/login.php" class="btn btn-primary btn-sm" style="flex:1;">Se connecter</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center; margin-top:3rem;">
            <a href="pages/produits.php" class="btn btn-primary">Voir tous nos cafés →</a>
        </div>
    </div>
</section>

<!-- CTA -->
<?php if (!isLoggedIn()): ?>
<section class="section" style="background: linear-gradient(135deg, var(--brown-dark), var(--brown-mid)); color: var(--cream); text-align:center;">
    <div class="section-inner">
        <h2>Rejoignez notre communauté</h2>
        <p style="opacity:0.8; margin: 1rem auto 2rem; max-width:500px;">Créez votre compte gratuitement et accédez à notre boutique exclusive, suivez vos commandes et gérez votre profil.</p>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="pages/inscription.php" class="btn btn-primary">S'inscrire gratuitement</a>
            <a href="pages/login.php" class="btn btn-outline">Se connecter</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>