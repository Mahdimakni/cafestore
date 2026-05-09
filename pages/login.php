<?php
$pageTitle = 'Connexion';
require_once __DIR__ . '/../includes/header.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (loginUser($email, $password)) {
        $redirect = $_SESSION['redirect_after_login'] ?? SITE_URL . '/index.php';
        unset($_SESSION['redirect_after_login']);
        if (isAdmin()) {
            header('Location: ' . SITE_URL . '/admin/index.php');
        } else {
            header('Location: ' . $redirect);
        }
        exit();
    } else {
        $error = 'Email ou mot de passe incorrect.';
    }
}
?>

<div class="form-container">
    <div style="text-align:center; font-size:3rem; margin-bottom:1rem;">☕</div>
    <h1 class="form-title">Connexion</h1>
    <p class="form-subtitle">Bon retour parmi nous !</p>

    <?php if ($error): ?>
    <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" placeholder="votre@email.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem;">Se connecter</button>
    </form>

    <p class="form-link">Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>


</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
