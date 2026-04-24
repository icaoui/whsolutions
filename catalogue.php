<?php
$pageTitle = 'Catalogue - WH Solutions';
$pageDescription = 'Consultez et téléchargez le catalogue complet WH Solutions. Découvrez notre gamme de produits d\'hygiène professionnelle au Maroc.';
$pageKeywords = 'catalogue WH Solutions, catalogue hygiène professionnelle, catalogue produits nettoyage Maroc, télécharger catalogue';
require_once 'config/config.php';
require_once 'includes/functions.php';
logVisitor($pdo, 'catalogue');
$categories = getCategories($pdo);
$pdfFile = SITE_URL . '/uploads/WH_Catalogue.pdf';
$waLink = getWhatsAppCatalogueLink();
require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Notre Catalogue</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Accueil</a>
            <span>/</span>
            <span>Catalogue</span>
        </div>
    </div>
</section>

<!-- Catalogue Viewer Section -->
<section class="section catalogue-section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Catalogue <?= date('Y') ?></span>
            <h2>Feuilleter Notre Catalogue</h2>
            <p>Parcourez notre catalogue complet directement depuis votre navigateur</p>
        </div>

        <!-- PDF Toolbar -->
        <div class="pdf-toolbar animate-on-scroll">
            <div class="pdf-toolbar-left">
                <button id="pdfPrev" class="pdf-tool-btn" title="Page précédente"><i class="fas fa-chevron-left"></i></button>
                <span class="pdf-page-info">Page <span id="pdfPageNum">1</span> / <span id="pdfPageCount">-</span></span>
                <button id="pdfNext" class="pdf-tool-btn" title="Page suivante"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="pdf-toolbar-right">
                <button id="pdfZoomOut" class="pdf-tool-btn" title="Zoom -"><i class="fas fa-search-minus"></i></button>
                <span id="pdfZoomLevel" class="pdf-zoom-level">100%</span>
                <button id="pdfZoomIn" class="pdf-tool-btn" title="Zoom +"><i class="fas fa-search-plus"></i></button>
                <button id="pdfFullscreen" class="pdf-tool-btn" title="Plein écran"><i class="fas fa-expand"></i></button>
                <a href="<?= $pdfFile ?>" download="WH_Solutions_Catalogue.pdf" class="pdf-tool-btn pdf-download-btn" title="Télécharger"><i class="fas fa-download"></i> <span>Télécharger</span></a>
            </div>
        </div>

        <!-- PDF Canvas Container -->
        <div class="pdf-viewer-container animate-on-scroll" id="pdfViewerContainer">
            <div class="pdf-loading" id="pdfLoading">
                <div class="pdf-loading-spinner"></div>
                <p>Chargement du catalogue...</p>
            </div>
            <canvas id="pdfCanvas"></canvas>
        </div>

        <!-- Mobile Fallback + Download Buttons -->
        <div class="catalogue-actions-bottom animate-on-scroll">
            <a href="<?= $pdfFile ?>" download="WH_Solutions_Catalogue.pdf" class="btn btn-primary">
                <i class="fas fa-download"></i> Télécharger le Catalogue PDF
            </a>
            <a href="<?= $waLink ?>" target="_blank" class="btn btn-whatsapp">
                <i class="fab fa-whatsapp"></i> Commander via WhatsApp
            </a>
        </div>

        <!-- Quick Category Access -->
        <div style="margin-top:80px;">
            <div class="section-header">
                <span class="section-tag">Accès rapide</span>
                <h2>Explorer par Catégorie</h2>
            </div>
            <div class="categories-grid" data-stagger>
                <?php foreach($categories as $cat): ?>
                <a href="products.php?category=<?= $cat['slug'] ?>" class="category-card">
                    <div class="cat-icon"><i class="<?= $cat['icon'] ?>"></i></div>
                    <h3><?= sanitize($cat['name']) ?></h3>
                    <p><?= sanitize(mb_strimwidth($cat['description'], 0, 80, '...')) ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content animate-scale">
            <h2>Besoin d'informations supplémentaires ?</h2>
            <p>Notre équipe est à votre disposition pour vous conseiller sur le choix de vos produits</p>
            <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
                <a href="<?= $waLink ?>" target="_blank" class="btn btn-whatsapp" style="font-size:1.1rem; padding:18px 45px;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="contact.php" class="btn btn-outline" style="font-size:1.1rem; padding:18px 45px; color:var(--white); border-color:var(--white);">
                    <i class="fas fa-envelope"></i> Contact
                </a>
            </div>
        </div>
    </div>
</section>

<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const url = '<?= $pdfFile ?>';
    const canvas = document.getElementById('pdfCanvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('pdfViewerContainer');
    const loading = document.getElementById('pdfLoading');

    let pdfDoc = null, pageNum = 1, scale = 1.5, rendering = false;

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    function renderPage(num) {
        rendering = true;
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({ scale: scale });
            const ratio = window.devicePixelRatio || 1;
            canvas.width = viewport.width * ratio;
            canvas.height = viewport.height * ratio;
            canvas.style.width = viewport.width + 'px';
            canvas.style.height = viewport.height + 'px';
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

            page.render({ canvasContext: ctx, viewport: viewport }).promise.then(function() {
                rendering = false;
            });
        });
        document.getElementById('pdfPageNum').textContent = num;
        document.getElementById('pdfZoomLevel').textContent = Math.round(scale / 1.5 * 100) + '%';
    }

    pdfjsLib.getDocument(url).promise.then(function(pdf) {
        pdfDoc = pdf;
        document.getElementById('pdfPageCount').textContent = pdf.numPages;
        loading.style.display = 'none';
        canvas.style.display = 'block';

        // Fit width on load
        pdf.getPage(1).then(function(page) {
            const containerWidth = container.clientWidth - 40;
            const viewport = page.getViewport({ scale: 1 });
            scale = containerWidth / viewport.width;
            renderPage(1);
        });
    }).catch(function(err) {
        loading.innerHTML = '<p style="color:var(--danger);"><i class="fas fa-exclamation-triangle"></i> Impossible de charger le catalogue. <a href="' + url + '" download>Téléchargez-le ici</a>.</p>';
    });

    document.getElementById('pdfPrev').addEventListener('click', function() {
        if (pageNum <= 1) return;
        pageNum--;
        renderPage(pageNum);
    });
    document.getElementById('pdfNext').addEventListener('click', function() {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        renderPage(pageNum);
    });
    document.getElementById('pdfZoomIn').addEventListener('click', function() {
        scale *= 1.2;
        renderPage(pageNum);
    });
    document.getElementById('pdfZoomOut').addEventListener('click', function() {
        scale /= 1.2;
        if (scale < 0.5) scale = 0.5;
        renderPage(pageNum);
    });
    document.getElementById('pdfFullscreen').addEventListener('click', function() {
        if (container.requestFullscreen) container.requestFullscreen();
        else if (container.webkitRequestFullscreen) container.webkitRequestFullscreen();
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') { document.getElementById('pdfPrev').click(); }
        if (e.key === 'ArrowRight') { document.getElementById('pdfNext').click(); }
    });

    // Touch swipe for mobile
    let touchStartX = 0;
    container.addEventListener('touchstart', function(e) { touchStartX = e.touches[0].clientX; });
    container.addEventListener('touchend', function(e) {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) document.getElementById('pdfNext').click();
            else document.getElementById('pdfPrev').click();
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
