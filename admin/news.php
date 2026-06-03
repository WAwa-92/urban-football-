<?php
require __DIR__ . '/config.php';
require __DIR__ . '/../php/csrf.php';
requireAdmin();

$pdo = getPDO();
ensureNewsTable($pdo);

$message = '';
$error = '';
$editId = (int) ($_GET['edit'] ?? 0);

$emptyNews = [
    'id' => 0,
    'title' => '',
    'content' => '',
    'image_url' => '',
    'published_at' => '',
    'is_published' => 1,
];
$currentNews = $emptyNews;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Session expirée. Merci de réessayer.';
    } else {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $newsId = (int) ($_POST['news_id'] ?? 0);
        if ($newsId > 0) {
            $stmt = $pdo->prepare('DELETE FROM news WHERE id = :id');
            $stmt->execute([':id' => $newsId]);
            $message = 'Actualité supprimée.';
        }
    } elseif ($action === 'toggle') {
        $newsId = (int) ($_POST['news_id'] ?? 0);
        if ($newsId > 0) {
            $stmt = $pdo->prepare('UPDATE news SET is_published = CASE WHEN is_published = 1 THEN 0 ELSE 1 END WHERE id = :id');
            $stmt->execute([':id' => $newsId]);
            $message = 'Visibilité de l’actualité mise à jour.';
        }
    } else {
        $newsId = (int) ($_POST['news_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        $publishedAt = trim($_POST['published_at'] ?? '');
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '' || $content === '') {
            $error = 'Le titre et le contenu sont obligatoires.';
            $currentNews = [
                'id' => $newsId,
                'title' => $title,
                'content' => $content,
                'image_url' => $imageUrl,
                'published_at' => $publishedAt,
                'is_published' => $isPublished,
            ];
        } else {
            if ($imageUrl === '') {
                $imageUrl = null;
            }
            if ($publishedAt === '') {
                $publishedAt = date('Y-m-d H:i:s');
            } else {
                $publishedAt = str_replace('T', ' ', $publishedAt) . ':00';
            }

            if ($newsId > 0) {
                $stmt = $pdo->prepare('UPDATE news SET title = :title, content = :content, image_url = :image_url, published_at = :published_at, is_published = :is_published WHERE id = :id');
                $stmt->execute([
                    ':title' => $title,
                    ':content' => $content,
                    ':image_url' => $imageUrl,
                    ':published_at' => $publishedAt,
                    ':is_published' => $isPublished,
                    ':id' => $newsId,
                ]);
                $message = 'Actualité mise à jour.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO news (title, content, image_url, published_at, is_published) VALUES (:title, :content, :image_url, :published_at, :is_published)');
                $stmt->execute([
                    ':title' => $title,
                    ':content' => $content,
                    ':image_url' => $imageUrl,
                    ':published_at' => $publishedAt,
                    ':is_published' => $isPublished,
                ]);
                $message = 'Actualité ajoutée.';
            }

            $currentNews = $emptyNews;
            $editId = 0;
        }
    }
    }
}

if ($error === '' && $editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM news WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $editId]);
    $found = $stmt->fetch();
    if ($found) {
        if (!empty($found['published_at'])) {
            $found['published_at'] = date('Y-m-d\TH:i', strtotime((string) $found['published_at']));
        }
        $currentNews = $found;
    }
}

$newsList = $pdo->query('SELECT * FROM news ORDER BY COALESCE(published_at, created_at) DESC')->fetchAll();
$publishedCount = 0;
foreach ($newsList as $newsItem) {
    if ((int) $newsItem['is_published'] === 1) {
        $publishedCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des actualités</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:30px; flex-wrap:wrap;">
            <div>
                <h1 class="section-title" style="margin-bottom:10px; text-align:left;">Gestion des actualités</h1>
                <p>Ajoutez et publiez les actualités du club.</p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="bt" href="dashboard.php">Dashboard</a>
                <a class="bt" href="reservations.php">Réservations</a>
                <a class="bt" href="events.php">Événements</a>
                <a class="bt" href="../Urban Center.html">Voir le site</a>
            </div>
        </div>

        <div class="stats">
            <div class="stat-box"><span class="stat-number"><?php echo count($newsList); ?></span><span class="stat-label">Actualités total</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $publishedCount; ?></span><span class="stat-label">Publiées</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo max(0, count($newsList) - $publishedCount); ?></span><span class="stat-label">Brouillons</span></div>
        </div>

        <div class="contact-form" style="max-width:100%; margin-bottom:30px;">
            <h2 class="form-title"><?php echo $currentNews['id'] ? 'Modifier une actualité' : 'Ajouter une actualité'; ?></h2>
            <?php if ($message !== ''): ?>
                <p style="color:#166534; margin-bottom:15px; font-weight:700;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <p style="color:#b91c1c; margin-bottom:15px; font-weight:700;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="news_id" value="<?php echo (int) $currentNews['id']; ?>">
                <input type="hidden" name="action" value="save">

                <label for="title">Titre *</label>
                <input id="title" name="title" type="text" required value="<?php echo htmlspecialchars((string) $currentNews['title']); ?>">

                <label for="content">Contenu *</label>
                <textarea id="content" name="content" rows="7" required><?php echo htmlspecialchars((string) $currentNews['content']); ?></textarea>

                <label for="image_url">URL image</label>
                <input id="image_url" name="image_url" type="url" placeholder="https://..." value="<?php echo htmlspecialchars((string) $currentNews['image_url']); ?>">

                <label for="published_at">Date de publication</label>
                <input id="published_at" name="published_at" type="datetime-local" value="<?php echo htmlspecialchars((string) $currentNews['published_at']); ?>">

                <label style="display:flex; align-items:center; gap:8px; margin:12px 0 18px;">
                    <input type="checkbox" name="is_published" <?php echo ((int) $currentNews['is_published'] === 1) ? 'checked' : ''; ?>>
                    Publier cette actualité
                </label>

                <button class="submit-btn" type="submit"><?php echo $currentNews['id'] ? 'Mettre à jour' : 'Ajouter'; ?></button>
                <?php if ($currentNews['id']): ?>
                    <a class="bt" href="news.php" style="margin-left:10px;">Annuler</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="contact-form" style="max-width:100%;">
            <h2 class="form-title">Liste des actualités</h2>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#1e3c72; color:#fff;">
                            <th style="padding:12px; text-align:left;">Titre</th>
                            <th style="padding:12px; text-align:left;">Publication</th>
                            <th style="padding:12px; text-align:left;">Statut</th>
                            <th style="padding:12px; text-align:left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($newsList)): ?>
                            <tr>
                                <td colspan="4" style="padding:16px; border-bottom:1px solid #eee;">Aucune actualité pour le moment.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($newsList as $news): ?>
                            <tr>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <strong><?php echo htmlspecialchars($news['title']); ?></strong>
                                    <div style="font-size:0.85rem; color:#666; margin-top:4px; max-width:560px;">
                                        <?php echo htmlspecialchars(mb_strimwidth((string) $news['content'], 0, 160, '…', 'UTF-8')); ?>
                                    </div>
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #eee; white-space:nowrap;">
                                    <?php
                                        $pubAt = $news['published_at'] ?: $news['created_at'];
                                        echo htmlspecialchars(date('d/m/Y H:i', strtotime((string) $pubAt)));
                                    ?>
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <?php if ((int) $news['is_published'] === 1): ?>
                                        <span style="background:#10b981;color:#fff;padding:4px 10px;border-radius:20px;font-size:0.8rem;font-weight:600;">Publiée</span>
                                    <?php else: ?>
                                        <span style="background:#6b7280;color:#fff;padding:4px 10px;border-radius:20px;font-size:0.8rem;font-weight:600;">Brouillon</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a class="bt" href="news.php?edit=<?php echo (int) $news['id']; ?>">Modifier</a>

                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="news_id" value="<?php echo (int) $news['id']; ?>">
                                            <button class="submit-btn" type="submit" style="width:auto; padding:10px 14px;">
                                                <?php echo ((int) $news['is_published'] === 1) ? 'Masquer' : 'Publier'; ?>
                                            </button>
                                        </form>

                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette actualité ?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="news_id" value="<?php echo (int) $news['id']; ?>">
                                            <button type="submit" style="background:#ef4444;color:#fff;border:none;padding:10px 14px;border-radius:10px;cursor:pointer;font-weight:700;">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p style="margin-top:20px; display:flex; gap:14px; flex-wrap:wrap;">
            <a href="../Urban Center.html">Retour au site public</a>
            <a href="logout.php">Se déconnecter</a>
        </p>
    </div>
</body>
</html>
