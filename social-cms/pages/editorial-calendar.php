<?php
require_once __DIR__ . '/../../social-cms/config.php';
require_once __DIR__ . '/../../social-cms/php/layout.php';

cmsEnsureManagerAccess();

$pdo = cmsPdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    $insert = $pdo->prepare('INSERT INTO cms_editorial_calendar (title, content, platform, activity, audience, scheduled_date, scheduled_time, status, created_by) VALUES (:title, :content, :platform, :activity, :audience, :scheduled_date, :scheduled_time, :status, :created_by)');
    $insert->execute([
        ':title' => trim($_POST['title'] ?? ''),
        ':content' => trim($_POST['content'] ?? ''),
        ':platform' => trim($_POST['platform'] ?? ''),
        ':activity' => trim($_POST['activity'] ?? ''),
        ':audience' => trim($_POST['audience'] ?? ''),
        ':scheduled_date' => $_POST['scheduled_date'] ?? date('Y-m-d'),
        ':scheduled_time' => $_POST['scheduled_time'] ?: null,
        ':status' => 'scheduled',
        ':created_by' => $_SESSION['admin_user']['email'] ?? null,
    ]);

    header('Location: /Urban-Center-main/social-cms/pages/editorial-calendar.php?created=1');
    exit;
}

$events = $pdo->query('SELECT * FROM cms_editorial_calendar ORDER BY scheduled_date ASC, id DESC')->fetchAll();

cmsRenderHeader('Calendrier éditorial', 'Préparer les publications par date, activité et réseau social.', 'calendar');
?>
        <section class="cms-layout-two">
            <article class="cms-card">
                <h3>Nouvelle publication</h3>
                <?php if (!empty($_GET['created'])): ?>
                    <p class="cms-chip success">Publication enregistrée.</p>
                <?php endif; ?>
                <form method="post" class="cms-form" style="margin-top:14px;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="cms-field"><label for="title">Titre</label><input id="title" name="title" required></div>
                    <div class="cms-field"><label for="content">Contenu</label><textarea id="content" name="content" required placeholder="Raconter l'activité, la date, l'ambiance..."></textarea></div>
                    <div class="cms-field"><label for="platform">Plateforme</label><select id="platform" name="platform"><option>Facebook</option><option>Instagram</option><option>TikTok</option><option>LinkedIn</option><option>YouTube</option></select></div>
                    <div class="cms-field"><label for="activity">Activité</label><input id="activity" name="activity" placeholder="Football, Padel, Fitness..." required></div>
                    <div class="cms-field"><label for="audience">Public visé</label><input id="audience" name="audience" placeholder="Adolescents, familles, entreprises..." required></div>
                    <div class="cms-field"><label for="scheduled_date">Date</label><input id="scheduled_date" name="scheduled_date" type="date" required value="<?php echo date('Y-m-d'); ?>"></div>
                    <div class="cms-field"><label for="scheduled_time">Heure</label><input id="scheduled_time" name="scheduled_time" type="time"></div>
                    <button class="cms-button" type="submit">Programmer</button>
                </form>
            </article>

            <article class="cms-card">
                <h3>Vision rapide</h3>
                <div class="cms-preview-box">
                    <p class="cms-muted" style="margin-top:0;">Le calendrier reste volontairement simple pour le moment : une vue claire, des contenus lisibles et une base facile à expliquer en stage.</p>
                </div>
                <div class="cms-table" style="margin-top:14px;">
                    <table>
                        <thead><tr><th>Date</th><th>Titre</th><th>Plateforme</th><th>Statut</th></tr></thead>
                        <tbody>
                            <?php if (!$events): ?>
                                <tr><td colspan="4" class="cms-muted">Aucune entrée.</td></tr>
                            <?php else: foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['scheduled_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($event['platform'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="cms-chip info"><?php echo htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
<?php
cmsRenderFooter();
