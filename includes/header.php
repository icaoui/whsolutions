<?php if(!defined('SITE_NAME')) { require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/functions.php'; } 
$categories = getCategories($pdo);
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? SITE_NAME . ' - ' . SITE_TAGLINE ?></title>
<meta name="description" content="<?= $pageDescription ?? 'WH Solutions - Expert en Hygiène Professionnelle. Produits de nettoyage, désinfection et hygiène industrielle au Maroc.' ?>">
<link rel="icon" href="<?= SITE_URL ?>/assets/images/logo.png" type="image/png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<!-- Scroll Progress -->
<div class="scroll-progress"></div>

<!-- Custom Cursor -->
<div class="cursor-dot"></div>
<div class="cursor-ring"></div>

<!-- Preloader -->
<div id="preloader">
    <div class="loader">
        <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="<?= SITE_NAME ?>">
        <div class="loader-bar"><div class="loader-progress"></div></div>
        <div class="loader-text">Chargement...</div>
    </div>
</div>

<!-- WhatsApp Float Button -->
<a href="<?= WHATSAPP_LINK ?>" target="_blank" class="whatsapp-float" title="Commander via WhatsApp">
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
            <li><a href="<?= CATALOGUE_PDF ?>" target="_blank" class="<?= $currentPage === 'catalogue' ? 'active' : '' ?>">Catalogue</a></li>
            <li><a href="<?= SITE_URL ?>/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">À Propos</a></li>
            <li><a href="<?= SITE_URL ?>/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
            <li><a href="<?= WHATSAPP_LINK ?>" target="_blank" class="nav-cta"><i class="fab fa-whatsapp"></i> Commander</a></li>
        </ul>
    </div>
</nav>