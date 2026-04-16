<?php
// Point d'entrée admin — redirige vers login si non connecté
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/admin/index.php';
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit();
}
if (!isAdmin()) {
    setFlash('error', 'Accès réservé aux administrateurs.');
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}
header('Location: ' . SITE_URL . '/admin/index.php');
exit();
?>
