<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            if (isset($admin['is_active']) && !$admin['is_active']) {
                $error = 'Ce compte est désactivé. Contactez le super administrateur.';
            } else {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
                $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
                logAdminActivity($pdo, $admin['id'], 'login', 'Connexion réussie');
                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion Admin - WH Solutions</title>
<link rel="icon" href="<?= SITE_URL ?>/assets/images/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="WH Solutions" style="height:60px; margin-bottom:15px;">
            <h1>Administration</h1>
            <p>Connectez-vous pour accéder au panneau d'administration</p>
        </div>
        <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom d'utilisateur</label>
                <input type="text" name="username" required placeholder="admin" autofocus>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Se Connecter</button>
        </form>
        <div class="login-footer">
            <a href="<?= SITE_URL ?>/"><i class="fas fa-arrow-left"></i> Retour au site</a>
        </div>
    </div>
</div>
</body>
</html>