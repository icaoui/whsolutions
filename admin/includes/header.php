<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$adminPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $adminTitle ?? 'Administration' ?> - WH Solutions Admin</title>
<link rel="icon" href="<?= SITE_URL ?>/assets/images/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="WH Solutions" class="sidebar-logo">
            <span class="sidebar-title">WH Admin</span>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item <?= $adminPage === 'index' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
            <a href="products.php" class="nav-item <?= $adminPage === 'products' ? 'active' : '' ?>"><i class="fas fa-box-open"></i><span>Produits</span></a>
            <a href="categories.php" class="nav-item <?= $adminPage === 'categories' ? 'active' : '' ?>"><i class="fas fa-th-large"></i><span>Catégories</span></a>
            <a href="messages.php" class="nav-item <?= $adminPage === 'messages' ? 'active' : '' ?>"><i class="fas fa-envelope"></i><span>Messages</span></a>
            <a href="inquiries.php" class="nav-item <?= $adminPage === 'inquiries' ? 'active' : '' ?>"><i class="fas fa-question-circle"></i><span>Demandes</span></a>
            <a href="visitors.php" class="nav-item <?= $adminPage === 'visitors' ? 'active' : '' ?>"><i class="fas fa-users"></i><span>Visiteurs</span></a>
            <div class="nav-divider"></div>
            <a href="<?= SITE_URL ?>/" target="_blank" class="nav-item"><i class="fas fa-globe"></i><span>Voir le Site</span></a>
            <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
        </nav>
    </aside>
    <!-- Main -->
    <main class="admin-main">
        <header class="admin-header">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <h1 class="page-title"><?= $adminTitle ?? 'Dashboard' ?></h1>
            <div class="admin-user">
                <span>Admin</span>
                <i class="fas fa-user-circle"></i>
            </div>
        </header>
        <div class="admin-content">