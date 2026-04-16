<?php
$pageTitle = 'Mon Panier';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDB();

// Actions
$action     = $_GET['action'] ?? '';
$product_id = intval($_GET['id'] ?? 0);

if ($action === 'add' && $product_id > 0) {
    $qty = intval($_GET['qty'] ?? 1);
    // Vérifier que le produit existe
    $stmt = $db->prepare("SELECT id, stock FROM produits WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    if ($prod) {
        addToCart($product_id, max(1, $qty));
        setFlash('success', 'Produit ajouté au panier !');
    }
    header('Location: panier.php');
    exit();
}

if ($action === 'remove' && $product_id > 0) {
    removeFromCart($product_id);
    setFlash('success', 'Produit retiré du panier.');
    header('Location: panier.php');
    exit();
}

if ($action === 'clear') {
    clearCart();
    setFlash('success', 'Panier vidé.');
    header('Location: panier.php');
    exit();
}

// Commander
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commander'])) {
    $cart = getCart();
    if (!empty($cart)) {
        $adresse = trim($_POST['adresse'] ?? '');
        if (empty($adresse)) {
            setFlash('error', 'Veuillez indiquer une adresse de livraison.');
            header('Location: panier.php');
            exit();
        }
        $total = getCartTotal();
        // Créer la commande
        $stmt = $db->prepare("INSERT INTO commandes (utilisateur_id, total, adresse_livraison) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $_SESSION['user_id'], $total, $adresse);
        $stmt->execute();
        $commande_id = $db->insert_id;

        // Lignes de commande
        $ids = implode(',', array_map('intval', array_keys($cart)));
        $produits_db = $db->query("SELECT id, prix, stock FROM produits WHERE id IN ($ids)")->fetch_all(MYSQLI_ASSOC);
        $produits_map = array_column($produits_db, null, 'id');

        foreach ($cart as $pid => $qty) {
            if (isset($produits_map[$pid])) {
                $prix = $produits_map[$pid]['prix'];
                $stmt2 = $db->prepare("INSERT INTO lignes_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("iiid", $commande_id, $pid, $qty, $prix);
                $stmt2->execute();
                // Mise à jour du stock
                $new_stock = max(0, $produits_map[$pid]['stock'] - $qty);
                $db->query("UPDATE produits SET stock = $new_stock WHERE id = $pid");
            }
        }
        clearCart();
        setFlash('success', "🎉 Commande #$commande_id passée avec succès ! Merci pour votre achat.");
        header('Location: commandes.php');
        exit();
    }
}

// Affichage panier
$cart = getCart();
$produits_panier = [];
if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $res = $db->query("SELECT * FROM produits WHERE id IN ($ids)");
    while ($p = $res->fetch_assoc()) {
        $p['quantite'] = $cart[$p['id']];
        $p['sous_total'] = $p['prix'] * $p['quantite'];
        $produits_panier[] = $p;
    }
}
$total = getCartTotal();
?>

<div class="page-hero">
    <h1>🛒 Mon Panier</h1>
    <p><?= getCartCount() ?> article(s)</p>
</div>

<div class="page-content">
<?php if (empty($produits_panier)): ?>
    <div class="alert-box">
        <div class="icon">🛒</div>
        <h3>Votre panier est vide</h3>
        <p style="margin:1rem 0;">Découvrez nos cafés et ajoutez vos préférés.</p>
        <a href="produits.php" class="btn btn-primary">Parcourir nos cafés</a>
    </div>
<?php else: ?>
    <div style="display:grid; grid-template-columns:1fr 350px; gap:2rem; align-items:start;">
        <div>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th>Sous-total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits_panier as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['nom']) ?></strong><br><small style="color:var(--text-light);">🌍 <?= htmlspecialchars($p['origine']) ?></small></td>
                        <td><?= number_format($p['prix'], 2) ?> TND</td>
                        <td><?= $p['quantite'] ?></td>
                        <td><strong><?= number_format($p['sous_total'], 2) ?> TND</strong></td>
                        <td><a href="panier.php?action=remove&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Retirer ce produit ?')">✕</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:1rem;">
                <a href="panier.php?action=clear" class="btn btn-outline btn-sm" onclick="return confirm('Vider tout le panier ?')">🗑 Vider le panier</a>
            </div>
        </div>

        <div class="cart-summary">
            <h3 style="margin-bottom:1.5rem; font-family:'Playfair Display',serif;">Récapitulatif</h3>
            <?php foreach ($produits_panier as $p): ?>
            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem; font-size:0.9rem;">
                <span><?= htmlspecialchars($p['nom']) ?> × <?= $p['quantite'] ?></span>
                <span><?= number_format($p['sous_total'], 2) ?> TND</span>
            </div>
            <?php endforeach; ?>
            <hr style="margin:1rem 0; border-color:var(--cream);">
            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem; font-size:0.9rem; color:var(--text-light);">
                <span>Livraison</span>
                <span><?= $total >= 50 ? 'Gratuite 🎉' : '5.00 TND' ?></span>
            </div>
            <div class="cart-total" style="display:flex; justify-content:space-between;">
                <span>Total</span>
                <span><?= number_format($total + ($total >= 50 ? 0 : 5), 2) ?> TND</span>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Adresse de livraison</label>
                    <textarea name="adresse" rows="3" placeholder="Votre adresse complète..." required><?= htmlspecialchars($_SESSION['user_addr'] ?? '') ?></textarea>
                </div>
                <button type="submit" name="commander" class="btn btn-primary btn-full">✅ Confirmer la commande</button>
            </form>
            <a href="produits.php" style="display:block; text-align:center; margin-top:1rem; font-size:0.9rem; color:var(--text-light);">← Continuer les achats</a>
        </div>
    </div>
<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
