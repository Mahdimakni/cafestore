<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Produit invalide.']);
    exit;
}

if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

$is_favorite = false;
if (in_array($id, $_SESSION['favorites'])) {
    // Remove
    $_SESSION['favorites'] = array_diff($_SESSION['favorites'], [$id]);
    $message = "Retiré des favoris.";
} else {
    // Add
    $_SESSION['favorites'][] = $id;
    $is_favorite = true;
    $message = "Ajouté aux favoris !";
}

echo json_encode([
    'status' => 'success',
    'message' => $message,
    'is_favorite' => $is_favorite,
    'favoritesCount' => count($_SESSION['favorites'])
]);
