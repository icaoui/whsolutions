<?php
$pageTitle = 'Nos Produits - WH Solutions';
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
        <div style="display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin-bottom:40px;">
            <a href="products.php" class="btn <?= !$categorySlug ? 'btn-primary' : 'btn-outline' ?>" style="padding:8px 20px; font-size:0.85rem;">Tous</a>
            <?php foreach($categories as $cat): ?>
            <a href="products.php?category=<?= $cat['slug'] ?>" class="btn <?= $categorySlug === $cat['slug'] ? 'btn-primary' : 'btn-outline' ?>" style="padding:8px 20px; font-size:0.85rem;">
                <?= sanitize($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if(empty($products)): ?>
        <div style="text-align:center; padding:60px 0;">
            <i class="fas fa-box-open" style="font-size:4rem; color:var(--gray-400); margin-bottom:20px;"></i>
            <h3 style="color:var(--gray-600);">Aucun produit trouvé</h3>
            <p style="color:var(--gray-500);">Essayez une autre recherche ou catégorie</p>
            <a href="products.php" class="btn btn-primary" style="margin-top:20px;">Voir tous les produits</a>
        </div>
        <?php else: ?>
        <p style="text-align:center; color:var(--gray-500); margin-bottom:30px;"><?= $totalProducts ?> produit(s) trouvé(s)</p>
        <div class="products-grid">
            <?php foreach($products as $prod): ?>
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
        <div class="cta-content animate-on-scroll">
            <h2>Vous ne trouvez pas ce que vous cherchez ?</h2>
            <p>Contactez-nous et nous vous proposerons la solution adaptée</p>
            <a href="<?= WHATSAPP_LINK ?>" target="_blank" class="btn btn-whatsapp" style="font-size:1.1rem; padding:15px 40px;">
                <i class="fab fa-whatsapp"></i> Nous Contacter
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>