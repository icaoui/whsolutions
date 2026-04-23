<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) { header('Location: products.php'); exit; }

$product = getProductBySlug($pdo, $slug);
if (!$product) { header('Location: products.php'); exit; }

incrementProductViews($pdo, $product['id']);
logVisitor($pdo, 'product_' . $product['id']);
$related = getRelatedProducts($pdo, $product['id'], $product['category_id'], 4);
$pageTitle = sanitize($product['name']) . ' - WH Solutions Maroc';
$pageDescription = 'Achetez ' . sanitize($product['name']) . ' chez WH Solutions. ' . sanitize(mb_strimwidth($product['short_description'] ?? $product['description'] ?? '', 0, 130, '...')) . ' Livraison au Maroc.';
$pageKeywords = sanitize($product['name']) . ', ' . sanitize($product['category_name']) . ', hygiène professionnelle Maroc, WH Solutions';

// Handle inquiry form
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $qty = intval($_POST['quantity'] ?? 1);
    $msg = sanitize($_POST['message'] ?? '');
    if (empty($name) || empty($phone)) {
        $error = 'Veuillez remplir les champs obligatoires.';
    } else {
        saveInquiry($pdo, [
            'product_id' => $product['id'],
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'quantity' => $qty,
            'message' => $msg
        ]);
        $success = 'Votre demande a été envoyée avec succès !';
    }
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><?= sanitize($product['name']) ?></h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Accueil</a>
            <span>/</span>
            <a href="<?= SITE_URL ?>/products.php">Produits</a>
            <span>/</span>
            <a href="<?= SITE_URL ?>/products.php?category=<?= $product['category_slug'] ?>"><?= sanitize($product['category_name']) ?></a>
            <span>/</span>
            <span><?= sanitize($product['name']) ?></span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div class="product-detail-grid">
            <!-- Image -->
            <div class="product-gallery animate-left">
                <?php if(!empty($product['image'])): ?>
                <img src="<?= SITE_URL ?>/uploads/products/<?= $product['image'] ?>" alt="<?= sanitize($product['name']) ?>">
                <?php else: ?>
                <i class="fas fa-box-open placeholder-icon"></i>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="product-detail-info animate-right">
                <span class="product-category" style="display:inline-block; margin-bottom:10px;"><?= sanitize($product['category_name']) ?></span>
                <h1><?= sanitize($product['name']) ?></h1>
                <div class="product-meta">
                    <span><i class="fas fa-eye"></i> <?= $product['views'] ?> vues</span>
                    <span><i class="fas fa-tag"></i> <?= sanitize($product['category_name']) ?></span>
                    <?php if(!empty($product['reference'])): ?>
                    <span><i class="fas fa-barcode"></i> Réf: <?= sanitize($product['reference']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="description">
                    <?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?>
                </div>

                <!-- Quantity & WhatsApp -->
                <div class="quantity-selector">
                    <label>Quantité :</label>
                    <div class="qty-controls">
                        <button type="button" class="qty-btn qty-minus">-</button>
                        <input type="number" class="qty-input" value="1" min="1">
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </div>
                </div>
                <a href="<?= getWhatsAppOrderLink($product) ?>" target="_blank" class="btn btn-whatsapp btn-whatsapp-order" data-product="<?= sanitize($product['name']) ?>" id="orderWhatsApp" onclick="return showOrderConfirm(event)" style="width:100%; justify-content:center; padding:16px; font-size:1.1rem;">
                    <i class="fab fa-whatsapp"></i> Commander via WhatsApp
                </a>

                <!-- Inquiry Form -->
                <div style="margin-top:30px; padding:25px; background:var(--gray-100); border-radius:var(--radius);">
                    <h3 style="font-size:1.1rem; color:var(--primary); margin-bottom:15px;"><i class="fas fa-envelope"></i> Demande de Renseignement</h3>
                    <form method="POST">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                            <div class="form-group">
                                <input type="text" name="name" placeholder="Votre nom *" required>
                            </div>
                            <div class="form-group">
                                <input type="tel" name="phone" placeholder="Votre téléphone *" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Votre email">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 2fr; gap:15px;">
                            <div class="form-group">
                                <input type="number" name="quantity" placeholder="Quantité" value="1" min="1">
                            </div>
                            <div class="form-group">
                                <input type="text" name="message" placeholder="Message (optionnel)">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fas fa-paper-plane"></i> Envoyer la Demande</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if(!empty($related)): ?>
        <div style="margin-top:80px;">
            <div class="section-header">
                <h2>Produits Similaires</h2>
            </div>
            <div class="products-grid" data-stagger>
                <?php foreach($related as $rel): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if(!empty($rel['image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/products/<?= $rel['image'] ?>" alt="<?= sanitize($rel['name']) ?>">
                        <?php else: ?>
                        <i class="fas fa-box-open placeholder-icon"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= sanitize($rel['name']) ?></h3>
                        <div class="product-actions">
                            <a href="product.php?slug=<?= $rel['slug'] ?>" class="btn btn-outline">Détails</a>
                            <a href="<?= getWhatsAppOrderLink($rel) ?>" target="_blank" class="btn btn-whatsapp"><i class="fab fa-whatsapp"></i> Commander</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Order Confirmation Modal -->
<div class="order-modal-overlay" id="orderModal">
    <div class="order-modal">
        <button class="order-modal-close" onclick="closeOrderModal()">&times;</button>
        <div class="order-modal-icon"><i class="fab fa-whatsapp"></i></div>
        <h3>Confirmer votre commande</h3>
        <div class="order-summary">
            <div class="order-summary-row">
                <span>Produit</span>
                <strong id="modalProduct"><?= sanitize($product['name']) ?></strong>
            </div>
            <div class="order-summary-row">
                <span>Catégorie</span>
                <strong><?= sanitize($product['category_name']) ?></strong>
            </div>
            <div class="order-summary-row">
                <span>Quantité</span>
                <strong id="modalQty">1</strong>
            </div>
            <?php if(!empty($product['reference'])): ?>
            <div class="order-summary-row">
                <span>Référence</span>
                <strong><?= sanitize($product['reference']) ?></strong>
            </div>
            <?php endif; ?>
        </div>
        <p class="order-modal-note"><i class="fas fa-info-circle"></i> Vous allez être redirigé vers WhatsApp pour finaliser votre commande avec notre équipe.</p>
        <div class="order-modal-actions">
            <button class="btn btn-outline" onclick="closeOrderModal()">Annuler</button>
            <a href="#" id="confirmOrderBtn" target="_blank" class="btn btn-whatsapp" onclick="orderSent()">
                <i class="fab fa-whatsapp"></i> Confirmer & Envoyer
            </a>
        </div>
    </div>
</div>

<!-- Order Sent Thank You Banner -->
<div class="order-thankyou" id="orderThankyou" style="display:none;">
    <div class="container">
        <div class="thankyou-content">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Commande envoyée avec succès !</strong>
                <p>Notre équipe vous répondra dans les plus brefs délais sur WhatsApp.</p>
            </div>
            <button onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="thankyou-close">&times;</button>
        </div>
    </div>
</div>

<script>
window.whConfig = {
    number: '<?= sanitize(getSetting($pdo, "whatsapp_number", WHATSAPP_NUMBER)) ?>',
    orderMsg: <?= json_encode(getSetting($pdo, 'whatsapp_order_message', 'Bonjour, je souhaite commander le produit : *{product}* (Quantité: {quantity})'), JSON_UNESCAPED_UNICODE) ?>
};

function showOrderConfirm(e) {
    e.preventDefault();
    const qty = document.querySelector('.qty-input')?.value || 1;
    document.getElementById('modalQty').textContent = qty;
    const btn = document.getElementById('orderWhatsApp');
    document.getElementById('confirmOrderBtn').href = btn.href;
    document.getElementById('orderModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    return false;
}
function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('active');
    document.body.style.overflow = '';
}
function orderSent() {
    closeOrderModal();
    sessionStorage.setItem('orderSent', '1');
    setTimeout(() => {
        document.getElementById('orderThankyou').style.display = 'block';
        document.getElementById('orderThankyou').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 500);
}
document.getElementById('orderModal').addEventListener('click', function(e) {
    if (e.target === this) closeOrderModal();
});
if (sessionStorage.getItem('orderSent')) {
    sessionStorage.removeItem('orderSent');
    document.getElementById('orderThankyou').style.display = 'block';
}
</script>

<?php require_once 'includes/footer.php'; ?>