<?php
require_once __DIR__ . '/../../social-cms/config.php';

cmsEnsureManagerAccess();

header('Content-Type: application/json; charset=utf-8');
function buildLocalGeneration(string $activity, string $audience, string $date, string $platform): array
{
    $platformNormalized = strtolower(trim($platform));
    $dateLabel = date('d/m/Y', strtotime($date));
    $title = sprintf('%s à ne pas manquer', $activity);

    if (str_contains($platformNormalized, 'instagram')) {
        $content = sprintf(
            "%s a lieu le %s à Urban Center.\n\nCette activité est prévue pour %s. Réservez votre créneau dès maintenant.",
            $activity,
            $dateLabel,
            $audience
        );
    } elseif (str_contains($platformNormalized, 'facebook')) {
        $content = sprintf(
            "Rendez-vous le %s pour %s à Urban Center.\n\nActivité prévue pour %s. Inscription ouverte dès maintenant.",
            $dateLabel,
            $activity,
            $audience
        );
    } elseif (str_contains($platformNormalized, 'tiktok')) {
        $content = sprintf(
            "%s le %s à Urban Center.\n\nActivité ouverte à %s. Réservation disponible.",
            $activity,
            $dateLabel,
            $audience
        );
    } elseif (str_contains($platformNormalized, 'linkedin')) {
        $content = sprintf(
            "Urban Center organise %s le %s.\n\nCette activité vise %s et s'inscrit dans notre programme sportif local.\n\nContactez-nous pour participer.",
            $activity,
            $dateLabel,
            $audience
        );
    } else {
        $content = sprintf(
            "%s a lieu le %s chez Urban Center.\n\nActivité destinée à %s. Réservez dès maintenant.",
            $activity,
            $dateLabel,
            $audience
        );
    }

    $hashtags = ['#UrbanCenter', '#Sport', '#Evenement', '#Tunisie'];

    if (stripos($activity, 'football') !== false) {
        $hashtags[] = '#Football';
        $hashtags[] = '#Tournoi';
    }
    if (stripos($activity, 'padel') !== false) {
        $hashtags[] = '#Padel';
    }
    if (stripos($activity, 'fitness') !== false) {
        $hashtags[] = '#Fitness';
        $hashtags[] = '#Training';
    }
    if (str_contains($platformNormalized, 'instagram')) {
        $hashtags[] = '#Instagram';
        $hashtags[] = '#Reel';
    }
    if (str_contains($platformNormalized, 'facebook')) {
        $hashtags[] = '#Facebook';
    }
    if (str_contains($platformNormalized, 'tiktok')) {
        $hashtags[] = '#TikTok';
    }
    if (str_contains($platformNormalized, 'linkedin')) {
        $hashtags[] = '#LinkedIn';
    }

    return [
        'title' => $title,
        'content' => $content,
        'hashtags' => implode(' ', array_unique($hashtags)),
        'source' => 'template',
    ];
}

$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$activity = trim($payload['activity'] ?? '');
$audience = trim($payload['audience'] ?? '');
$date = trim($payload['date'] ?? date('Y-m-d'));
$platform = trim($payload['platform'] ?? 'Instagram');

if (!isValidCsrfToken($payload['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['error' => 'Session expirée. Merci de recharger la page.']);
    exit;
}

if ($activity === '' || $audience === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Merci de renseigner l’activité et le public visé.']);
    exit;
}

$result = buildLocalGeneration($activity, $audience, $date, $platform);

$pdo = cmsPdo();
$insert = $pdo->prepare('INSERT INTO cms_social_posts (post_title, content, hashtags, platform, activity, audience, source, created_by) VALUES (:post_title, :content, :hashtags, :platform, :activity, :audience, :source, :created_by)');
$insert->execute([
    ':post_title' => $result['title'],
    ':content' => $result['content'],
    ':hashtags' => $result['hashtags'],
    ':platform' => $platform,
    ':activity' => $activity,
    ':audience' => $audience,
    ':source' => $result['source'],
    ':created_by' => $_SESSION['admin_user']['email'] ?? null,
]);

echo json_encode([
    'title' => $result['title'],
    'content' => $result['content'],
    'hashtags' => $result['hashtags'],
    'source' => $result['source'],
]);
