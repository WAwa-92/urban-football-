<?php
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/csrf.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['admin_user'])) {
    header('Location: /Urban-Center-main/social-cms/dashboard.php');
    exit;
}

$pdo = getPDO();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'La session a expiré. Merci de recommencer.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role, status FROM admin_users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_user'] = [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];

            $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $user['id']]);
            header('Location: /Urban-Center-main/social-cms/dashboard.php');
            exit;
        }

        $message = 'Identifiants incorrects.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Social CMS</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { display:grid; place-items:center; min-height:100vh; padding:24px; }
        .login-box { width:min(520px, 100%); background:#fff; border:1px solid #e5e7eb; border-radius:24px; box-shadow:0 18px 50px rgba(15,23,42,.08); padding:28px; }
        .login-head p { margin: 0; color:#6b7280; }
        .login-head h1 { margin: 8px 0 10px; }
        .login-foot { margin-top: 18px; color:#6b7280; }
    </style>
</head>
<body>
    <section class="login-box">
        <div class="login-head">
            <span class="cms-badge" style="background:#eef2ff;color:#3730a3;">Social CMS</span>
            <h1>Connexion</h1>
            <p>Accès à la bibliothèque, au calendrier éditorial et aux outils de communication.</p>
        </div>

        <?php if ($message !== ''): ?>
            <p style="color:#b91c1c;font-weight:700;margin:18px 0 0;">⚠️ <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="post" class="cms-form" style="margin-top:18px;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="cms-field">
                <label for="email">Adresse email</label>
                <input id="email" name="email" type="email" autocomplete="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="cms-field">
                <label for="password">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <div class="cms-actions">
                <button class="cms-button" type="submit">Se connecter</button>
                <a class="cms-button cms-button-ghost" href="/Urban-Center-main/Urban Center.html">Retour au site</a>
            </div>
        </form>

        <p class="login-foot">Compte admin Urban Center réutilisé pour garder une gestion simple et réaliste.</p>
    </section>
</body>
</html>
