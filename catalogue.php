<?php
$pageTitle = 'Catalogue - WH Solutions';
$pageDescription = 'Téléchargez le catalogue complet WH Solutions. Découvrez notre gamme de produits d\'hygiène professionnelle au Maroc.';
$pageKeywords = 'catalogue WH Solutions, catalogue hygiène professionnelle, catalogue produits nettoyage Maroc, télécharger catalogue';
require_once 'config/config.php';
require_once 'includes/functions.php';
logVisitor($pdo, 'catalogue');
$categories = getCategories($pdo);
require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Notre Catalogue</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Accueil</a>
            <span>/</span>
            <span>Catalogue</span>
        </div>
    </div>
</section>

<!-- Catalogue Section -->
<section class="section catalogue-section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Téléchargement</span>
            <h2>Catalogue Produits</h2>
            <p>Consultez ou téléchargez notre catalogue complet pour découvrir toute notre gamme</p>
        </div>

        <div class="catalogue-hero animate-scale">
            <div class="catalogue-card-main">
                <div class="catalogue-visual">
                    <div class="catalogue-icon-big">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="catalogue-decoration">
                        <div class="cat-deco-circle"></div>
                        <div class="cat-deco-circle cat-deco-2"></div>
                    </div>
                </div>
                <div class="catalogue-info">
                    <h3>Catalogue WH Solutions <?= date('Y') ?></h3>
                    <p>Notre catalogue complet comprend plus de 200 produits d'hygiène professionnelle couvrant toutes les catégories :</p>
                    <ul class="catalogue-features">
                        <?php foreach(array_slice($categories, 0, 6) as $cat): ?>
                        <li><i class="<?= $cat['icon'] ?>"></i> <?= sanitize($cat['name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="catalogue-actions">
                        <a href="<?= CATALOGUE_PDF ?>" target="_blank" class="btn btn-primary btn-lg">
                            <i class="fas fa-eye"></i> Consulter en Ligne
                        </a>
                        <a href="<?= CATALOGUE_PDF ?>" target="_blank" class="btn btn-outline btn-lg" download>
                            <i class="fas fa-download"></i> Télécharger PDF
                        </a>
                    </div>
                    <p class="catalogue-note"><i class="fas fa-info-circle"></i> Le catalogue s'ouvrira dans Google Drive. Vous pouvez le consulter directement ou le télécharger.</p>
                </div>
            </div>
        </div>

        <!-- Quick Category Access -->
        <div style="margin-top:80px;">
            <div class="section-header">
                <span class="section-tag">Accès rapide</span>
                <h2>Explorer par Catégorie</h2>
            </div>
            <div class="categories-grid" data-stagger>
                <?php foreach($categories as $cat): ?>
                <a href="products.php?category=<?= $cat['slug'] ?>" class="category-card">
                    <div class="cat-icon"><i class="<?= $cat['icon'] ?>"></i></div>
                    <h3><?= sanitize($cat['name']) ?></h3>
                    <p><?= sanitize(mb_strimwidth($cat['description'], 0, 80, '...')) ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content animate-scale">
            <h2>Besoin d'informations supplémentaires ?</h2>
            <p>Notre équipe est à votre disposition pour vous conseiller sur le choix de vos produits</p>
            <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
                <a href="<?= WHATSAPP_LINK ?>" target="_blank" class="btn btn-whatsapp" style="font-size:1.1rem; padding:18px 45px;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="contact.php" class="btn btn-outline" style="font-size:1.1rem; padding:18px 45px; color:var(--white); border-color:var(--white);">
                    <i class="fas fa-envelope"></i> Contact
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
