<?php
require_once __DIR__ . '/../../social-cms/config.php';
require_once __DIR__ . '/../../social-cms/php/layout.php';

cmsEnsureManagerAccess();

$pdo = cmsPdo();
$platforms = $pdo->query("SELECT platform, COUNT(*) AS total FROM cms_editorial_calendar GROUP BY platform ORDER BY total DESC")->fetchAll();
$statuses = $pdo->query("SELECT status, COUNT(*) AS total FROM cms_editorial_calendar GROUP BY status ORDER BY total DESC")->fetchAll();
$activities = $pdo->query("SELECT COALESCE(NULLIF(TRIM(activity),''), 'Non défini') AS activity, COUNT(*) AS total FROM cms_editorial_calendar GROUP BY activity ORDER BY total DESC LIMIT 10")->fetchAll();

cmsRenderHeader('Analytics', 'Quelques repères simples pour suivre le contenu préparé.', 'analytics');
?>
        <section class="cms-grid cards">
            <article class="cms-card"><h3>Publications</h3><p class="cms-stat"><?php echo (int) $pdo->query('SELECT COUNT(*) FROM cms_social_posts')->fetchColumn(); ?></p></article>
            <article class="cms-card"><h3>Calendrier</h3><p class="cms-stat"><?php echo (int) $pdo->query('SELECT COUNT(*) FROM cms_editorial_calendar')->fetchColumn(); ?></p></article>
            <article class="cms-card"><h3>Médias</h3><p class="cms-stat"><?php echo (int) $pdo->query('SELECT COUNT(*) FROM cms_media_library')->fetchColumn(); ?></p></article>
            <article class="cms-card"><h3>Templates</h3><p class="cms-stat"><?php echo (int) $pdo->query('SELECT COUNT(*) FROM cms_content_templates')->fetchColumn(); ?></p></article>
        </section>

        <section class="cms-layout-two">
            <article class="cms-card">
                <h3>Répartition par plateforme</h3>
                <canvas id="platformChart" class="cms-chart"></canvas>
            </article>
            <article class="cms-card">
                <h3>Répartition par statut</h3>
                <canvas id="statusChart" class="cms-chart"></canvas>
            </article>
        </section>

        <section class="cms-card" style="margin-top:18px;">
            <h3>Répartition par activité</h3>
            <canvas id="activityChart" class="cms-chart" style="max-height:260px;"></canvas>
        </section>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        new Chart(document.getElementById('platformChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(fn($row) => $row['platform'], $platforms), JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    label: 'Entrées',
                    data: <?php echo json_encode(array_map(fn($row) => (int) $row['total'], $platforms)); ?>,
                    backgroundColor: '#2a5298'
                }]
            }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(fn($row) => $row['status'], $statuses), JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(fn($row) => (int) $row['total'], $statuses)); ?>,
                    backgroundColor: ['#1e3c72', '#ff7a18', '#10b981']
                }]
            }
        });

        new Chart(document.getElementById('activityChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(fn($row) => $row['activity'], $activities), JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    label: 'Publications par activité',
                    data: <?php echo json_encode(array_map(fn($row) => (int) $row['total'], $activities)); ?>,
                    backgroundColor: ['#ff7a18','#1e40af','#10b981','#be185d','#0369a1','#b91c1c','#7c3aed','#0891b2','#ca8a04','#374151'],
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
        </script>
<?php
cmsRenderFooter();
