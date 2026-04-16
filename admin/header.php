<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
requireAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Admin' : 'Administration' ?> | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <div class="logo" style="margin-bottom:0.5rem;">
                <span class="logo-icon">☕</span>
                <span class="logo-text">Café<strong>Store</strong></span>
            </div>
            <div style="font-size:0.75rem; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:1px;">Panel Admin</div>
        </div>
        <nav style="margin-top:1rem;">
            <a href="<?= SITE_URL ?>/admin/index.php"      class="<?= basename($_SERVER['PHP_SELF']) === 'index.php'      ? 'active' : '' ?>">📊 Tableau de bord</a>
            <a href="<?= SITE_URL ?>/admin/produits.php"   class="<?= basename($_SERVER['PHP_SELF']) === 'produits.php'   ? 'active' : '' ?>">☕ Produits</a>
            <a href="<?= SITE_URL ?>/admin/categories.php" class="<?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">🗂 Catégories</a>
            <a href="<?= SITE_URL ?>/admin/commandes.php"  class="<?= basename($_SERVER['PHP_SELF']) === 'commandes.php'  ? 'active' : '' ?>">📦 Commandes</a>
            <a href="<?= SITE_URL ?>/admin/utilisateurs.php" class="<?= basename($_SERVER['PHP_SELF']) === 'utilisateurs.php' ? 'active' : '' ?>">👥 Utilisateurs</a>
            <div style="border-top:1px solid rgba(255,255,255,0.1); margin:1rem 0;"></div>
            <a href="<?= SITE_URL ?>/index.php">🌐 Voir le site</a>
            <a href="<?= SITE_URL ?>/pages/logout.php" style="color:#e07070 !important;">🚪 Déconnexion</a>
        </nav>
        <div style="padding:1.5rem; margin-top:auto; color:rgba(255,255,255,0.4); font-size:0.75rem; border-top:1px solid rgba(255,255,255,0.1);">
            Connecté en tant que<br><strong style="color:var(--gold);"><?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></strong>
        </div>
    </aside>

    <!-- CONTENT -->
    <div class="admin-content">
        <div class="admin-topbar">
            <h1 style="font-size:1.5rem;"><?= $pageTitle ?? 'Administration' ?></h1>
            <div style="font-size:0.85rem; color:var(--text-light);">📅 <?= date('d/m/Y H:i') ?></div>
        </div>
        <?php showFlash(); ?>
