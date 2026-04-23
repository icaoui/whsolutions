<?php
$pageTitle = 'À Propos - WH Solutions';
require_once 'config/config.php';
require_once 'includes/functions.php';
logVisitor($pdo, 'about');
require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>À Propos de WH Solutions</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Accueil</a>
            <span>/</span>
            <span>À Propos</span>
        </div>
    </div>
</section>

<!-- About -->
<section class="section about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-image animate-on-scroll">
                <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="WH Solutions" class="main-img" style="max-width:100%; padding:60px; background:var(--white); border-radius:var(--radius);">
                <div class="experience-badge">
                    <span class="number">15+</span>
                    <span class="text">Ans d'Expérience</span>
                </div>
            </div>
            <div class="about-content animate-on-scroll delay-1">
                <span class="section-tag">Qui sommes-nous</span>
                <h2>Expert en Hygiène Professionnelle au Maroc</h2>
                <p><strong>WH Solutions</strong> est une entreprise marocaine spécialisée dans la fourniture de solutions d'hygiène professionnelle pour les secteurs agroalimentaire, industriel et environnemental.</p>
                <p>Nous proposons une gamme complète de produits de nettoyage, désinfection, traitement des eaux et équipements d'hygiène, répondant aux normes HACCP les plus exigeantes.</p>
                <ul class="about-list">
                    <li><i class="fas fa-check-circle"></i> Produits certifiés conformes aux normes HACCP</li>
                    <li><i class="fas fa-check-circle"></i> Expertise technique et accompagnement personnalisé</li>
                    <li><i class="fas fa-check-circle"></i> Large gamme couvrant tous les besoins d'hygiène</li>
                    <li><i class="fas fa-check-circle"></i> Livraison sur tout le territoire marocain</li>
                    <li><i class="fas fa-check-circle"></i> Support réactif et service après-vente</li>
                </ul>
                <a href="<?= WHATSAPP_LINK ?>" target="_blank" class="btn btn-whatsapp"><i class="fab fa-whatsapp"></i> Nous Contacter</a>
            </div>
        </div>
    </div>
</section>

<!-- Why Us -->
<section class="section" style="background:var(--white);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Nos Avantages</span>
            <h2>Pourquoi Choisir WH Solutions ?</h2>
        </div>
        <div class="why-grid">
            <div class="why-item animate-on-scroll">
                <div class="why-icon"><i class="fas fa-award"></i></div>
                <div>
                    <h4>Qualité Premium</h4>
                    <p>Des produits rigoureusement sélectionnés et testés pour garantir une efficacité maximale.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll delay-1">
                <div class="why-icon"><i class="fas fa-users"></i></div>
                <div>
                    <h4>Équipe Experte</h4>
                    <p>Des professionnels qualifiés à votre service pour vous conseiller dans le choix de vos produits.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll delay-2">
                <div class="why-icon"><i class="fas fa-truck"></i></div>
                <div>
                    <h4>Livraison Nationale</h4>
                    <p>Service de livraison fiable et rapide sur tout le Maroc.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll delay-3">
                <div class="why-icon"><i class="fas fa-tags"></i></div>
                <div>
                    <h4>Prix Compétitifs</h4>
                    <p>Les meilleurs tarifs du marché avec un excellent rapport qualité-prix.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll">
                <div class="why-icon"><i class="fas fa-recycle"></i></div>
                <div>
                    <h4>Éco-Responsable</h4>
                    <p>Des solutions respectueuses de l'environnement et conformes aux réglementations en vigueur.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll delay-1">
                <div class="why-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <h4>Disponibilité</h4>
                    <p>Stock permanent et réapprovisionnement rapide pour répondre à tous vos besoins.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content animate-on-scroll">
            <h2>Prêt à collaborer avec nous ?</h2>
            <p>Contactez-nous dès maintenant pour discuter de vos besoins en hygiène professionnelle</p>
            <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
                <a href="<?= WHATSAPP_LINK ?>" target="_blank" class="btn btn-whatsapp" style="font-size:1.1rem; padding:15px 40px;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="contact.php" class="btn btn-outline" style="font-size:1.1rem; padding:15px 40px; color:var(--white); border-color:var(--white);">
                    <i class="fas fa-envelope"></i> Formulaire de Contact
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>