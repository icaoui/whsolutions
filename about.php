<?php
$pageTitle = 'À Propos de WH Solutions - Expert Hygiène Professionnelle Maroc';
$pageDescription = 'Découvrez WH Solutions, entreprise marocaine spécialisée en hygiène professionnelle depuis 15 ans. Kenitra, Maroc. Normes HACCP, solutions éco-responsables.';
$pageKeywords = 'WH Solutions Maroc, entreprise hygiène Kenitra, expert nettoyage professionnel Maroc, HACCP Maroc';
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
        <div class="row align-items-center g-5">
            <div class="col-lg-5 animate-left">
                <div class="about-image">
                    <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="WH Solutions" class="main-img" style="max-width:100%; padding:60px; background:var(--white); border-radius:var(--radius);">
                    <div class="experience-badge">
                        <span class="number">15+</span>
                        <span class="text">Ans d'Expérience</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 animate-right">
                <div class="about-content">
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
                    <a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" class="btn btn-whatsapp"><i class="fab fa-whatsapp"></i> Nous Contacter</a>
                </div>
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
        <div class="row g-4" data-stagger>
            <div class="col-lg-4 col-md-6">
                <div class="why-item h-100">
                    <div class="why-icon"><i class="fas fa-award"></i></div>
                    <div>
                        <h4>Qualité Premium</h4>
                        <p>Des produits rigoureusement sélectionnés et testés pour garantir une efficacité maximale.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="why-item h-100">
                    <div class="why-icon"><i class="fas fa-users"></i></div>
                    <div>
                        <h4>Équipe Experte</h4>
                        <p>Des professionnels qualifiés à votre service pour vous conseiller dans le choix de vos produits.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="why-item h-100">
                    <div class="why-icon"><i class="fas fa-truck"></i></div>
                    <div>
                        <h4>Livraison Nationale</h4>
                        <p>Service de livraison fiable et rapide sur tout le Maroc.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="why-item h-100">
                    <div class="why-icon"><i class="fas fa-tags"></i></div>
                    <div>
                        <h4>Prix Compétitifs</h4>
                        <p>Les meilleurs tarifs du marché avec un excellent rapport qualité-prix.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="why-item h-100">
                    <div class="why-icon"><i class="fas fa-recycle"></i></div>
                    <div>
                        <h4>Éco-Responsable</h4>
                        <p>Des solutions respectueuses de l'environnement et conformes aux réglementations en vigueur.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="why-item h-100">
                    <div class="why-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <h4>Disponibilité</h4>
                        <p>Stock permanent et réapprovisionnement rapide pour répondre à tous vos besoins.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section" style="background:var(--gray-100);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">FAQ</span>
            <h2>Questions Fréquentes</h2>
            <p>Retrouvez les réponses aux questions les plus posées par nos clients</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3" style="border-radius:var(--radius) !important; overflow:hidden; box-shadow:var(--shadow);">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" style="font-family:var(--font); color:var(--primary);">
                                <i class="fas fa-truck me-3" style="color:var(--secondary);"></i> Quels sont vos délais de livraison ?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="color:var(--gray-600);">
                                Nous livrons sur tout le territoire marocain. Les délais varient de 24h à 72h selon votre localisation. Les commandes passées avant 14h à Kenitra sont livrées le jour même. Pour les grandes quantités, nous organisons des livraisons sur mesure.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-3" style="border-radius:var(--radius) !important; overflow:hidden; box-shadow:var(--shadow);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" style="font-family:var(--font); color:var(--primary);">
                                <i class="fas fa-shopping-cart me-3" style="color:var(--secondary);"></i> Comment passer une commande ?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="color:var(--gray-600);">
                                Le moyen le plus rapide est via <strong>WhatsApp</strong>. Parcourez notre catalogue, choisissez vos produits et envoyez-nous votre commande directement. Vous pouvez aussi nous contacter par téléphone ou email. Nous confirmons votre commande sous 1h pendant les heures ouvrables.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-3" style="border-radius:var(--radius) !important; overflow:hidden; box-shadow:var(--shadow);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" style="font-family:var(--font); color:var(--primary);">
                                <i class="fas fa-certificate me-3" style="color:var(--secondary);"></i> Vos produits sont-ils certifiés HACCP ?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="color:var(--gray-600);">
                                Oui, l'ensemble de nos produits sont conformes aux normes HACCP et aux réglementations en vigueur au Maroc et à l'international. Nous fournissons les fiches techniques et certificats de conformité sur demande.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-3" style="border-radius:var(--radius) !important; overflow:hidden; box-shadow:var(--shadow);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" style="font-family:var(--font); color:var(--primary);">
                                <i class="fas fa-box-open me-3" style="color:var(--secondary);"></i> Proposez-vous des échantillons ?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="color:var(--gray-600);">
                                Oui, pour les professionnels et les commandes importantes, nous pouvons fournir des échantillons gratuits de nos produits. Contactez-nous via WhatsApp ou email en précisant les produits souhaités et votre secteur d'activité.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-3" style="border-radius:var(--radius) !important; overflow:hidden; box-shadow:var(--shadow);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" style="font-family:var(--font); color:var(--primary);">
                                <i class="fas fa-credit-card me-3" style="color:var(--secondary);"></i> Quels sont vos modes de paiement ?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="color:var(--gray-600);">
                                Nous acceptons le paiement à la livraison, le virement bancaire et le chèque. Pour les clients réguliers, nous proposons des facilités de paiement adaptées. Les conditions sont discutées lors de votre première commande.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content animate-scale">
            <h2>Prêt à collaborer avec nous ?</h2>
            <p>Contactez-nous dès maintenant pour discuter de vos besoins en hygiène professionnelle</p>
            <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
                <a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" class="btn btn-whatsapp" style="font-size:1.1rem; padding:15px 40px;">
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