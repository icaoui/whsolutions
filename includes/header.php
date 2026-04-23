<?php if(!defined('SITE_NAME')) { require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/functions.php'; } 
$categories = getCategories($pdo);
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<title><?= $pageTitle ?? SITE_NAME . ' - ' . SITE_TAGLINE ?></title>
<meta name="description" content="<?= $pageDescription ?? 'WH Solutions - Expert en Hygiène Professionnelle au Maroc. Produits de nettoyage, désinfection, traitement des eaux et hygiène industrielle à Kenitra et partout au Maroc.' ?>">
<meta name="keywords" content="<?= $pageKeywords ?? 'hygiène professionnelle Maroc, produits nettoyage Maroc, désinfection industrielle, HACCP Maroc, produits hygiène Kenitra, nettoyage agroalimentaire, traitement eaux Maroc, WH Solutions' ?>">
<meta name="author" content="WH Solutions">
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
<meta name="geo.region" content="MA">
<meta name="geo.placename" content="Kenitra, Maroc">
<meta name="geo.position" content="34.2610;-6.5802">
<meta name="ICBM" content="34.2610, -6.5802">
<link rel="canonical" href="<?= SITE_URL ?>/<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? '' : basename($_SERVER['PHP_SELF']) ?>">
<meta name="theme-color" content="#1B3A5C">
<meta name="msapplication-TileColor" content="#1B3A5C">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= $pageTitle ?? SITE_NAME . ' - ' . SITE_TAGLINE ?>">
<meta property="og:description" content="<?= $pageDescription ?? 'WH Solutions - Expert en Hygiène Professionnelle au Maroc. Produits de nettoyage, désinfection et hygiène industrielle.' ?>">
<meta property="og:url" content="<?= SITE_URL ?>/<?= basename($_SERVER['PHP_SELF']) ?>">
<meta property="og:image" content="<?= SITE_URL ?>/assets/images/logo.png">
<meta property="og:site_name" content="<?= SITE_NAME ?>">
<meta property="og:locale" content="fr_MA">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= $pageTitle ?? SITE_NAME ?>">
<meta name="twitter:description" content="<?= $pageDescription ?? 'Expert en Hygiène Professionnelle au Maroc' ?>">
<meta name="twitter:image" content="<?= SITE_URL ?>/assets/images/logo.png">

<!-- Structured Data - LocalBusiness -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "WH Solutions",
    "description": "Expert en Hygiène Professionnelle - Solutions de nettoyage, désinfection et traitement pour l'industrie agroalimentaire au Maroc",
    "url": "<?= SITE_URL ?>",
    "logo": "<?= SITE_URL ?>/assets/images/logo.png",
    "image": "<?= SITE_URL ?>/assets/images/logo.png",
    "telephone": "+212652020702",
    "email": "info.whsolution@gmail.com",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "N°31, Rue Martir ABD Eslam Ben Mohammed - Residence Riad Zayton, Bureau N°2 Val fleury",
        "addressLocality": "Kenitra",
        "addressCountry": "MA"
    },
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": "34.2610",
        "longitude": "-6.5802"
    },
    "areaServed": {
        "@type": "Country",
        "name": "Maroc"
    },
    "priceRange": "$$",
    "openingHours": "Mo-Sa 08:00-18:00",
    "sameAs": []
}
</script>

<link rel="icon" href="<?= SITE_URL ?>/assets/images/logo.png" type="image/png">
<link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>
</head>
<body>
<!-- Scroll Progress -->
<div class="scroll-progress"></div>

<!-- WhatsApp Float Button -->
<a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" class="whatsapp-float" title="Commander via WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <span>Commander</span>
</a>

<!-- Navigation -->
<nav class="navbar" id="navbar">
    <div class="container">
        <a href="<?= SITE_URL ?>/" class="navbar-brand">
            <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="<?= SITE_NAME ?>">
        </a>
        <button class="navbar-toggle" id="navToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="navbar-menu" id="navMenu">
            <li><a href="<?= SITE_URL ?>/" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Accueil</a></li>
            <li class="has-dropdown">
                <a href="<?= SITE_URL ?>/products.php" class="<?= $currentPage === 'products' ? 'active' : '' ?>">Nos Produits <i class="fas fa-chevron-down"></i></a>
                <ul class="dropdown">
                    <?php foreach($categories as $cat): ?>
                    <li><a href="<?= SITE_URL ?>/products.php?category=<?= $cat['slug'] ?>"><i class="<?= $cat['icon'] ?>"></i> <?= sanitize($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <li><a href="<?= SITE_URL ?>/catalogue.php" class="<?= $currentPage === 'catalogue' ? 'active' : '' ?>">Catalogue</a></li>
            <li><a href="<?= SITE_URL ?>/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">À Propos</a></li>
            <li><a href="<?= SITE_URL ?>/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
            <li><a href="<?= getWhatsAppGeneralLink() ?>" target="_blank" class="nav-cta"><i class="fab fa-whatsapp"></i> Commander</a></li>
        </ul>
    </div>
</nav>