<?php
$pageTitle = 'Mon Profil';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDB();
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = trim($_POST['nom'] ?? '');
    $prenom    = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse   = trim($_POST['adresse'] ?? '');
    $new_pass  = $_POST['new_password'] ?? '';
    $new_pass2 = $_POST['new_password2'] ?? '';

    if (empty($nom) || empty($prenom)) {
        $error = 'Nom et prénom sont obligatoires.';
    } elseif ($new_pass && strlen($new_pass) < 6) {
        $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    } elseif ($new_pass && $new_pass !== $new_pass2) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        if ($new_pass) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE utilisateurs SET nom=?, prenom=?, telephone=?, adresse=?, mot_de_passe=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $telephone, $adresse, $hash, $_SESSION['user_id']]);
        } else {
            $stmt = $db->prepare("UPDATE utilisateurs SET nom=?, prenom=?, telephone=?, adresse=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $telephone, $adresse, $_SESSION['user_id']]);
        }
        if ($stmt->execute()) {
            $_SESSION['user_nom']    = $nom;
            $_SESSION['user_prenom'] = $prenom;
            setFlash('success', 'Profil mis à jour avec succès !');
            header('Location: profil.php');
            exit();
        } else {
            $error = 'Erreur lors de la mise à jour.';
        }
    }
}
?>

<div class="page-hero">
    <h1>Mon Profil</h1>
    <p>Gérez vos informations personnelles</p>
</div>

<div class="page-content" style="max-width:700px; margin:0 auto;">
    <?php if ($error): ?>
    <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div style="background:var(--white); padding:2.5rem; border-radius:var(--radius); box-shadow:0 4px 15px var(--shadow);">
        <div style="display:flex; align-items:center; gap:1.5rem; margin-bottom:2rem; padding-bottom:2rem; border-bottom:1px solid var(--cream);">
            <div style="width:80px; height:80px; background:var(--brown-mid); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; color:var(--cream);">
                <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
            </div>
            <div>
                <h2 style="margin-bottom:0.2rem;"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
                <p style="color:var(--text-light);"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge badge-success"><?= $user['role'] === 'admin' ? '⚡ Administrateur' : '👤 Client' ?></span>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Email (non modifiable)</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.7;">
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="tel" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" placeholder="+216 XX XXX XXX">
            </div>
            <div class="form-group">
                <label>Adresse de livraison</label>
                <textarea name="adresse" rows="3"><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea>
            </div>

            <hr style="margin:2rem 0; border-color:var(--cream);">
            <h3 style="margin-bottom:1rem; font-size:1.1rem;">Changer de mot de passe <small style="color:var(--text-light); font-weight:400;">(laisser vide pour ne pas changer)</small></h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="new_password" placeholder="Min. 6 caractères">
                </div>
                <div class="form-group">
                    <label>Confirmer</label>
                    <input type="password" name="new_password2" placeholder="Répéter">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer les modifications</button>
        </form>
    </div>

    <div style="text-align:center; margin-top:1.5rem;">
        <a href="../pages/logout.php" class="btn btn-outline" style="color:var(--error); border-color:var(--error);">Se déconnecter</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
