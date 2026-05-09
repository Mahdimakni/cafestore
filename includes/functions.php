<?php
// ============================================
// FONCTIONS D'AUTHENTIFICATION ET SESSION
// ============================================
 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
 
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
 
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit();
    }
}
 
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit();
    }
}
 
function loginUser($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
 
    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_nom']   = $user['nom'];
        $_SESSION['user_prenom']= $user['prenom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role']       = $user['role'];
        return true;
    }
    return false;
}
 
function logoutUser() {
    session_unset();
    session_destroy();
}
 
// ── Panier en session ──────────────────────────────────────────────────────────
 
function getCart() {
    return $_SESSION['panier'] ?? [];
}
 
function addToCart($product_id, $qty = 1) {
    if (!isset($_SESSION['panier'][$product_id])) {
        $_SESSION['panier'][$product_id] = 0;
    }
    $_SESSION['panier'][$product_id] += $qty;
}
 
function removeFromCart($product_id) {
    unset($_SESSION['panier'][$product_id]);
}
 
function clearCart() {
    $_SESSION['panier'] = [];
}
 
function getCartCount() {
    $cart = getCart();
    return array_sum($cart);
}
 
function getCartTotal() {
    $cart  = getCart();
    if (empty($cart)) return 0;
    $db    = getDB();
    $ids   = implode(',', array_map('intval', array_keys($cart)));
    $res   = $db->query("SELECT id, prix FROM produits WHERE id IN ($ids)");
    $total = 0;
    while ($p = $res->fetch_assoc()) {
        $total += $p['prix'] * $cart[$p['id']];
    }
    return $total;
}
 
// ── Image helper ───────────────────────────────────────────────────────────────
 
function productImage($image, $nom = '', $class = 'product-img') {
    if (empty($image)) {
        return "<div class='product-img-placeholder'>☕</div>";
    }
    // URL externe (Unsplash etc.)
    if (str_starts_with($image, 'http')) {
        return "<img src='" . htmlspecialchars($image) . "' alt='" . htmlspecialchars($nom) . "' class='$class' loading='lazy' onerror=\"this.style.display='none';this.nextElementSibling.style.display='flex';\"><div class='product-img-placeholder' style='display:none'>☕</div>";
    }
    // Fichier local
    return "<img src='" . SITE_URL . "/assets/images/" . htmlspecialchars($image) . "' alt='" . htmlspecialchars($nom) . "' class='$class' loading='lazy' onerror=\"this.style.display='none';this.nextElementSibling.style.display='flex';\"><div class='product-img-placeholder' style='display:none'>☕</div>";
}
 
// ── Flash messages ─────────────────────────────────────────────────────────────
 
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
 
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
 
function showFlash() {
    $flash = getFlash();
    if ($flash) {
        $cls = $flash['type'] === 'success' ? 'flash-success' : 'flash-error';
        echo "<div class='flash {$cls}'>" . htmlspecialchars($flash['message']) . "</div>";
    }
}

// ── Stock display ─────────────────────────────────────────────────────────────

function getStockDisplay($stock) {
    if (isAdmin()) {
        return '📦 ' . $stock . ' unités en stock';
    }
    
    if ($stock <= 0) {
        return '<span style="color:var(--error); font-weight:bold;">📦 Rupture de stock</span>';
    } elseif ($stock < 10) {
        return '<span style="color:var(--gold); font-weight:bold;">📦 Stock presque épuisé</span>';
    } else {
        return '<span style="color:var(--success); font-weight:bold;">📦 En stock</span>';
    }
}
?>