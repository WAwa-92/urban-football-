<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/php/layout.php';

cmsEnsureManagerAccess();

$pdo = cmsPdo();

$stats = [
    'media_count' => (int) $pdo->query('SELECT COUNT(*) FROM cms_media_library')->fetchColumn(),
    'calendar_count' => (int) $pdo->query('SELECT COUNT(*) FROM cms_editorial_calendar')->fetchColumn(),
    'posts_count' => (int) $pdo->query('SELECT COUNT(*) FROM cms_social_posts')->fetchColumn(),
    'scheduled_count' => (int) $pdo->query("SELECT COUNT(*) FROM cms_editorial_calendar WHERE status = 'scheduled'")->fetchColumn(),
];

$upcoming = $pdo->query("SELECT title, platform, scheduled_date, status FROM cms_editorial_calendar ORDER BY scheduled_date ASC, id DESC LIMIT 6")->fetchAll();
$notifications = $pdo->query("SELECT title, message, type, is_read FROM cms_notifications ORDER BY id DESC LIMIT 5")->fetchAll();

cmsRenderHeader('Tableau de bord', 'Vue d’ensemble du contenu à préparer et publier.', 'dashboard');
?>
        <section class="cms-grid cards">
            <article class="cms-card"><h3>Médias</h3><p class="cms-stat"><?php echo $stats['media_count']; ?></p><p class="cms-muted">Photos, affiches, vidéos, logos</p></article>
            <article class="cms-card"><h3>Posts créés</h3><p class="cms-stat"><?php echo $stats['posts_count']; ?></p><p class="cms-muted">Contenus générés ou saisis</p></article>
            <article class="cms-card"><h3>Éléments planifiés</h3><p class="cms-stat"><?php echo $stats['calendar_count']; ?></p><p class="cms-muted">Publications et campagnes</p></article>
            <article class="cms-card"><h3>Publications à venir</h3><p class="cms-stat"><?php echo $stats['scheduled_count']; ?></p><p class="cms-muted">Statut scheduled uniquement</p></article>
        </section>

        <section class="cms-layout-two">
            <article class="cms-card">
                <div class="cms-hero">
                    <span class="cms-chip info">Plan de travail</span>
                    <h3>Ce qui est déjà prêt</h3>
                    <p>La base technique est posée : auth, BDD, bibliothèque média et calendrier éditorial peuvent maintenant être alimentés progressivement sans donner un rendu trop “généré”.</p>
                </div>
                <div class="cms-actions" style="margin-top:18px;">
                    <a class="cms-button" href="/Urban-Center-main/social-cms/pages/media-library.php">Ajouter un média</a>
                    <a class="cms-button cms-button-ghost" href="/Urban-Center-main/social-cms/pages/content-generator.php">Générer un texte</a>
                </div>
            </article>

            <article class="cms-card">
                <h3>Notifications récentes</h3>
                <div class="cms-list">
                    <?php if (!$notifications): ?>
                        <p class="cms-muted">Aucune notification pour le moment.</p>
                    <?php else: foreach ($notifications as $notification): ?>
                        <div>
                            <span class="cms-chip <?php echo htmlspecialchars($notification['type'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($notification['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <strong style="display:block;margin-top:8px;"><?php echo htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <p class="cms-muted" style="margin:6px 0 0;"><?php echo htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </article>
        </section>

        <section class="cms-card" style="margin-top:18px;">
            <h3>Calendrier éditorial – prochaines entrées</h3>
            <div class="cms-table" style="margin-top:14px;">
                <table>
                    <thead>
                        <tr><th>Titre</th><th>Plateforme</th><th>Date</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$upcoming): ?>
                            <tr><td colspan="4" class="cms-muted">Aucune publication planifiée pour l’instant.</td></tr>
                        <?php else: foreach ($upcoming as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['platform'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['scheduled_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="cms-chip info"><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
<?php
cmsRenderFooter();
