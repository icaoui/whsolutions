<!-- Footer -->
<footer class="footer">
    <div class="footer-wave">
        <svg viewBox="0 0 1440 100" preserveAspectRatio="none"><path d="M0,50 C360,100 1080,0 1440,50 L1440,100 L0,100 Z" fill="currentColor"/></svg>
    </div>
    <div class="container">
        <div class="footer-grid" data-stagger>
            <div class="footer-col">
                <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="<?= SITE_NAME ?>" class="footer-logo">
                <p>Expert en Hygiène Professionnelle. Solutions performantes pour l'industrie agroalimentaire et environnementale au Maroc.</p>
                <div class="footer-social">
                    <a href="<?= getWhatsAppGeneralLink() ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <a href="mailto:<?= SITE_EMAIL ?>"><i class="fas fa-envelope"></i></a>
                    <a href="tel:<?= SITE_PHONE ?>"><i class="fas fa-phone"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Liens Rapides</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/">Accueil</a></li>
                    <li><a href="<?= SITE_URL ?>/products.php">Nos Produits</a></li>
                    <li><a href="<?= SITE_URL ?>/packages.php">Nos Packages</a></li>
                    <li><a href="<?= CATALOGUE_PDF ?>" target="_blank">Catalogue PDF</a></li>
                    <li><a href="<?= SITE_URL ?>/about.php">À Propos</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Nos Catégories</h4>
                <ul>
                    <?php foreach(getCategories($pdo) as $cat): ?>
                    <li><a href="<?= SITE_URL ?>/products.php?category=<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <ul class="footer-contact">
                    <li><i class="fas fa-phone"></i> <?= SITE_PHONE ?></li>
                    <li><i class="fas fa-envelope"></i> <?= SITE_EMAIL ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?= SITE_ADDRESS ?></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés. | Développé avec <i class="fas fa-heart" style="color:var(--accent);"></i></p>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<button id="backToTop" class="back-to-top" aria-label="Retour en haut"><i class="fas fa-arrow-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
<script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
</body>
</html>