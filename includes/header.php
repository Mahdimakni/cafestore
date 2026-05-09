<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="<?= SITE_URL ?>/index.php" class="logo">
            <span class="logo-icon">☕</span>
            <span class="logo-text">Café<strong>Store</strong></span>
        </a>

        <nav class="main-nav">
            <a href="<?= SITE_URL ?>/index.php">Accueil</a>
            <a href="<?= SITE_URL ?>/pages/produits.php">Nos Cafés</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/pages/commandes.php">Mes Commandes</a>
                <a href="<?= SITE_URL ?>/pages/profil.php">Mon Profil</a>
                <?php if (isAdmin()): ?>
                    <a href="<?= SITE_URL ?>/admin/index.php" class="nav-admin">Administration</a>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>/pages/logout.php" class="nav-logout">Déconnexion</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/pages/login.php">Connexion</a>
                <a href="<?= SITE_URL ?>/pages/inscription.php" class="nav-cta">S'inscrire</a>
            <?php endif; ?>
        </nav>

        <div style="display:flex; align-items:center; gap: 1rem;">
            <?php 
                if (session_status() === PHP_SESSION_NONE) session_start();
                $fav_count = count($_SESSION['favorites'] ?? []);
            ?>
            <a href="<?= SITE_URL ?>/pages/favoris.php" style="cursor:pointer; text-decoration:none;" title="Favoris">
                ❤️ <span class="cart-count" id="fav-count" style="background:var(--error); color:var(--white);"><?= $fav_count ?></span>
            </a>
            
            <?php if (isLoggedIn()): ?>
            <a href="<?= SITE_URL ?>/pages/panier.php" class="cart-btn">
                🛒 <span class="cart-count"><?= getCartCount() ?></span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="site-main">
<?php showFlash(); ?>
