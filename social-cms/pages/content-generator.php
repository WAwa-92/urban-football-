<?php
require_once __DIR__ . '/../../social-cms/config.php';
require_once __DIR__ . '/../../social-cms/php/layout.php';

cmsEnsureManagerAccess();

$pdo = cmsPdo();
$templates = $pdo->query('SELECT * FROM cms_content_templates ORDER BY is_default DESC, id DESC')->fetchAll();

cmsRenderHeader('Générateur de contenu', 'Préparer des textes courts et naturels pour les réseaux.', 'generator');
?>
        <section class="cms-layout-two">
            <article class="cms-card">
                <h3>Créer une proposition</h3>
                <form class="cms-form" id="generator-form">
                    <input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="cms-field"><label for="activity">Activité</label><input id="activity" name="activity" required placeholder="Tournoi de football, séance fitness..."></div>
                    <div class="cms-field"><label for="audience">Public visé</label><input id="audience" name="audience" required placeholder="Adolescents, familles, entreprises..."></div>
                    <div class="cms-field"><label for="date">Date</label><input id="date" name="date" type="date" value="<?php echo date('Y-m-d'); ?>"></div>
                    <div class="cms-field"><label for="platform">Plateforme</label><select id="platform" name="platform"><option>Instagram</option><option>Facebook</option><option>TikTok</option><option>LinkedIn</option></select></div>
                    <button class="cms-button" type="submit">Générer</button>
                </form>
            </article>

            <article class="cms-card">
                <h3>Exemples de base</h3>
                <div class="cms-list">
                    <?php foreach ($templates as $template): ?>
                        <div class="cms-preview-box">
                            <strong><?php echo htmlspecialchars($template['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <p class="cms-muted" style="margin-bottom:0;"><?php echo htmlspecialchars($template['template_text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </section>

        <section class="cms-card" style="margin-top:18px;">
            <h3>Résultat</h3>
            <div class="cms-preview-box" id="result-box">
                <p class="cms-muted">Remplissez le formulaire pour obtenir une proposition de publication.</p>
            </div>
            <div id="save-actions" style="display:none; margin-top:14px;">
                <button class="cms-button" id="btn-save-calendar" type="button">📅 Sauvegarder dans le calendrier</button>
                <span id="save-status" style="margin-left:12px; font-size:.9rem; color:#10b981;"></span>
            </div>
        </section>

        <script>
        const form = document.getElementById('generator-form');
        const resultBox = document.getElementById('result-box');
        const saveActions = document.getElementById('save-actions');
        const saveStatus = document.getElementById('save-status');
        const scheduleUrl = '<?php echo htmlspecialchars(cmsUrl('/social-cms/api/schedule-post.php'), ENT_QUOTES, 'UTF-8'); ?>';
        let lastGenerated = null;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            saveActions.style.display = 'none';
            saveStatus.textContent = '';
            lastGenerated = null;

            const response = await fetch('../api/generate-content.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    ...Object.fromEntries(new FormData(form).entries()),
                    csrf_token: document.getElementById('csrf_token').value,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                resultBox.innerHTML = `<p class="cms-chip warning">${data.error || 'Erreur pendant la génération.'}</p>`;
                return;
            }

            lastGenerated = data;
            resultBox.innerHTML = `
                <strong>${data.title}</strong>
                <p style="margin:10px 0 0; white-space:pre-line;">${data.content}</p>
                <p class="cms-muted" style="margin:10px 0 0;">${data.hashtags}</p>
            `;
            saveActions.style.display = 'block';
        });

        document.getElementById('btn-save-calendar').addEventListener('click', async () => {
            if (!lastGenerated) return;
            const fd = Object.fromEntries(new FormData(form).entries());
            const res = await fetch(scheduleUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    csrf_token: document.getElementById('csrf_token').value,
                    title: lastGenerated.title,
                    content: lastGenerated.content + '\n\n' + lastGenerated.hashtags,
                    platform: fd.platform || 'Instagram',
                    activity: fd.activity || '',
                    audience: fd.audience || '',
                    scheduled_date: fd.date || new Date().toISOString().split('T')[0],
                    status: 'draft',
                }),
            });
            if (res.ok) {
                saveStatus.textContent = 'Ajouté au calendrier éditorial.';
            } else {
                saveStatus.style.color = '#dc2626';
                saveStatus.textContent = 'Erreur lors de l\'enregistrement.';
            }
        });
        </script>
<?php
cmsRenderFooter();
