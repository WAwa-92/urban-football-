<?php
require __DIR__ . '/config.php';
requireAdmin();

$pdo = getPDO();
ensureSiteEventsTable($pdo);

$message = '';
$error = '';
$editId = (int) ($_GET['edit'] ?? 0);

$emptyEvent = [
    'id' => 0,
    'title' => '',
    'sport_type' => 'football',
    'date_label' => '',
    'event_date' => '',
    'event_time' => '',
    'location' => '',
    'participants_info' => '',
    'description' => '',
    'detail_1' => '',
    'detail_2' => '',
    'detail_3' => '',
    'cta_label' => "S'inscrire",
    'is_published' => 1,
    'display_order' => 0,
];
$currentEvent = $emptyEvent;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $eventId = (int) ($_POST['event_id'] ?? 0);
        if ($eventId > 0) {
            $stmt = $pdo->prepare('DELETE FROM site_events WHERE id = :id');
            $stmt->execute([':id' => $eventId]);
            $message = 'Événement supprimé.';
        }
    } elseif ($action === 'toggle') {
        $eventId = (int) ($_POST['event_id'] ?? 0);
        if ($eventId > 0) {
            $stmt = $pdo->prepare('UPDATE site_events SET is_published = CASE WHEN is_published = 1 THEN 0 ELSE 1 END WHERE id = :id');
            $stmt->execute([':id' => $eventId]);
            $message = 'Visibilité de l’événement mise à jour.';
        }
    } else {
        $eventId = (int) ($_POST['event_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $sportType = $_POST['sport_type'] ?? 'multi';
        $dateLabel = trim($_POST['date_label'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $participantsInfo = trim($_POST['participants_info'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $detail1 = trim($_POST['detail_1'] ?? '');
        $detail2 = trim($_POST['detail_2'] ?? '');
        $detail3 = trim($_POST['detail_3'] ?? '');
        $ctaLabel = trim($_POST['cta_label'] ?? "S'inscrire");
        $isPublished = isset($_POST['is_published']) ? 1 : 0;
        $displayOrder = (int) ($_POST['display_order'] ?? 0);
        $allowedSportTypes = ['football', 'padel', 'fitness', 'tennis', 'multi', 'other'];

        if ($title === '' || $dateLabel === '' || $description === '' || !in_array($sportType, $allowedSportTypes, true)) {
            $error = 'Titre, sport, libellé de date et description sont obligatoires.';
            $currentEvent = [
                'id' => $eventId,
                'title' => $title,
                'sport_type' => $sportType,
                'date_label' => $dateLabel,
                'event_date' => $eventDate,
                'event_time' => $eventTime,
                'location' => $location,
                'participants_info' => $participantsInfo,
                'description' => $description,
                'detail_1' => $detail1,
                'detail_2' => $detail2,
                'detail_3' => $detail3,
                'cta_label' => $ctaLabel,
                'is_published' => $isPublished,
                'display_order' => $displayOrder,
            ];
        } else {
            if ($eventDate === '') {
                $eventDate = null;
            }
            if ($eventTime === '') {
                $eventTime = null;
            }
            if ($location === '') {
                $location = null;
            }
            if ($participantsInfo === '') {
                $participantsInfo = null;
            }
            if ($detail1 === '') {
                $detail1 = null;
            }
            if ($detail2 === '') {
                $detail2 = null;
            }
            if ($detail3 === '') {
                $detail3 = null;
            }
            if ($ctaLabel === '') {
                $ctaLabel = "S'inscrire";
            }

            if ($eventId > 0) {
                $stmt = $pdo->prepare('UPDATE site_events SET title = :title, sport_type = :sport_type, date_label = :date_label, event_date = :event_date, event_time = :event_time, location = :location, participants_info = :participants_info, description = :description, detail_1 = :detail_1, detail_2 = :detail_2, detail_3 = :detail_3, cta_label = :cta_label, is_published = :is_published, display_order = :display_order WHERE id = :id');
                $stmt->execute([
                    ':title' => $title,
                    ':sport_type' => $sportType,
                    ':date_label' => $dateLabel,
                    ':event_date' => $eventDate,
                    ':event_time' => $eventTime,
                    ':location' => $location,
                    ':participants_info' => $participantsInfo,
                    ':description' => $description,
                    ':detail_1' => $detail1,
                    ':detail_2' => $detail2,
                    ':detail_3' => $detail3,
                    ':cta_label' => $ctaLabel,
                    ':is_published' => $isPublished,
                    ':display_order' => $displayOrder,
                    ':id' => $eventId,
                ]);
                $message = 'Événement mis à jour.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO site_events (title, sport_type, date_label, event_date, event_time, location, participants_info, description, detail_1, detail_2, detail_3, cta_label, is_published, display_order) VALUES (:title, :sport_type, :date_label, :event_date, :event_time, :location, :participants_info, :description, :detail_1, :detail_2, :detail_3, :cta_label, :is_published, :display_order)');
                $stmt->execute([
                    ':title' => $title,
                    ':sport_type' => $sportType,
                    ':date_label' => $dateLabel,
                    ':event_date' => $eventDate,
                    ':event_time' => $eventTime,
                    ':location' => $location,
                    ':participants_info' => $participantsInfo,
                    ':description' => $description,
                    ':detail_1' => $detail1,
                    ':detail_2' => $detail2,
                    ':detail_3' => $detail3,
                    ':cta_label' => $ctaLabel,
                    ':is_published' => $isPublished,
                    ':display_order' => $displayOrder,
                ]);
                $message = 'Événement ajouté.';
            }

            $currentEvent = $emptyEvent;
            $editId = 0;
        }
    }
}

if ($error === '' && $editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM site_events WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $editId]);
    $found = $stmt->fetch();
    if ($found) {
        $currentEvent = $found;
    }
}

$events = $pdo->query('SELECT * FROM site_events ORDER BY display_order ASC, created_at DESC')->fetchAll();
$publishedCount = 0;
foreach ($events as $event) {
    if ((int) $event['is_published'] === 1) {
        $publishedCount++;
    }
}

function adminEventBadge(string $sportType): string
{
    return match ($sportType) {
        'football' => '⚽ Football',
        'padel' => '🏓 Padel',
        'fitness' => '💪 Fitness',
        'tennis' => '🎾 Tennis',
        'multi' => '🏟 Multi-sport',
        default => '📣 Annonce',
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des événements</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:30px; flex-wrap:wrap;">
            <div>
                <h1 class="section-title" style="margin-bottom:10px; text-align:left;">Gestion des événements</h1>
                <p>Ajoutez des annonces, tournois et événements qui apparaîtront automatiquement sur la page publique.</p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="bt" href="dashboard.php">Dashboard</a>
                <a class="bt" href="reservations.php">Réservations</a>
            </div>
        </div>

        <div class="stats">
            <div class="stat-box"><span class="stat-number"><?php echo count($events); ?></span><span class="stat-label">Événements total</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $publishedCount; ?></span><span class="stat-label">Publiés</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo max(0, count($events) - $publishedCount); ?></span><span class="stat-label">Brouillons</span></div>
        </div>

        <div class="contact-form" style="max-width:100%; margin-bottom:30px;">
            <h2 class="form-title"><?php echo $currentEvent['id'] ? 'Modifier un événement' : 'Ajouter un événement'; ?></h2>
            <?php if ($message !== ''): ?>
                <p style="color:#166534; margin-bottom:15px; font-weight:700;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <p style="color:#b91c1c; margin-bottom:15px; font-weight:700;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="event_id" value="<?php echo (int) $currentEvent['id']; ?>">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                    <div class="form-group">
                        <label>Titre *</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars((string) $currentEvent['title']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Sport *</label>
                        <select name="sport_type" required>
                            <?php foreach (['football' => 'Football', 'padel' => 'Padel', 'fitness' => 'Fitness', 'tennis' => 'Tennis', 'multi' => 'Multi-sport', 'other' => 'Autre'] as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo $currentEvent['sport_type'] === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Libellé date *</label>
                        <input type="text" name="date_label" required placeholder="Ex: Chaque vendredi" value="<?php echo htmlspecialchars((string) $currentEvent['date_label']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Date réelle</label>
                        <input type="date" name="event_date" value="<?php echo htmlspecialchars((string) $currentEvent['event_date']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Heure</label>
                        <input type="text" name="event_time" placeholder="Ex: 19h00" value="<?php echo htmlspecialchars((string) $currentEvent['event_time']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Lieu</label>
                        <input type="text" name="location" placeholder="Ex: Terrain principal" value="<?php echo htmlspecialchars((string) $currentEvent['location']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Participants</label>
                        <input type="text" name="participants_info" placeholder="Ex: 4 équipes · 5 joueurs" value="<?php echo htmlspecialchars((string) $currentEvent['participants_info']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Ordre d'affichage</label>
                        <input type="number" name="display_order" min="0" value="<?php echo (int) $currentEvent['display_order']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Libellé bouton</label>
                        <input type="text" name="cta_label" value="<?php echo htmlspecialchars((string) $currentEvent['cta_label']); ?>">
                    </div>
                    <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-top:36px;">
                        <input type="checkbox" name="is_published" id="is_published" <?php echo (int) $currentEvent['is_published'] === 1 ? 'checked' : ''; ?>>
                        <label for="is_published" style="margin:0;">Publier sur le site</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" required style="min-height:120px;"><?php echo htmlspecialchars((string) $currentEvent['description']); ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                    <div class="form-group">
                        <label>Détail 1</label>
                        <input type="text" name="detail_1" placeholder="Ex: 📅 Vendredi à 19h00" value="<?php echo htmlspecialchars((string) $currentEvent['detail_1']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Détail 2</label>
                        <input type="text" name="detail_2" placeholder="Ex: 👥 4 équipes" value="<?php echo htmlspecialchars((string) $currentEvent['detail_2']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Détail 3</label>
                        <input type="text" name="detail_3" placeholder="Ex: 📍 Urban Center" value="<?php echo htmlspecialchars((string) $currentEvent['detail_3']); ?>">
                    </div>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <button type="submit" class="submit-btn" style="width:auto; padding:12px 22px;"><?php echo $currentEvent['id'] ? 'Enregistrer les modifications' : 'Ajouter l\'événement'; ?></button>
                    <?php if ($currentEvent['id']): ?>
                        <a href="events.php" class="bt">Annuler l'édition</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="contact-form" style="max-width:100%;">
            <h2 class="form-title">Liste des événements</h2>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#1e3c72; color:#fff;">
                            <th style="padding:12px; text-align:left;">Titre</th>
                            <th style="padding:12px; text-align:left;">Sport</th>
                            <th style="padding:12px; text-align:left;">Date</th>
                            <th style="padding:12px; text-align:left;">Statut</th>
                            <th style="padding:12px; text-align:left;">Ordre</th>
                            <th style="padding:12px; text-align:left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td style="padding:12px; border-bottom:1px solid #eee; min-width:220px;">
                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                    <div style="font-size:0.9rem; color:#64748b; margin-top:4px;"><?php echo htmlspecialchars(strlen($event['description']) > 90 ? substr($event['description'], 0, 90) . '...' : $event['description']); ?></div>
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars(adminEventBadge($event['sport_type'])); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($event['date_label']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <span style="display:inline-block; padding:6px 10px; border-radius:999px; background:<?php echo (int) $event['is_published'] === 1 ? 'rgba(22,101,52,0.12)' : 'rgba(148,163,184,0.2)'; ?>; color:<?php echo (int) $event['is_published'] === 1 ? '#166534' : '#475569'; ?>; font-weight:700;">
                                        <?php echo (int) $event['is_published'] === 1 ? 'Publié' : 'Brouillon'; ?>
                                    </span>
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo (int) $event['display_order']; ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a class="bt" href="events.php?edit=<?php echo (int) $event['id']; ?>" style="padding:10px 14px; font-size:0.9rem;">Modifier</a>
                                        <form method="POST" style="display:inline-flex;">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>">
                                            <button type="submit" class="submit-btn" style="width:auto; padding:10px 14px;"><?php echo (int) $event['is_published'] === 1 ? 'Dépublier' : 'Publier'; ?></button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Supprimer cet événement ?');" style="display:inline-flex;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>">
                                            <button type="submit" style="padding:10px 14px; border:none; border-radius:999px; background:#b91c1c; color:#fff; font-weight:700; cursor:pointer;">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p style="margin-top:20px;"><a href="logout.php">Se déconnecter</a></p>
    </div>
</body>
</html>
