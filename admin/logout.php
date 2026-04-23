<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
if (isset($_SESSION['admin_id'])) {
    logAdminActivity($pdo, $_SESSION['admin_id'], 'logout', 'Déconnexion');
}
session_destroy();
header('Location: login.php');
exit;