<?php
// WH Solutions - Helper Functions
require_once __DIR__ . '/../config/database.php';

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function sanitizeHtml($html) {
    $allowed = '<b><i><u><s><strong><em><br><p><ul><ol><li><h1><h2><h3><h4><blockquote><pre><code><hr><span><div><table><thead><tbody><tr><th><td><a><sub><sup>';
    $html = strip_tags(trim($html), $allowed);
    // Remove event handlers and javascript: from attributes
    $html = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
    $html = preg_replace('/\bon\w+\s*=\s*\S+/i', '', $html);
    $html = preg_replace('/href\s*=\s*["\']?\s*javascript:/i', 'href="', $html);
    return $html;
}

function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

function logAdminActivity($pdo, $adminId, $action, $details = '') {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([intval($adminId), $action, $details, $ip]);
    } catch (Exception $e) {
        // Table may not exist yet - silently fail
    }
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

function getCategories($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM categories";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY sort_order ASC";
    return $pdo->query($sql)->fetchAll();
}

function getCategoryBySlug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getProducts($pdo, $categoryId = null, $page = 1, $limit = 12, $search = null) {
    $limit = intval($limit);
    $offset = (intval($page) - 1) * $limit;
    $params = [];
    $where = ["p.is_active = 1"];
    if ($categoryId) {
        $where[] = "p.category_id = ?";
        $params[] = $categoryId;
    }
    if ($search) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $whereStr = implode(' AND ', $where);
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p LEFT JOIN categories c ON p.category_id = c.id 
            WHERE $whereStr ORDER BY p.sort_order ASC, p.created_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function countProducts($pdo, $categoryId = null, $search = null) {
    $params = [];
    $where = ["is_active = 1"];
    if ($categoryId) { $where[] = "category_id = ?"; $params[] = $categoryId; }
    if ($search) { $where[] = "(name LIKE ? OR description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $whereStr = implode(' AND ', $where);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $whereStr");
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getProductBySlug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getProductById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getFeaturedProducts($pdo, $limit = 6) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.sort_order ASC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getRelatedProducts($pdo, $productId, $categoryId, $limit = 4) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 ORDER BY RAND() LIMIT ?");
    $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(2, $productId, PDO::PARAM_INT);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function incrementProductViews($pdo, $id) {
    $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$id]);
}

function saveMessage($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['subject'], $data['message']]);
}

function saveInquiry($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO inquiries (product_id, customer_name, customer_phone, customer_email, quantity, message) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$data['product_id'], $data['name'], $data['phone'], $data['email'], $data['quantity'], $data['message']]);
}

function logVisitor($pdo, $page) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO visitors (ip_address, page_visited, user_agent) VALUES (?, ?, ?)");
    $stmt->execute([$ip, $page, $ua]);
}

function getWhatsAppOrderLink($product, $quantity = 1) {
    global $pdo;
    $phone = getSetting($pdo, 'whatsapp_number', WHATSAPP_NUMBER);
    $tpl = getSetting($pdo, 'whatsapp_order_message', '');
    if (!empty($tpl)) {
        $msg = str_replace(
            ['{product}', '{quantity}'],
            [$product['name'], $quantity],
            $tpl
        );
    } else {
        $msg = "Bonjour, je suis intéressé par le produit : *{$product['name']}*";
        if ($quantity > 1) $msg .= " (Quantité: $quantity)";
        $msg .= "\nMerci de me contacter pour plus d'informations.";
    }
    return "https://wa.me/$phone?text=" . urlencode($msg);
}

function getWhatsAppGeneralLink() {
    global $pdo;
    $phone = getSetting($pdo, 'whatsapp_number', WHATSAPP_NUMBER);
    $msg = getSetting($pdo, 'whatsapp_welcome_message', 'Bonjour, je souhaite avoir des informations sur vos produits d\'hygiène professionnelle.');
    return "https://wa.me/$phone?text=" . urlencode($msg);
}

function getWhatsAppCatalogueLink() {
    global $pdo;
    $phone = getSetting($pdo, 'whatsapp_number', WHATSAPP_NUMBER);
    $msg = getSetting($pdo, 'whatsapp_catalogue_message', 'Bonjour, j\'ai consulté votre catalogue et je souhaite avoir plus d\'informations sur vos produits.');
    return "https://wa.me/$phone?text=" . urlencode($msg);
}

function getSetting($pdo, $key, $default = '') {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    $cache[$key] = ($val !== false) ? $val : $default;
    return $cache[$key];
}

function setSetting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP");
    return $stmt->execute([$key, $value, $value]);
}

function getAllSettings($pdo) {
    $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
    $settings = [];
    foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];
    return $settings;
}