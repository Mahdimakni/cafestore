<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Veuillez vous connecter pour ajouter au panier.', 'redirect' => SITE_URL . '/pages/login.php']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
$qty = intval($_POST['qty'] ?? $_GET['qty'] ?? 1);

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Produit invalide.']);
    exit;
}

$db = getDB();

if ($action === 'add') {
    $stmt = $db->prepare("SELECT id, stock FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($prod) {
        if ($prod['stock'] < $qty) {
            echo json_encode(['status' => 'error', 'message' => 'Stock insuffisant.']);
            exit;
        }
        addToCart($id, max(1, $qty));
        echo json_encode([
            'status' => 'success', 
            'message' => 'Produit ajouté au panier !',
            'cartCount' => getCartCount(),
            'cartTotal' => getCartTotal()
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Produit introuvable.']);
    }
    exit;
}

if ($action === 'remove') {
    removeFromCart($id);
    echo json_encode([
        'status' => 'success', 
        'message' => 'Produit retiré du panier.',
        'cartCount' => getCartCount(),
        'cartTotal' => getCartTotal()
    ]);
    exit;
}

if ($action === 'update') {
    if ($qty <= 0) {
        removeFromCart($id);
    } else {
        $_SESSION['panier'][$id] = $qty;
    }
    
    // Get new subtotal for this item
    $subtotal = 0;
    if (isset($_SESSION['panier'][$id])) {
        $stmt = $db->prepare("SELECT prix FROM produits WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($p) {
            $subtotal = $p['prix'] * $_SESSION['panier'][$id];
        }
    }

    echo json_encode([
        'status' => 'success', 
        'message' => 'Panier mis à jour.',
        'cartCount' => getCartCount(),
        'cartTotal' => getCartTotal(),
        'itemSubtotal' => $subtotal
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Action inconnue.']);
