<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$type = $_GET['type'] ?? '';
$allowed = ['products', 'messages', 'inquiries'];

if (!in_array($type, $allowed)) {
    header('Location: settings.php');
    exit;
}

$filename = 'whsolutions_' . $type . '_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

switch ($type) {
    case 'products':
        fputcsv($output, ['ID', 'Nom', 'Catégorie', 'Référence', 'Description', 'En Vedette', 'Actif', 'Vues', 'Créé le']);
        $rows = $pdo->query("SELECT p.id, p.name, c.name as category, p.reference, p.short_description, p.is_featured, p.is_active, p.views, p.created_at FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id")->fetchAll();
        foreach ($rows as $r) {
            fputcsv($output, [$r['id'], $r['name'], $r['category'], $r['reference'], $r['short_description'], $r['is_featured'] ? 'Oui' : 'Non', $r['is_active'] ? 'Oui' : 'Non', $r['views'], $r['created_at']]);
        }
        break;

    case 'messages':
        fputcsv($output, ['ID', 'Nom', 'Email', 'Téléphone', 'Sujet', 'Message', 'Lu', 'Date']);
        $rows = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
        foreach ($rows as $r) {
            fputcsv($output, [$r['id'], $r['name'], $r['email'], $r['phone'] ?? '', $r['subject'] ?? '', $r['message'], $r['is_read'] ? 'Oui' : 'Non', $r['created_at']]);
        }
        break;

    case 'inquiries':
        fputcsv($output, ['ID', 'Client', 'Téléphone', 'Email', 'Produit', 'Quantité', 'Message', 'Statut', 'Date']);
        $rows = $pdo->query("SELECT i.*, p.name as product_name FROM inquiries i LEFT JOIN products p ON i.product_id = p.id ORDER BY i.created_at DESC")->fetchAll();
        foreach ($rows as $r) {
            fputcsv($output, [$r['id'], $r['customer_name'], $r['customer_phone'], $r['customer_email'] ?? '', $r['product_name'] ?? 'N/A', $r['quantity'], $r['message'] ?? '', $r['status'], $r['created_at']]);
        }
        break;
}

fclose($output);
exit;
