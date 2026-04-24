<?php
$pageTitle = 'WH Solutions - Expert en hygiène professionnelle au Maroc | Nettoyage & Désinfection';
$pageDescription = 'WH Solutions, votre expert en hygiène professionnelle au Maroc. Produits de nettoyage, désinfection, traitement des eaux conformes HACCP. Livraison partout au Maroc.';
$pageKeywords = 'hygiène professionnelle Maroc, produits nettoyage industriel, désinfection HACCP, WH Solutions Kenitra, produits hygiène agroalimentaire';
require_once 'config/config.php';
require_once 'includes/functions.php';
logVisitor($pdo, 'home');
$featured = getFeaturedProducts($pdo, 8);
$categories = getCategories($pdo);
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="background-image: url('assets/images/hero-bg.png');">
    <div class="hero-particles"></div>
    <div class="container">
        <div class="hero-content">
            <h1>
                <span class="hero-line"><span class="hero-word">Expert en</span></span>
                <span class="hero-line"><span class="hero-word"><span>Hygiène</span></span></span>
                <span class="hero-line"><span class="hero-word">Professionnelle</span></span>
            </h1>
            <p class="hero-subtitle">Solutions performantes de nettoyage, désinfection et traitement pour l'industrie agroalimentaire et environnementale au Maroc.</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-primary"><i class="fas fa-box-open"></i> Découvrir nos Produits</a>
                <a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" class="btn btn-whatsapp"><i class="fab fa-whatsapp"></i> Commander via WhatsApp</a>
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
            <div class="hero-shape hero-shape-4"></div>
        </div>
    </div>
    <div class="hero-scroll-indicator">
        <div class="mouse"></div>
        <span>Scroll</span>
    </div>
</section>

<!-- Marquee -->
<div class="marquee-section">
    <div class="marquee-track">
        <div class="marquee-item"><i class="fas fa-shield-alt"></i> Normes HACCP</div>
        <div class="marquee-item"><i class="fas fa-leaf"></i> Éco-Responsable</div>
        <div class="marquee-item"><i class="fas fa-truck-fast"></i> Livraison Nationale</div>
        <div class="marquee-item"><i class="fas fa-award"></i> Qualité Premium</div>
        <div class="marquee-item"><i class="fas fa-headset"></i> Support 24/7</div>
        <div class="marquee-item"><i class="fas fa-flask"></i> Innovation Continue</div>
        <div class="marquee-item"><i class="fas fa-handshake"></i> +500 Clients</div>
        <div class="marquee-item"><i class="fas fa-certificate"></i> Certifié ISO</div>
        <div class="marquee-item"><i class="fas fa-shield-alt"></i> Normes HACCP</div>
        <div class="marquee-item"><i class="fas fa-leaf"></i> Éco-Responsable</div>
        <div class="marquee-item"><i class="fas fa-truck-fast"></i> Livraison Nationale</div>
        <div class="marquee-item"><i class="fas fa-award"></i> Qualité Premium</div>
        <div class="marquee-item"><i class="fas fa-headset"></i> Support 24/7</div>
        <div class="marquee-item"><i class="fas fa-flask"></i> Innovation Continue</div>
        <div class="marquee-item"><i class="fas fa-handshake"></i> +500 Clients</div>
        <div class="marquee-item"><i class="fas fa-certificate"></i> Certifié ISO</div>
    </div>
</div>

<!-- Features -->
<section class="section features">
    <div class="container">
        <div class="row g-4" data-stagger>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card text-center p-4">
                    <div class="icon mb-3"><i class="fas fa-shield-alt"></i></div>
                    <h3>Qualité Certifiée</h3>
                    <p>Produits conformes aux normes internationales HACCP et aux standards d'hygiène les plus stricts.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card text-center p-4">
                    <div class="icon mb-3"><i class="fas fa-truck-fast"></i></div>
                    <h3>Livraison Rapide</h3>
                    <p>Service de livraison sur tout le Maroc avec un suivi de commande en temps réel.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="feature-card text-center p-4">
                    <div class="icon mb-3"><i class="fas fa-headset"></i></div>
                    <h3>Support Expert</h3>
                    <p>Équipe technique à votre écoute pour vous conseiller et accompagner vos projets.</p>
                </div>
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
        <div class="row g-4" data-stagger>
            <?php 
            $catImages = [
                'nettoyage-desinfection' => 'cat-cleaning.png',
                'traitement-eaux' => 'cat-water.jpg',
                'hygiene-personnel' => 'cat-hygiene.png',
                'materiel-nettoyage' => 'cat-material.png'
            ];
            foreach($categories as $i => $cat): 
                $catImg = $catImages[$cat['slug']] ?? '';
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <a href="products.php?category=<?= $cat['slug'] ?>" class="category-card<?= $catImg ? ' has-image' : '' ?> d-block h-100">
                    <?php if($catImg): ?><img src="assets/images/<?= $catImg ?>" alt="<?= sanitize($cat['name']) ?>" class="cat-bg-img"><?php endif; ?>
                    <div class="cat-icon"><i class="<?= $cat['icon'] ?>"></i></div>
                    <h3><?= sanitize($cat['name']) ?></h3>
                    <p><?= sanitize(mb_strimwidth($cat['description'], 0, 80, '...')) ?></p>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- Featured Products -->
<section class="section products-section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Nos Best-sellers</span>
            <h2>Produits Phares</h2>
            <p>Les produits les plus demandés par nos clients professionnels</p>
        </div>
        <div class="row g-4" data-stagger>
            <?php foreach($featured as $prod): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="product-card h-100">
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
        <div class="row g-4" data-stagger>
            <div class="col-lg-4 col-md-6">
                <div class="engagement-card text-center h-100">
                    <img src="assets/images/icon-quality.png" alt="Qualité" class="eng-img">
                    <h3>Qualité et Performance</h3>
                    <p>Des produits et services testés pour garantir un niveau d'hygiène irréprochable.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="engagement-card text-center h-100">
                    <img src="assets/images/icon-environment.png" alt="Environnement" class="eng-img">
                    <h3>Respect de l'Environnement</h3>
                    <p>Solutions biodégradables et écoresponsables, pour une hygiène durable.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="engagement-card text-center h-100">
                    <img src="assets/images/icon-security.png" alt="Sécurité" class="eng-img">
                    <h3>Sécurité et Efficacité</h3>
                    <p>Des interventions conformes aux normes, sans compromis sur la performance.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Carousel -->
<section class="section" style="background:var(--white);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Témoignages</span>
            <h2>Ce Que Disent Nos Clients</h2>
            <p>La satisfaction de nos partenaires est notre meilleure récompense</p>
        </div>
        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="text-center px-4 py-5" style="background:var(--gray-100); border-radius:var(--radius-lg); position:relative;">
                                <i class="fas fa-quote-left" style="font-size:2.5rem; color:var(--secondary); opacity:0.3; position:absolute; top:20px; left:30px;"></i>
                                <div class="mb-3">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                </div>
                                <p class="fs-5 fst-italic mb-4" style="color:var(--gray-700); line-height:1.8;">"WH Solutions nous fournit des produits d'hygiène de qualité exceptionnelle. Leur service client est réactif et leurs livraisons toujours à temps. Un partenaire de confiance."</p>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:linear-gradient(135deg,var(--secondary),var(--neon));color:#fff;font-weight:700;">AK</div>
                                    <div class="text-start">
                                        <strong style="color:var(--primary);">Ahmed K.</strong>
                                        <div style="font-size:0.82rem; color:var(--gray-500);">Directeur Qualité — Industrie Agroalimentaire</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="text-center px-4 py-5" style="background:var(--gray-100); border-radius:var(--radius-lg); position:relative;">
                                <i class="fas fa-quote-left" style="font-size:2.5rem; color:var(--secondary); opacity:0.3; position:absolute; top:20px; left:30px;"></i>
                                <div class="mb-3">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                </div>
                                <p class="fs-5 fst-italic mb-4" style="color:var(--gray-700); line-height:1.8;">"Depuis que nous travaillons avec WH Solutions, notre conformité HACCP s'est nettement améliorée. Des produits performants et un accompagnement technique remarquable."</p>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:linear-gradient(135deg,var(--purple),var(--secondary));color:#fff;font-weight:700;">SM</div>
                                    <div class="text-start">
                                        <strong style="color:var(--primary);">Sara M.</strong>
                                        <div style="font-size:0.82rem; color:var(--gray-500);">Responsable Hygiène — Hôtellerie</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="text-center px-4 py-5" style="background:var(--gray-100); border-radius:var(--radius-lg); position:relative;">
                                <i class="fas fa-quote-left" style="font-size:2.5rem; color:var(--secondary); opacity:0.3; position:absolute; top:20px; left:30px;"></i>
                                <div class="mb-3">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                </div>
                                <p class="fs-5 fst-italic mb-4" style="color:var(--gray-700); line-height:1.8;">"Commande simple via WhatsApp, livraison rapide et produits de qualité. WH Solutions comprend les besoins des professionnels. Je recommande vivement."</p>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:linear-gradient(135deg,var(--gold),#FFB800);color:#5D4600;font-weight:700;">YB</div>
                                    <div class="text-start">
                                        <strong style="color:var(--primary);">Youssef B.</strong>
                                        <div style="font-size:0.82rem; color:var(--gray-500);">Gérant — Restaurant & Traiteur</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-4">
                <button class="btn btn-sm rounded-circle" data-bs-target="#testimonialCarousel" data-bs-slide="prev" style="width:40px;height:40px;background:var(--primary);color:#fff;border:none;"><i class="fas fa-chevron-left"></i></button>
                <button class="btn btn-sm rounded-circle" data-bs-target="#testimonialCarousel" data-bs-slide="next" style="width:40px;height:40px;background:var(--secondary);color:#fff;border:none;"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</section>

<!-- Catalogue Download -->
<section class="section catalogue-download-section">
    <div class="container">
        <div class="catalogue-download animate-scale">
            <div class="catalogue-download-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="catalogue-download-content">
                <span class="section-tag">Catalogue <?= date('Y') ?></span>
                <h2>Téléchargez Notre Catalogue Complet</h2>
                <p>Plus de 200 produits d'hygiène professionnelle. Nettoyage, désinfection, traitement des eaux et bien plus.</p>
                <div class="catalogue-download-buttons">
                    <a href="catalogue.php" class="btn btn-primary"><i class="fas fa-book-open"></i> Voir le Catalogue</a>
                    <a href="<?= CATALOGUE_PDF ?>" target="_blank" class="btn btn-outline"><i class="fas fa-download"></i> Télécharger PDF</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content animate-scale">
            <h2>Besoin d'un devis personnalisé ?</h2>
            <p>Contactez-nous via WhatsApp pour recevoir une offre adaptée à vos besoins</p>
            <a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" class="btn btn-whatsapp" style="font-size:1.1rem; padding:18px 45px;">
                <i class="fab fa-whatsapp"></i> Demander un Devis
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>