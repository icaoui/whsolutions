<?php
/**
 * WH Solutions - Setup Script
 * Run once after importing database.sql to set up the super admin.
 * DELETE THIS FILE after setup is complete.
 */
require_once 'config/config.php';
require_once 'includes/functions.php';

$done = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? 'admin';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Update or create super admin
    $check = $pdo->prepare("SELECT id FROM admins WHERE username = 'admin'");
    $check->execute();
    if ($check->fetch()) {
        $pdo->prepare("UPDATE admins SET password = ?, role = 'super_admin', is_active = 1 WHERE username = 'admin'")->execute([$hash]);
    } else {
        $pdo->prepare("INSERT INTO admins (username, password, name, email, role) VALUES (?, ?, 'Super Administrateur', 'info.whsolution@gmail.com', 'super_admin')")->execute(['admin', $hash]);
    }

    // Add missing columns if upgrading
    try { $pdo->exec("ALTER TABLE admins ADD COLUMN role ENUM('super_admin','admin') DEFAULT 'admin' AFTER email"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE admins ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER role"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE admins ADD COLUMN last_login DATETIME DEFAULT NULL AFTER is_active"); } catch(Exception $e) {}

    // Add image column to categories if missing
    try { $pdo->exec("ALTER TABLE categories ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER icon"); } catch(Exception $e) {}

    // Create activity log table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT,
            action VARCHAR(100) NOT NULL,
            details TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
        ) ENGINE=InnoDB");
    } catch(Exception $e) {}

    // Create packages table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            slug VARCHAR(200) NOT NULL UNIQUE,
            subtitle VARCHAR(300),
            description TEXT,
            price DECIMAL(10,2) DEFAULT NULL,
            price_label VARCHAR(100) DEFAULT NULL,
            duration_months INT DEFAULT 12,
            icon VARCHAR(50) DEFAULT 'fas fa-box',
            color VARCHAR(30) DEFAULT '#4ECDC4',
            badge VARCHAR(100) DEFAULT NULL,
            is_popular TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");
    } catch(Exception $e) {}

    // Create package_features table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS package_features (
            id INT AUTO_INCREMENT PRIMARY KEY,
            package_id INT NOT NULL,
            feature_text VARCHAR(500) NOT NULL,
            is_included TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");
    } catch(Exception $e) {}

    // Create customer_packages table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS customer_packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(150) NOT NULL,
            customer_email VARCHAR(150),
            customer_phone VARCHAR(30),
            customer_company VARCHAR(200),
            customer_city VARCHAR(100),
            package_id INT,
            status ENUM('pending','active','expired','cancelled') DEFAULT 'pending',
            notes TEXT,
            activated_at DATETIME DEFAULT NULL,
            expires_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
        ) ENGINE=InnoDB");
    } catch(Exception $e) {}

    $done = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup - WH Solutions</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f2f5; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
.card { background:#fff; border-radius:16px; padding:40px; max-width:450px; width:100%; box-shadow:0 10px 40px rgba(0,0,0,0.1); text-align:center; }
h1 { color:#1B3A5C; margin-bottom:8px; }
p { color:#666; margin-bottom:25px; font-size:0.92rem; }
input { width:100%; padding:12px 16px; border:2px solid #e2e8f0; border-radius:10px; font-size:1rem; margin-bottom:15px; }
input:focus { border-color:#4ECDC4; outline:none; }
button { width:100%; padding:14px; background:linear-gradient(135deg,#4ECDC4,#00F5D4); border:none; border-radius:10px; color:#fff; font-size:1rem; font-weight:600; cursor:pointer; }
button:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(78,205,196,0.4); }
.success { background:#d4edda; color:#155724; padding:15px; border-radius:10px; margin-bottom:15px; }
.warn { background:#fff3cd; color:#856404; padding:12px; border-radius:10px; margin-top:15px; font-size:0.85rem; }
label { display:block; text-align:left; font-weight:600; color:#333; margin-bottom:6px; font-size:0.88rem; }
</style>
</head>
<body>
<div class="card">
    <h1>🔧 Setup WH Solutions</h1>
    <?php if($done): ?>
        <div class="success">✅ Super Admin configuré avec succès !<br>Username: <strong>admin</strong></div>
        <a href="admin/login.php"><button type="button">Accéder au Panel Admin</button></a>
        <div class="warn">⚠️ Supprimez ce fichier (setup.php) après configuration pour des raisons de sécurité.</div>
    <?php else: ?>
        <p>Configuration du Super Administrateur. Ce compte aura tous les droits de gestion.</p>
        <form method="POST">
            <label>Mot de passe Super Admin</label>
            <input type="password" name="password" value="admin" placeholder="Mot de passe" required>
            <button type="submit">Configurer le Super Admin</button>
        </form>
        <div class="warn">Le nom d'utilisateur sera : <strong>admin</strong></div>
    <?php endif; ?>
</div>
</body>
</html>
