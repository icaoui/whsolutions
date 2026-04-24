<?php
$pageTitle = 'Nos Packages - ' . SITE_NAME;
$pageDescription = 'Découvrez nos propositions de valeur et packages d\'hygiène professionnelle adaptés à votre activité.';
require_once 'includes/header.php';

// Fetch active packages with features
$packages = $pdo->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll();
$packageFeatures = [];
if ($packages) {
    $ids = array_column($packages, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM package_features WHERE package_id IN ($placeholders) ORDER BY sort_order");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $f) {
        $packageFeatures[$f['package_id']][] = $f;
    }
}

// Handle package request form
$formSuccess = false;
$formError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_package'])) {
    $name = trim($_POST['client_name'] ?? '');
    $email = trim($_POST['client_email'] ?? '');
    $phone = trim($_POST['client_phone'] ?? '');
    $company = trim($_POST['client_company'] ?? '');
    $city = trim($_POST['client_city'] ?? '');
    $pkgId = (int)($_POST['package_id'] ?? 0);

    if (empty($name) || empty($phone) || !$pkgId) {
        $formError = 'Veuillez remplir les champs obligatoires.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO customer_packages (customer_name, customer_email, customer_phone, customer_company, customer_city, package_id, status) VALUES (?,?,?,?,?,?,'pending')");
        $stmt->execute([$name, $email, $phone, $company, $city, $pkgId]);
        $formSuccess = true;
    }
}
?>

<!-- Hero -->
<section class="page-header" style="background:linear-gradient(135deg, var(--primary) 0%, #0a1628 100%); position:relative; overflow:hidden;">
    <div style="position:absolute; inset:0; opacity:0.1;">
        <div style="position:absolute; width:400px; height:400px; border-radius:50%; background:var(--secondary); top:-100px; right:-100px; filter:blur(80px);"></div>
        <div style="position:absolute; width:300px; height:300px; border-radius:50%; background:var(--neon); bottom:-80px; left:-50px; filter:blur(60px);"></div>
    </div>
    <div class="container" style="position:relative; z-index:2; text-align:center;">
        <span class="section-tag" style="background:rgba(78,205,196,0.2); color:var(--neon);">Propositions de Valeur</span>
        <h1 style="font-size:clamp(2rem,5vw,3.5rem); font-weight:800; color:#fff; margin:15px 0;">Choisissez votre <span style="background:linear-gradient(135deg,var(--secondary),var(--neon)); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Package</span></h1>
        <p style="color:rgba(255,255,255,0.7); font-size:1.1rem; max-width:600px; margin:0 auto;">Des solutions d'hygiène professionnelle sur mesure, adaptées à la taille et aux besoins de votre entreprise</p>
    </div>
</section>

<?php if($formSuccess): ?>
<div class="container" style="margin-top:30px;">
    <div class="alert alert-dismissible fade show" style="background:linear-gradient(135deg,#d4edda,#c3e6cb); border:none; border-radius:16px; padding:25px 30px; display:flex; align-items:center; gap:15px;">
        <div style="width:50px; height:50px; border-radius:50%; background:#27ae60; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fas fa-check" style="color:#fff; font-size:1.2rem;"></i>
        </div>
        <div>
            <strong style="color:#155724; font-size:1.1rem;">Demande envoyée avec succès !</strong>
            <p style="color:#155724; margin:5px 0 0; opacity:0.8;">Notre équipe vous contactera sous 24h pour activer votre package. Merci de votre confiance !</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<?php if($formError): ?>
<div class="container" style="margin-top:30px;">
    <div class="alert alert-dismissible fade show" style="background:#ffeef0; border:none; border-radius:16px; padding:20px 30px;">
        <i class="fas fa-exclamation-circle" style="color:#e74c3c;"></i> <?= $formError ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Packages Grid -->
<section class="section" style="background:var(--gray-100);">
    <div class="container">
        <?php if(empty($packages)): ?>
        <div style="text-align:center; padding:60px 0;">
            <i class="fas fa-gem" style="font-size:4rem; color:var(--gray-400); margin-bottom:20px;"></i>
            <h3 style="color:var(--gray-600);">Packages en cours de préparation</h3>
            <p style="color:var(--gray-500);">Nos propositions de valeur seront bientôt disponibles. Contactez-nous pour un devis personnalisé.</p>
        </div>
        <?php else: ?>
        <div class="row g-4 justify-content-center" id="packagesGrid">
            <?php foreach($packages as $i => $pkg): 
                $features = $packageFeatures[$pkg['id']] ?? [];
                $isPopular = $pkg['is_popular'];
                $color = $pkg['color'] ?: '#4ECDC4';
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="package-card <?= $isPopular ? 'package-popular' : '' ?>" style="--pkg-color:<?= sanitize($color) ?>;" data-animate="fade-up" data-delay="<?= $i * 100 ?>">
                    <?php if($pkg['badge']): ?>
                    <div class="package-badge" style="background:linear-gradient(135deg, <?= sanitize($color) ?>, <?= sanitize($color) ?>cc);"><?= sanitize($pkg['badge']) ?></div>
                    <?php endif; ?>
                    
                    <div class="package-header">
                        <div class="package-icon-wrap" style="background:<?= sanitize($color) ?>15;">
                            <i class="<?= sanitize($pkg['icon']) ?>" style="color:<?= sanitize($color) ?>; font-size:2rem;"></i>
                        </div>
                        <h3 class="package-name"><?= sanitize($pkg['name']) ?></h3>
                        <?php if($pkg['subtitle']): ?>
                        <p class="package-subtitle"><?= sanitize($pkg['subtitle']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="package-price">
                        <?php if($pkg['price']): ?>
                        <div class="price-amount">
                            <span class="price-value"><?= number_format($pkg['price'], 0, ',', '.') ?></span>
                            <span class="price-currency">MAD</span>
                        </div>
                        <?php if($pkg['price_label']): ?>
                        <span class="price-label"><?= sanitize($pkg['price_label']) ?></span>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="price-amount">
                            <span class="price-value" style="font-size:1.5rem;">Sur devis</span>
                        </div>
                        <span class="price-label">Contactez-nous</span>
                        <?php endif; ?>
                    </div>

                    <?php if($pkg['description']): ?>
                    <p class="package-description"><?= sanitize($pkg['description']) ?></p>
                    <?php endif; ?>

                    <?php if($features): ?>
                    <ul class="package-features">
                        <?php foreach($features as $f): ?>
                        <li class="<?= $f['is_included'] ? 'included' : 'excluded' ?>">
                            <i class="fas <?= $f['is_included'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                            <?= sanitize($f['feature_text']) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <div class="package-action">
                        <button type="button" class="btn-package <?= $isPopular ? 'btn-package-primary' : '' ?>" style="--pkg-color:<?= sanitize($color) ?>;" onclick="openPackageModal(<?= $pkg['id'] ?>, <?= htmlspecialchars(json_encode($pkg['name']), ENT_QUOTES) ?>)">
                            <i class="fas fa-paper-plane"></i> Demander ce package
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- How it works -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Comment ça marche</span>
            <h2>En 3 étapes simples</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-animate="fade-up" data-delay="0">
                <div style="text-align:center; padding:30px;">
                    <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--secondary)); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; position:relative;">
                        <i class="fas fa-hand-pointer" style="color:#fff; font-size:1.8rem;"></i>
                        <span style="position:absolute; top:-5px; right:-5px; width:30px; height:30px; border-radius:50%; background:var(--neon); color:var(--primary); font-weight:800; display:flex; align-items:center; justify-content:center; font-size:0.9rem;">1</span>
                    </div>
                    <h4 style="color:var(--primary); margin-bottom:10px;">Choisissez votre package</h4>
                    <p style="color:var(--gray-600);">Sélectionnez la formule qui correspond le mieux à votre activité et à vos besoins</p>
                </div>
            </div>
            <div class="col-md-4" data-animate="fade-up" data-delay="100">
                <div style="text-align:center; padding:30px;">
                    <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg,var(--secondary),var(--neon)); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; position:relative;">
                        <i class="fas fa-headset" style="color:#fff; font-size:1.8rem;"></i>
                        <span style="position:absolute; top:-5px; right:-5px; width:30px; height:30px; border-radius:50%; background:var(--gold); color:var(--primary); font-weight:800; display:flex; align-items:center; justify-content:center; font-size:0.9rem;">2</span>
                    </div>
                    <h4 style="color:var(--primary); margin-bottom:10px;">Confirmation & activation</h4>
                    <p style="color:var(--gray-600);">Notre équipe vous contacte sous 24h pour finaliser et activer votre package</p>
                </div>
            </div>
            <div class="col-md-4" data-animate="fade-up" data-delay="200">
                <div style="text-align:center; padding:30px;">
                    <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg,var(--neon),#00d4aa); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; position:relative;">
                        <i class="fas fa-truck" style="color:#fff; font-size:1.8rem;"></i>
                        <span style="position:absolute; top:-5px; right:-5px; width:30px; height:30px; border-radius:50%; background:var(--purple); color:#fff; font-weight:800; display:flex; align-items:center; justify-content:center; font-size:0.9rem;">3</span>
                    </div>
                    <h4 style="color:var(--primary); margin-bottom:10px;">Livraison & suivi</h4>
                    <p style="color:var(--gray-600);">Recevez vos produits et bénéficiez d'un suivi personnalisé tout au long de votre abonnement</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content animate-scale">
            <h2>Besoin d'un package personnalisé ?</h2>
            <p>Nous créons des solutions sur mesure pour les entreprises ayant des besoins spécifiques</p>
            <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
                <a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" class="btn btn-whatsapp" style="font-size:1.1rem; padding:15px 40px;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="contact.php" class="btn btn-outline" style="font-size:1.1rem; padding:15px 40px; color:var(--white); border-color:var(--white);">
                    <i class="fas fa-envelope"></i> Contact
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Package Request Modal -->
<div class="modal fade" id="packageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:none; border-radius:20px; overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--primary),#0a1628); border:none; padding:25px 30px;">
                <div>
                    <h5 class="modal-title" style="color:#fff; font-weight:700;">Demander un Package</h5>
                    <p style="color:rgba(255,255,255,0.6); margin:5px 0 0; font-size:0.9rem;" id="modalPackageName"></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body" style="padding:25px 30px;">
                    <input type="hidden" name="request_package" value="1">
                    <input type="hidden" name="package_id" id="modalPackageId">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nom complet *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="client_name" class="form-control" required placeholder="Votre nom" maxlength="150">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Téléphone *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="client_phone" class="form-control" required placeholder="+212..." pattern="[+0-9]{10,15}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="client_email" class="form-control" placeholder="email@exemple.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Entreprise</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" name="client_company" class="form-control" placeholder="Nom de l'entreprise">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ville</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" name="client_city" class="form-control" placeholder="Votre ville">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border:none; padding:15px 30px 25px;">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal" style="border-color:var(--gray-300); color:var(--gray-600);">Annuler</button>
                    <button type="submit" class="btn" style="background:linear-gradient(135deg,var(--primary),var(--secondary)); color:#fff; padding:12px 30px; border-radius:12px; font-weight:600;">
                        <i class="fas fa-paper-plane"></i> Envoyer ma demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPackageModal(id, name) {
    document.getElementById('modalPackageId').value = id;
    document.getElementById('modalPackageName').textContent = name;
    new bootstrap.Modal(document.getElementById('packageModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
