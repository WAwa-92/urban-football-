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
    <link rel="stylesheet" href="/Urban-Center-main/social-cms/style.css">
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
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'dashboard'); ?>" href="/Urban-Center-main/social-cms/dashboard.php">Tableau de bord</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'media'); ?>" href="/Urban-Center-main/social-cms/pages/media-library.php">Bibliothèque multimédia</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'calendar'); ?>" href="/Urban-Center-main/social-cms/pages/editorial-calendar.php">Calendrier éditorial</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'generator'); ?>" href="/Urban-Center-main/social-cms/pages/content-generator.php">Générateur de contenu</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'analytics'); ?>" href="/Urban-Center-main/social-cms/pages/analytics.php">Analytics</a>
            <a class="cms-nav-link<?php echo cmsActiveClass($activePage, 'users'); ?>" href="/Urban-Center-main/social-cms/admin/users.php">Utilisateurs</a>
        </nav>

        <div class="cms-sidebar-footer">
            <p><?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?></p>
            <small><?php echo htmlspecialchars(strtoupper($currentRole), ENT_QUOTES, 'UTF-8'); ?></small>
            <a class="cms-logout" href="/Urban-Center-main/pages/logout.php">Retour au site</a>
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
            <a class="cms-button cms-button-ghost" href="/Urban-Center-main/admin/dashboard.php">Admin Urban Center</a>
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
