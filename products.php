<?php
$pageTitle = 'Nos Produits d\'Hygiène Professionnelle - WH Solutions Maroc';
$pageDescription = 'Découvrez notre gamme complète de produits d\'hygiène professionnelle : nettoyage, désinfection, traitement des eaux, matériel de nettoyage au Maroc.';
$pageKeywords = 'produits hygiène Maroc, nettoyage industriel, désinfection professionnelle, matériel nettoyage, consommables hygiène';
require_once 'config/config.php';
require_once 'includes/functions.php';
logVisitor($pdo, 'products');

$categorySlug = $_GET['category'] ?? null;
$search = isset($_GET['q']) ? sanitize($_GET['q']) : null;
$page = max(1, intval($_GET['page'] ?? 1));
$category = null;
$categoryId = null;

if ($categorySlug) {
    $category = getCategoryBySlug($pdo, $categorySlug);
    if ($category) $categoryId = $category['id'];
}

$totalProducts = countProducts($pdo, $categoryId, $search);
$totalPages = ceil($totalProducts / PRODUCTS_PER_PAGE);
$products = getProducts($pdo, $categoryId, $page, PRODUCTS_PER_PAGE, $search);
$categories = getCategories($pdo);

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><?= $category ? sanitize($category['name']) : ($search ? 'Recherche : ' . sanitize($search) : 'Nos Produits') ?></h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Accueil</a>
            <span>/</span>
            <?php if($category): ?>
            <a href="<?= SITE_URL ?>/products.php">Produits</a>
            <span>/</span>
            <span><?= sanitize($category['name']) ?></span>
            <?php else: ?>
            <span>Produits</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section products-section">
    <div class="container">
        <!-- Search -->
        <form class="search-bar" action="products.php" method="GET">
            <input type="text" name="q" placeholder="Rechercher un produit..." value="<?= sanitize($search ?? '') ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>

        <!-- Category Filter -->
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
            <a href="products.php" class="btn btn-sm <?= !$categorySlug ? 'text-white' : '' ?> fw-semibold" style="padding:8px 22px; border-radius:50px; <?= !$categorySlug ? 'background:linear-gradient(135deg,var(--primary),var(--secondary)); border:none;' : 'border:2px solid var(--gray-200); color:var(--gray-700);' ?>">
                <i class="fas fa-th-large me-1"></i> Tous
                <span class="badge bg-white text-dark ms-1"><?= $totalProducts ?></span>
            </a>
            <?php foreach($categories as $cat): ?>
            <a href="products.php?category=<?= $cat['slug'] ?>" class="btn btn-sm <?= $categorySlug === $cat['slug'] ? 'text-white' : '' ?> fw-semibold" style="padding:8px 22px; border-radius:50px; <?= $categorySlug === $cat['slug'] ? 'background:linear-gradient(135deg,var(--primary),var(--secondary)); border:none;' : 'border:2px solid var(--gray-200); color:var(--gray-700);' ?>">
                <i class="<?= $cat['icon'] ?> me-1"></i> <?= sanitize($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if(empty($products)): ?>
        <div class="text-center py-5">
            <div class="mb-4" style="width:120px;height:120px;margin:0 auto;background:var(--gray-100);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-search" style="font-size:3rem; color:var(--gray-400);"></i>
            </div>
            <h3 class="fw-bold" style="color:var(--primary);">Aucun produit trouvé</h3>
            <p style="color:var(--gray-500); max-width:400px; margin:10px auto 25px;">Essayez une autre recherche ou sélectionnez une catégorie différente</p>
            <a href="products.php" class="btn btn-primary" style="background:linear-gradient(135deg,var(--primary),var(--secondary)); border:none; padding:12px 30px; border-radius:50px;">
                <i class="fas fa-th-large me-2"></i> Voir tous les produits
            </a>
        </div>
        <?php else: ?>
        <p style="text-align:center; color:var(--gray-500); margin-bottom:30px;"><?= $totalProducts ?> produit(s) trouvé(s)</p>
        <div class="products-grid" data-stagger>
            <?php foreach($products as $prod): ?>
            <div class="product-card">
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

        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <?php if($i == $page): ?>
            <span class="current"><?= $i ?></span>
            <?php else: ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
            <?php endif; ?>
            <?php endfor; ?>
            <?php if($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content animate-scale">
            <h2>Vous ne trouvez pas ce que vous cherchez ?</h2>
            <p>Contactez-nous et nous vous proposerons la solution adaptée</p>
            <a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" class="btn btn-whatsapp" style="font-size:1.1rem; padding:15px 40px;">
                <i class="fab fa-whatsapp"></i> Nous Contacter
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>