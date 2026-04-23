<?php
$pageTitle = 'WH Solutions - Expert en Hygiène Professionnelle';
require_once 'config/config.php';
require_once 'includes/functions.php';
logVisitor($pdo, 'home');
$featured = getFeaturedProducts($pdo, 8);
$categories = getCategories($pdo);
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="animate-on-scroll">Expert en <span>Hygiène</span> Professionnelle</h1>
            <p class="animate-on-scroll">Solutions performantes de nettoyage, désinfection et traitement pour l'industrie agroalimentaire et environnementale au Maroc.</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-primary"><i class="fas fa-box-open"></i> Nos Produits</a>
                <a href="<?= WHATSAPP_LINK ?>" target="_blank" class="btn btn-whatsapp"><i class="fab fa-whatsapp"></i> Commander via WhatsApp</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat"><span class="number counter" data-target="200">0+</span><span class="label">Produits</span></div>
                <div class="hero-stat"><span class="number counter" data-target="500">0+</span><span class="label">Clients Satisfaits</span></div>
                <div class="hero-stat"><span class="number counter" data-target="15">0+</span><span class="label">Ans d'Expérience</span></div>
            </div>
        </div>
        <div class="hero-shapes">
            <div class="hero-shape hero-shape-1"></div>
            <div class="hero-shape hero-shape-2"></div>
            <div class="hero-shape hero-shape-3"></div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="section features">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card animate-on-scroll">
                <div class="icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Qualité Certifiée</h3>
                <p>Produits conformes aux normes internationales HACCP et aux standards d'hygiène les plus stricts.</p>
            </div>
            <div class="feature-card animate-on-scroll delay-1">
                <div class="icon"><i class="fas fa-truck-fast"></i></div>
                <h3>Livraison Rapide</h3>
                <p>Service de livraison sur tout le Maroc avec un suivi de commande en temps réel.</p>
            </div>
            <div class="feature-card animate-on-scroll delay-2">
                <div class="icon"><i class="fas fa-headset"></i></div>
                <h3>Support Expert</h3>
                <p>Équipe technique à votre écoute pour vous conseiller et accompagner vos projets.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="section" style="background: var(--gray-100);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Ce que nous offrons</span>
            <h2>Nos Catégories</h2>
            <p>Découvrez notre gamme complète de produits d'hygiène professionnelle</p>
        </div>
        <div class="categories-grid">
            <?php foreach($categories as $cat): ?>
            <a href="products.php?category=<?= $cat['slug'] ?>" class="category-card animate-on-scroll">
                <div class="cat-icon"><i class="<?= $cat['icon'] ?>"></i></div>
                <h3><?= sanitize($cat['name']) ?></h3>
                <p><?= sanitize(mb_strimwidth($cat['description'], 0, 80, '...')) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section products-section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Nos Best-sellers</span>
            <h2>Produits Phares</h2>
            <p>Les produits les plus demandés par nos clients professionnels</p>
        </div>
        <div class="products-grid">
            <?php foreach($featured as $prod): ?>
            <div class="product-card animate-on-scroll">
                <div class="product-image">
                    <?php if(!empty($prod['image'])): ?>
                    <img src="<?= SITE_URL ?>/uploads/products/<?= $prod['image'] ?>" alt="<?= sanitize($prod['name']) ?>">
                    <?php else: ?>
                    <i class="fas fa-box-open placeholder-icon"></i>
                    <?php endif; ?>
                    <?php if($prod['is_featured']): ?>
                    <span class="product-badge">Populaire</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-category"><?= sanitize($prod['category_name']) ?></span>
                    <h3 class="product-name"><?= sanitize($prod['name']) ?></h3>
                    <p class="product-desc"><?= sanitize($prod['short_description'] ?? '') ?></p>
                    <div class="product-actions">
                        <a href="product.php?slug=<?= $prod['slug'] ?>" class="btn btn-outline">Détails</a>
                        <a href="<?= getWhatsAppOrderLink($prod) ?>" target="_blank" class="btn btn-whatsapp">
                            <i class="fab fa-whatsapp"></i> Commander
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center; margin-top:40px;">
            <a href="products.php" class="btn btn-primary"><i class="fas fa-th-large"></i> Voir tous les produits</a>
        </div>
    </div>
</section>

<!-- Engagements -->
<section class="section" style="background: var(--gray-100);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Pourquoi WH Solutions</span>
            <h2>Nos Engagements</h2>
            <p>Des valeurs fortes au service de votre satisfaction</p>
        </div>
        <div class="engagement-grid">
            <div class="engagement-card animate-on-scroll">
                <div class="eng-icon"><i class="fas fa-leaf"></i></div>
                <h3>Éco-Responsable</h3>
                <p>Solutions respectueuses de l'environnement avec des formulations biodégradables et des emballages recyclables.</p>
            </div>
            <div class="engagement-card animate-on-scroll delay-1">
                <div class="eng-icon"><i class="fas fa-flask"></i></div>
                <h3>Innovation</h3>
                <p>Recherche et développement continus pour vous proposer les solutions les plus performantes du marché.</p>
            </div>
            <div class="engagement-card animate-on-scroll delay-2">
                <div class="eng-icon"><i class="fas fa-handshake"></i></div>
                <h3>Partenariat</h3>
                <p>Accompagnement personnalisé et formation de vos équipes pour une utilisation optimale de nos produits.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content animate-on-scroll">
            <h2>Besoin d'un devis personnalisé ?</h2>
            <p>Contactez-nous via WhatsApp pour recevoir une offre adaptée à vos besoins</p>
            <a href="<?= WHATSAPP_LINK ?>" target="_blank" class="btn btn-whatsapp" style="font-size:1.1rem; padding:15px 40px;">
                <i class="fab fa-whatsapp"></i> Demander un Devis
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>