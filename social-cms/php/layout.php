<?php

function cmsRenderHeader(string $title, string $subtitle = '', string $activePage = 'dashboard'): void
{
    $currentUser = $_SESSION['admin_user']['full_name'] ?? 'Équipe';
    $currentRole = $_SESSION['admin_user']['role'] ?? 'admin';
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?> – Social CMS</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/style.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
<div class="cms-shell">
    <aside class="cms-sidebar">
        <div class="cms-brand">
            <span class="cms-badge">Urban Center</span>
            <h1>Social CMS</h1>
            <p>Communication digitale</p>
        </div>

        <nav class="cms-nav">
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'dashboard'); ?>" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">Tableau de bord</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'media'); ?>" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/pages/media-library.php'), ENT_QUOTES, 'UTF-8'); ?>">Bibliothèque multimédia</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'calendar'); ?>" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/pages/editorial-calendar.php'), ENT_QUOTES, 'UTF-8'); ?>">Calendrier éditorial</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'generator'); ?>" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/pages/content-generator.php'), ENT_QUOTES, 'UTF-8'); ?>">Générateur de contenu</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'analytics'); ?>" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/pages/analytics.php'), ENT_QUOTES, 'UTF-8'); ?>">Analytics</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'users'); ?>" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/admin/users.php'), ENT_QUOTES, 'UTF-8'); ?>">Utilisateurs</a>
        </nav>

        <div class="cms-sidebar-footer">
            <p><?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?></p>
            <small><?php echo htmlspecialchars(strtoupper($currentRole), ENT_QUOTES, 'UTF-8'); ?></small>
            <a class="cms-logout" href="<?php echo htmlspecialchars(cmsUrl('/Urban Center.html'), ENT_QUOTES, 'UTF-8'); ?>">Retour au site public</a>
        </div>
    </aside>

    <main class="cms-main">
        <header class="cms-topbar">
            <div>
                <h2><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
                <?php if ($subtitle !== ''): ?>
                    <p><?php echo htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
            <a class="cms-button cms-button-ghost" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/admin/logout.php'), ENT_QUOTES, 'UTF-8'); ?>">Déconnexion</a>
        </header>
    <?php
}

function cmsRenderFooter(): void
{
    ?>
    </main>
</div>
</body>
</html>
    <?php
}
