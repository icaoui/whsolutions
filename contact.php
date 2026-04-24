<?php
$pageTitle = 'Contactez WH Solutions - Hygiène Professionnelle Maroc';
$pageDescription = 'Contactez WH Solutions pour vos besoins en hygiène professionnelle au Maroc. WhatsApp, téléphone, email. Kenitra, Maroc.';
$pageKeywords = 'contact WH Solutions, hygiène professionnelle Kenitra, commander produits nettoyage Maroc';
require_once 'config/config.php';
require_once 'includes/functions.php';
logVisitor($pdo, 'contact');

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez entrer un email valide.';
    } else {
        saveMessage($pdo, compact('name', 'email', 'phone', 'subject', 'message'));
        $success = 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.';
    }
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Contactez-Nous</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Accueil</a>
            <span>/</span>
            <span>Contact</span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if($success): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div><?= $success ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php endif; ?>
        <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2 fs-5"></i>
            <div><?= $error ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php endif; ?>

        <div class="contact-grid">
            <!-- Form -->
            <div class="animate-left">
                <h2 style="font-size:1.8rem; color:var(--primary); margin-bottom:10px;">Envoyez-nous un message</h2>
                <p style="color:var(--gray-600); margin-bottom:30px;">Remplissez le formulaire ci-dessous et nous vous répondrons rapidement.</p>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom Complet *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="name" class="form-control" required placeholder="Votre nom">
                                <div class="invalid-feedback">Veuillez entrer votre nom.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control" required placeholder="Votre email">
                                <div class="invalid-feedback">Veuillez entrer un email valide.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-phone text-muted"></i></span>
                                <input type="tel" name="phone" class="form-control" placeholder="Votre téléphone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sujet</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-tag text-muted"></i></span>
                                <input type="text" name="subject" class="form-control" placeholder="Sujet du message">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message *</label>
                        <textarea name="message" class="form-control" rows="5" required placeholder="Décrivez votre besoin..."></textarea>
                        <div class="invalid-feedback">Veuillez entrer votre message.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" style="background:linear-gradient(135deg, var(--primary), var(--secondary)); border:none; border-radius:12px; font-size:1rem;">
                        <i class="fas fa-paper-plane me-2"></i> Envoyer le Message
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="animate-right">
                <h2 style="font-size:1.8rem; color:var(--primary); margin-bottom:10px;">Nos Coordonnées</h2>
                <p style="color:var(--gray-600); margin-bottom:30px;">N'hésitez pas à nous contacter par le moyen qui vous convient le mieux.</p>
                <div class="contact-info-cards">
                    <div class="contact-info-card">
                        <div class="info-icon"><i class="fab fa-whatsapp"></i></div>
                        <div>
                            <h4>WhatsApp</h4>
                            <p><a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" style="color:var(--secondary);"><?= SITE_PHONE ?></a></p>
                            <p style="font-size:0.8rem;">Commande rapide & Support</p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h4>Email</h4>
                            <p><a href="mailto:<?= SITE_EMAIL ?>" style="color:var(--secondary);"><?= SITE_EMAIL ?></a></p>
                            <p><a href="mailto:<?= SITE_EMAIL2 ?>" style="color:var(--secondary);"><?= SITE_EMAIL2 ?></a></p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="info-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <h4>Téléphone</h4>
                            <p><a href="tel:+212652020702" style="color:var(--secondary);"><?= SITE_PHONE ?></a></p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h4>Adresse</h4>
                            <p><?= SITE_ADDRESS ?></p>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp CTA -->
                <div style="margin-top:30px; padding:25px; background:linear-gradient(135deg, #25D366, #128C7E); border-radius:var(--radius); text-align:center;">
                    <h3 style="color:var(--white); margin-bottom:10px;"><i class="fab fa-whatsapp"></i> Commander par WhatsApp</h3>
                    <p style="color:rgba(255,255,255,0.9); font-size:0.9rem; margin-bottom:15px;">Le moyen le plus rapide pour passer commande</p>
                    <a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" class="btn" style="background:var(--white); color:#25D366; font-weight:700; padding:12px 30px;">
                        Ouvrir WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bootstrap form validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.needs-validation').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
            form.classList.add('was-validated');
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>