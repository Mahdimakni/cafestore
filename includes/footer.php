</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <span class="logo-icon">☕</span>
            <span class="logo-text">Café<strong>Store</strong></span>
            <p>L'excellence du café artisanal, livré chez vous.</p>
        </div>
        <div class="footer-links">
            <h4>Navigation</h4>
            <a href="<?= SITE_URL ?>/index.php">Accueil</a>
            <a href="<?= SITE_URL ?>/pages/produits.php">Nos Cafés</a>
            <a href="<?= SITE_URL ?>/pages/inscription.php">S'inscrire</a>
        </div>
        <div class="footer-contact">
            <h4>Contact</h4>
            <p>📍 12 Avenue Habib Bourguiba, Tunis</p>
            <p>📞 +216 71 000 000</p>
            <p>✉️ contact@cafestore.tn</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> CaféStore. Tous droits réservés.</p>
    </div>
</footer>

<?php if (!isset($_COOKIE['cookie_consent'])): ?>
<div id="cookie-banner" class="cookie-banner">
    <div class="cookie-content">
        <span class="cookie-icon">🍪</span>
        <div class="cookie-text">
            <h4>Respect de votre vie privée</h4>
            <p>Nous utilisons des cookies pour améliorer votre expérience sur notre site, analyser le trafic et personnaliser le contenu. En continuant votre navigation, vous acceptez notre politique de cookies.</p>
        </div>
    </div>
    <div class="cookie-actions">
        <button id="accept-cookies" class="btn btn-primary btn-sm">Accepter</button>
        <button id="decline-cookies" class="btn btn-outline btn-sm">Refuser</button>
    </div>
</div>
<?php endif; ?>

<div id="toast-container"></div>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>

</body>
</html>
