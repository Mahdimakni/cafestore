<?php
$pageTitle = 'Inscription';
require_once __DIR__ . '/../includes/header.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = trim($_POST['nom'] ?? '');
    $prenom    = trim($_POST['prenom'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse   = trim($_POST['adresse'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $password2) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, adresse) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nom, $prenom, $email, $hash, $telephone, $adresse);
            if ($stmt->execute()) {
                setFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
                header('Location: login.php');
                exit();
            } else {
                $error = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}
?>

<div class="form-container" style="max-width:600px;">
    <div style="text-align:center; font-size:3rem; margin-bottom:1rem;">☕</div>
    <h1 class="form-title">Créer un compte</h1>
    <p class="form-subtitle">Rejoignez la communauté CaféStore</p>

    <?php if ($error): ?>
    <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom <span style="color:red">*</span></label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom <span style="color:red">*</span></label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email <span style="color:red">*</span></label>
            <input type="email" id="email" name="email" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <input type="tel" id="telephone" name="telephone" placeholder="+216 XX XXX XXX" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="adresse">Adresse de livraison</label>
            <textarea id="adresse" name="adresse" rows="2" placeholder="Votre adresse complète"><?= htmlspecialchars($_POST['adresse'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Mot de passe <span style="color:red">*</span></label>
                <input type="password" id="password" name="password" placeholder="Min. 6 caractères" required>
            </div>
            <div class="form-group">
                <label for="password2">Confirmer <span style="color:red">*</span></label>
                <input type="password" id="password2" name="password2" placeholder="Répéter" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem;">Créer mon compte</button>
    </form>

    <p class="form-link">Déjà un compte ? <a href="login.php">Se connecter</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
