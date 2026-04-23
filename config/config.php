<?php
// WH Solutions - Configuration
define('SITE_NAME', 'WH Solutions');
define('SITE_TAGLINE', 'Expert en Hygiène Professionnelle');
define('SITE_URL', 'http://localhost/whsolutions');
define('SITE_EMAIL', 'info.whsolution@gmail.com');
define('SITE_EMAIL2', 'contact.whsolution@gmail.com');
define('SITE_PHONE', '+212 (0) 6 52 020702');
define('SITE_ADDRESS', 'N°31, Rue Martir ABD Eslam Ben Mohammed - Residence Riad Zayton, Bureau N°2 Val fleury - Kenitra Maroc');
define('WHATSAPP_NUMBER', '212652020702');
define('WHATSAPP_LINK', 'https://wa.link/t3eheg');
define('CATALOGUE_PDF', 'https://drive.google.com/file/d/149LV19xcNzt5apoNIbIQvo7vI5KIB4v6/view');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'whsolutions');
define('DB_USER', 'root');
define('DB_PASS', '');

// Admin
define('ADMIN_PATH', '/admin');
define('UPLOADS_PATH', __DIR__ . '/../uploads/');
define('PRODUCTS_UPLOAD_PATH', __DIR__ . '/../uploads/products/');

// Pagination
define('PRODUCTS_PER_PAGE', 12);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
