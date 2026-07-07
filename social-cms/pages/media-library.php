<?php
require_once __DIR__ . '/../../social-cms/config.php';
require_once __DIR__ . '/../../social-cms/php/layout.php';

cmsEnsureManagerAccess();

$pdo = cmsPdo();
$filterCategory = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');

$sql = 'SELECT * FROM cms_media_library WHERE 1=1';
$params = [];

if ($filterCategory !== '') {
    $sql .= ' AND category = :category';
    $params[':category'] = $filterCategory;
}

if ($search !== '') {
    $sql .= ' AND (title LIKE :search OR original_name LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

$sql .= ' ORDER BY id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mediaItems = $stmt->fetchAll();

$categories = ['Photos', 'Vidéos', 'Affiches', 'Logos', 'Flyers', 'Général'];
$uploadStatus = $_GET['upload'] ?? '';
$uploadMessage = trim($_GET['message'] ?? '');

cmsRenderHeader('Bibliothèque multimédia', 'Classer, rechercher et préparer les visuels destinés aux réseaux sociaux.', 'media');
?>
        <section class="cms-layout-two">
            <article class="cms-card">
                <h3>Ajouter un média</h3>
                <?php if ($uploadStatus === 'ok'): ?>
                    <p class="cms-chip success">Média téléversé avec succès.</p>
                <?php elseif ($uploadStatus === 'error'): ?>
                    <p class="cms-chip warning"><?php echo htmlspecialchars($uploadMessage !== '' ? $uploadMessage : 'Erreur pendant le téléversement.', ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <form class="cms-form" action="<?php echo htmlspecialchars(cmsUrl('/social-cms/api/upload-media.php'), ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars(cmsUrl('/social-cms/pages/media-library.php'), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="cms-field">
                        <label for="title">Titre</label>
                        <input id="title" name="title" type="text" required placeholder="Ex. Flyer tournoi juin">
                    </div>
                    <div class="cms-field">
                        <label for="category">Catégorie</label>
                        <select id="category" name="category">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cms-field">
                        <label for="media_file">Fichier</label>
                        <input id="media_file" name="media_file" type="file" required accept="image/*,video/*,.pdf">
                    </div>
                    <button class="cms-button" type="submit">Téléverser</button>
                </form>
            </article>

            <article class="cms-card">
                <h3>Recherche rapide</h3>
                <form class="cms-search" method="get">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Rechercher un fichier">
                    <select name="category">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filterCategory === $category ? 'selected' : ''; ?>><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="cms-button cms-button-ghost" type="submit">Filtrer</button>
                </form>

                <div class="cms-preview-box">
                    <strong><?php echo count($mediaItems); ?></strong> média(s) affiché(s) pour l’instant.
                    <p class="cms-muted" style="margin-bottom:0;">Le premier usage est simple : on range les contenus avant de penser à une logique plus poussée.</p>
                </div>
            </article>
        </section>

        <section class="cms-card" style="margin-top:18px;">
            <h3>Contenus récents</h3>
            <div class="cms-gallery" style="margin-top:14px;">
                <?php if (!$mediaItems): ?>
                    <p class="cms-muted">Aucun fichier pour le moment.</p>
                <?php else: foreach ($mediaItems as $item): ?>
                    <article class="cms-thumb">
                        <figure>
                            <?php if (($item['file_type'] ?? '') === 'video'): ?>
                                <video controls src="<?php echo htmlspecialchars(cmsUrl('/' . ltrim((string) $item['file_path'], '/')), ENT_QUOTES, 'UTF-8'); ?>"></video>
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars(cmsUrl('/' . ltrim((string) $item['file_path'], '/')), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>
                        </figure>
                        <div class="cms-thumb-body">
                            <strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <p class="cms-muted" style="margin:6px 0 10px;">
                                <?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo cmsFormatBytes((int) $item['file_size']); ?>
                            </p>
                            <a class="cms-button cms-button-ghost" href="<?php echo htmlspecialchars(cmsUrl('/' . ltrim((string) $item['file_path'], '/')), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Prévisualiser</a>
                                        <button class="cms-button cms-button-danger" style="margin-top:6px;" data-action="delete-media" data-id="<?php echo (int) $item['id']; ?>" data-csrf="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">Supprimer</button>
                        </div>
                    </article>
                <?php endforeach; endif; ?>
            </div>
        </section>
<?php
cmsRenderFooter();
            ?>
            <script>
            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('[data-action="delete-media"]');
                if (!btn) return;
                if (!confirm('Supprimer ce média définitivement ?')) return;

                const id = btn.dataset.id;
                const csrf = btn.dataset.csrf;

                btn.disabled = true;
                btn.textContent = '…';

                const res = await fetch('<?php echo htmlspecialchars(cmsUrl('/social-cms/api/delete-media.php'), ENT_QUOTES, 'UTF-8'); ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: Number(id), csrf_token: csrf}),
                });

                if (res.ok) {
                    btn.closest('article').remove();
                } else {
                    const data = await res.json().catch(() => ({}));
                    alert(data.error || 'Erreur lors de la suppression.');
                    btn.disabled = false;
                    btn.textContent = 'Supprimer';
                }
            });
            </script>
