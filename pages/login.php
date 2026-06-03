<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/csrf.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Si déjà connecté → rediriger directement selon le rôle
if (!empty($_SESSION['admin_user'])) {
    header('Location: ../admin/dashboard.php');
    exit;
}
if (!empty($_SESSION['site_user'])) {
    if (($_SESSION['site_user']['role'] ?? '') === 'coach') {
        header('Location: coach-dashboard.php');
        exit;
    }
    header('Location: ../Urban Center.html');
    exit;
}

$pdo = getPDO();
// S'assurer que le compte admin par défaut existe
require_once __DIR__ . '/../admin/config.php';
ensureDefaultAdmin($pdo);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Session expirée. Merci de réessayer.';
    } else {
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        // 1. Vérifier la table admin_users (admin / manager / super_admin)
        $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role, status FROM admin_users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && $admin['status'] === 'active' && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_user'] = [
                'id'        => $admin['id'],
                'full_name' => $admin['full_name'],
                'email'     => $admin['email'],
                'role'      => $admin['role'],
            ];
            $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $admin['id']]);
            header('Location: ../admin/dashboard.php');
            exit;
        }

        // 2. Vérifier la table users (joueur / coach)
        $stmt2 = $pdo->prepare('SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = :email LIMIT 1');
        $stmt2->execute([':email' => $email]);
        $user = $stmt2->fetch();

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['site_user'] = [
                'id'        => $user['id'],
                'full_name' => $user['full_name'],
                'email'     => $user['email'],
                'role'      => $user['role'],
            ];
            $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $user['id']]);

            if ($user['role'] === 'coach') {
                header('Location: coach-dashboard.php');
                exit;
            }
            header('Location: ../Urban Center.html');
            exit;
        }

        $error = 'Email ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion – Urban Center</title>
    <meta name="description" content="Connectez-vous à votre espace Urban Center : joueur, coach ou administrateur.">
    <link rel="shortcut icon" href="../assets/img/logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages.css">
</head>
<body>
    <nav id="main-nav">
        <a href="../Urban Center.html" class="nav-brand">
            <img src="../assets/img/logo.jpg" width="36" height="36" alt="Logo" style="border-radius:50%;vertical-align:middle;margin-right:8px;">
            Urban Center
        </a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <ul id="nav-menu">
            <li><a href="../Urban Center.html">Accueil</a></li>
            <li><a href="installations.html">Installations</a></li>
            <li><a href="events.html">Événements</a></li>
            <li><a href="gallery.html">Galerie</a></li>
            <li><a href="../Urban Center.html#contactez-nous">Contact</a></li>
            <li><a href="login.php" class="nav-action">Se connecter</a></li>
            <li><a href="register.php" class="nav-action-secondary">S'inscrire</a></li>
            <li><a href="reservation.html" class="nav-action nav-action-reserve">Réserver</a></li>
        </ul>
    </nav>

    <div class="page-hero" style="min-height:200px;">
        <h1>Bon retour 👋</h1>
        <p>Content de vous revoir sur Urban Center.</p>
    </div>

    <section class="container" style="max-width:520px; padding-top:48px; padding-bottom:60px;">
        <div class="contact-form" style="max-width:100%;">

            <?php if ($error !== ''): ?>
                <p style="color:#c0392b; margin-bottom:18px; font-weight:600;">❌ <?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label for="email">Votre email</label>
                    <input id="email" type="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="exemple@email.com" autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Votre mot de passe</label>
                    <input id="password" type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
                </div>

                <button type="submit" class="submit-btn">Se connecter →</button>
            </form>

            <p style="margin-top:22px; color:#666; font-size:0.95rem;">
                Pas encore membre ?
                <a href="register.php" style="color:#1e3c72; font-weight:700;">Créer un compte</a>
            </p>

        </div>
    </section>

    <footer>
        <p>&copy; 2026 Urban Center Hessi Djerbi. Tous droits réservés.</p>
        <p style="margin-top:8px;opacity:0.8;font-size:0.9rem;">
            Contact : <a href="mailto:info@urbancenterhjb.com" style="color:#ff7a18;text-decoration:none;">info@urbancenterhjb.com</a>
            &nbsp;·&nbsp; Téléphone : <a href="tel:+21600000000" style="color:#ff7a18;text-decoration:none;">+216 XX XXX XXX</a>
        </p>
    </footer>

    <script src="../assets/js/app.js" defer></script>
</body>
</html>
