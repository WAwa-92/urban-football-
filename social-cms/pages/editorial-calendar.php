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

    header('Location: editorial-calendar.php?created=1');
    exit;
}

$events = $pdo->query('SELECT * FROM cms_editorial_calendar ORDER BY scheduled_date ASC, id DESC')->fetchAll();

cmsRenderHeader('Calendrier éditorial', 'Préparer les publications par date, activité et réseau social.', 'calendar');
?>
<?php
$csrfToken = htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8');
$calYear  = (int) ($_GET['year']  ?? date('Y'));
$calMonth = (int) ($_GET['month'] ?? date('m'));
if ($calMonth < 1) { $calMonth = 12; $calYear--; }
if ($calMonth > 12) { $calMonth = 1; $calYear++; }
$prevMonth = $calMonth - 1 ?: 12;
$prevYear  = $calMonth - 1 ? $calYear : $calYear - 1;
$nextMonth = $calMonth % 12 + 1;
$nextYear  = $calMonth < 12 ? $calYear : $calYear + 1;
$daysInMonth = (int) date('t', mktime(0,0,0,$calMonth,1,$calYear));
$firstWeekDay = (int) date('N', mktime(0,0,0,$calMonth,1,$calYear));

$eventsByDay = [];
foreach ($events as $ev) {
    if (substr($ev['scheduled_date'], 0, 7) === sprintf('%04d-%02d', $calYear, $calMonth)) {
        $day = (int) substr($ev['scheduled_date'], 8, 2);
        $eventsByDay[$day][] = $ev;
    }
}
$platformColors = ['Facebook'=>'#1e40af','Instagram'=>'#be185d','TikTok'=>'#111827','LinkedIn'=>'#0369a1','YouTube'=>'#b91c1c'];
$monthNames = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
?>
        <section class="cms-layout-two">
            <article class="cms-card">
                <h3>Nouvelle publication</h3>
                <?php if (!empty($_GET['created'])): ?>
                    <p class="cms-chip success" style="display:inline-block;margin-bottom:12px;">Publication enregistrée.</p>
                <?php endif; ?>
                <form method="post" class="cms-form" style="margin-top:14px;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
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
                <h3>Notifications à venir</h3>
                <?php
                $soon = array_filter($events, function($ev) {
                    $diff = (strtotime($ev['scheduled_date']) - time()) / 86400;
                    return $diff >= 0 && $diff <= 7 && $ev['status'] === 'scheduled';
                });
                ?>
                <?php if (!$soon): ?>
                    <p class="cms-muted">Aucune publication dans les 7 prochains jours.</p>
                <?php else: foreach ($soon as $notif): ?>
                    <div class="cms-preview-box" style="margin-bottom:10px; border-left:3px solid <?php echo $platformColors[$notif['platform']] ?? '#1e3c72'; ?>; padding-left:12px;">
                        <strong><?php echo htmlspecialchars($notif['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <p class="cms-muted" style="margin:4px 0 0;"><?php echo htmlspecialchars($notif['platform'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($notif['scheduled_date'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php endforeach; endif; ?>
            </article>
        </section>

        <!-- Vue mensuelle -->
        <section class="cms-card" style="margin-top:18px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
                <h3 style="margin:0;"><?php echo $monthNames[$calMonth]; ?> <?php echo $calYear; ?></h3>
                <div style="display:flex;gap:8px;">
                    <a class="cms-button cms-button-ghost" href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>">← Précédent</a>
                    <a class="cms-button cms-button-ghost" href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>">Suivant →</a>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px; text-align:center; font-size:.8rem;">
                <?php foreach (['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $d): ?>
                    <div style="padding:6px; font-weight:700; color:#6b7280;"><?php echo $d; ?></div>
                <?php endforeach; ?>
                <?php for ($i = 1; $i < $firstWeekDay; $i++): ?>
                    <div></div>
                <?php endfor; ?>
                <?php for ($day = 1; $day <= $daysInMonth; $day++):
                    $isToday = ($day === (int)date('d') && $calMonth === (int)date('m') && $calYear === (int)date('Y'));
                    $dayEvents = $eventsByDay[$day] ?? [];
                ?>
                    <div style="min-height:58px; padding:4px; border:1px solid #e5e7eb; border-radius:6px; <?php echo $isToday ? 'background:#f3faf6; border-color:#1f6f43;' : 'background:#f9fafb;'; ?>">
                        <div style="font-weight:<?php echo $isToday ? '700' : '500'; ?>; color:<?php echo $isToday ? '#ff7a18' : '#374151'; ?>; font-size:.85rem;"><?php echo $day; ?></div>
                        <?php foreach ($dayEvents as $ev): ?>
                            <div title="<?php echo htmlspecialchars($ev['title'], ENT_QUOTES, 'UTF-8'); ?>" style="margin-top:2px; font-size:.65rem; background:<?php echo $platformColors[$ev['platform']] ?? '#374151'; ?>; color:#fff; border-radius:3px; padding:1px 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:pointer;" data-ev-id="<?php echo (int)$ev['id']; ?>">
                                <?php echo htmlspecialchars(mb_strimwidth($ev['title'], 0, 14, '…'), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </section>

        <!-- Vue liste avec modifier/supprimer -->
        <section class="cms-card" style="margin-top:18px;">
            <h3>Toutes les publications</h3>
            <div class="cms-table" style="margin-top:14px;">
                <table>
                    <thead><tr><th>Date</th><th>Titre</th><th>Plateforme</th><th>Activité</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody id="calendar-tbody">
                        <?php if (!$events): ?>
                            <tr><td colspan="6" class="cms-muted">Aucune entrée.</td></tr>
                        <?php else: foreach ($events as $event): ?>
                            <tr id="ev-row-<?php echo (int)$event['id']; ?>">
                                <td><?php echo htmlspecialchars($event['scheduled_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:.75rem;background:<?php echo $platformColors[$event['platform']] ?? '#374151'; ?>;color:#fff;"><?php echo htmlspecialchars($event['platform'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($event['activity'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="cms-chip info"><?php echo htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td style="white-space:nowrap;">
                                    <button class="cms-button cms-button-ghost" style="font-size:.75rem;padding:4px 10px;" data-action="edit-event" data-id="<?php echo (int)$event['id']; ?>" data-title="<?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?>" data-content="<?php echo htmlspecialchars($event['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-platform="<?php echo htmlspecialchars($event['platform'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-activity="<?php echo htmlspecialchars($event['activity'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-audience="<?php echo htmlspecialchars($event['audience'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-date="<?php echo htmlspecialchars($event['scheduled_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-status="<?php echo htmlspecialchars($event['status'] ?? 'scheduled', ENT_QUOTES, 'UTF-8'); ?>">Modifier</button>
                                    <button class="cms-button cms-button-danger" style="font-size:.75rem;padding:4px 10px;margin-left:4px;" data-action="delete-event" data-id="<?php echo (int)$event['id']; ?>">Supprimer</button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Modal édition -->
        <div id="edit-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:999; align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:16px; padding:28px; width:min(560px,95vw); max-height:90vh; overflow-y:auto;">
                <h3 style="margin:0 0 18px;">Modifier la publication</h3>
                <form id="edit-form" class="cms-form">
                    <input type="hidden" id="edit-id">
                    <div class="cms-field"><label>Titre</label><input id="edit-title" required></div>
                    <div class="cms-field"><label>Contenu</label><textarea id="edit-content" rows="4"></textarea></div>
                    <div class="cms-field"><label>Plateforme</label><select id="edit-platform"><option>Facebook</option><option>Instagram</option><option>TikTok</option><option>LinkedIn</option><option>YouTube</option></select></div>
                    <div class="cms-field"><label>Activité</label><input id="edit-activity"></div>
                    <div class="cms-field"><label>Public visé</label><input id="edit-audience"></div>
                    <div class="cms-field"><label>Date</label><input id="edit-date" type="date"></div>
                    <div class="cms-field"><label>Statut</label><select id="edit-status"><option value="scheduled">Programmé</option><option value="draft">Brouillon</option><option value="published">Publié</option></select></div>
                    <div style="display:flex;gap:10px;margin-top:8px;">
                        <button class="cms-button" type="submit">Enregistrer</button>
                        <button class="cms-button cms-button-ghost" type="button" id="close-modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>

<?php
cmsRenderFooter();
?>
<script>
const CSRF = '<?php echo $csrfToken; ?>';
const scheduleUrl = '<?php echo htmlspecialchars(cmsUrl('/social-cms/api/schedule-post.php'), ENT_QUOTES, 'UTF-8'); ?>';
const modal = document.getElementById('edit-modal');

document.addEventListener('click', async (e) => {
    // Supprimer
    const delBtn = e.target.closest('[data-action="delete-event"]');
    if (delBtn) {
        if (!confirm('Supprimer cette publication ?')) return;
        const id = Number(delBtn.dataset.id);
        const res = await fetch(scheduleUrl, {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({csrf_token: CSRF, id}),
        });
        if (res.ok) {
            document.getElementById('ev-row-' + id)?.remove();
        } else {
            alert('Erreur lors de la suppression.');
        }
    }

    // Ouvrir modal édition
    const editBtn = e.target.closest('[data-action="edit-event"]');
    if (editBtn) {
        document.getElementById('edit-id').value = editBtn.dataset.id;
        document.getElementById('edit-title').value = editBtn.dataset.title;
        document.getElementById('edit-content').value = editBtn.dataset.content;
        document.getElementById('edit-platform').value = editBtn.dataset.platform;
        document.getElementById('edit-activity').value = editBtn.dataset.activity;
        document.getElementById('edit-audience').value = editBtn.dataset.audience;
        document.getElementById('edit-date').value = editBtn.dataset.date;
        document.getElementById('edit-status').value = editBtn.dataset.status;
        modal.style.display = 'flex';
    }

    // Fermer modal
    if (e.target === modal || e.target.id === 'close-modal') {
        modal.style.display = 'none';
    }
});

document.getElementById('edit-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await fetch(scheduleUrl, {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            csrf_token: CSRF,
            id: Number(document.getElementById('edit-id').value),
            title: document.getElementById('edit-title').value,
            content: document.getElementById('edit-content').value,
            platform: document.getElementById('edit-platform').value,
            activity: document.getElementById('edit-activity').value,
            audience: document.getElementById('edit-audience').value,
            scheduled_date: document.getElementById('edit-date').value,
            status: document.getElementById('edit-status').value,
        }),
    });
    if (res.ok) {
        modal.style.display = 'none';
        location.reload();
    } else {
        alert('Erreur lors de la modification.');
    }
});
</script>
