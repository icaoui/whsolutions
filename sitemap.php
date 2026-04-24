<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= SITE_URL ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= SITE_URL ?>/products.php</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= SITE_URL ?>/about.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?= SITE_URL ?>/contact.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?= SITE_URL ?>/catalogue.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= SITE_URL ?>/packages.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php foreach(getCategories($pdo) as $cat): ?>
    <url>
        <loc><?= SITE_URL ?>/products.php?category=<?= $cat['slug'] ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
    <?php
    $allProducts = $pdo->query("SELECT slug, updated_at FROM products WHERE is_active = 1 ORDER BY updated_at DESC")->fetchAll();
    foreach($allProducts as $p):
    ?>
    <url>
        <loc><?= SITE_URL ?>/product.php?slug=<?= $p['slug'] ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($p['updated_at'])) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>
</urlset>
