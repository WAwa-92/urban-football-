<?php
require_once __DIR__ . '/../../social-cms/config.php';

cmsEnsureManagerAccess();

header('Content-Type: application/json; charset=utf-8');

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

$dateLabel = date('d/m/Y', strtotime($date));
$title = $activity . ' — ' . $platform;
$content = sprintf(
    "%s approche le %s chez Urban Center.\n\nUne proposition simple pour %s, avec une ambiance sportive et conviviale qui reste naturelle.",
    $activity,
    $dateLabel,
    $audience
);

$hashtags = ['#UrbanCenter', '#Sport', '#Evenement'];

if (stripos($activity, 'football') !== false) {
    $hashtags[] = '#Football';
}
if (stripos($activity, 'padel') !== false) {
    $hashtags[] = '#Padel';
}
if (stripos($activity, 'fitness') !== false) {
    $hashtags[] = '#Fitness';
}
if (stripos($platform, 'instagram') !== false) {
    $hashtags[] = '#Instagram';
}

$pdo = cmsPdo();
$insert = $pdo->prepare('INSERT INTO cms_social_posts (post_title, content, hashtags, platform, activity, audience, source, created_by) VALUES (:post_title, :content, :hashtags, :platform, :activity, :audience, :source, :created_by)');
$insert->execute([
    ':post_title' => $title,
    ':content' => $content,
    ':hashtags' => implode(' ', array_unique($hashtags)),
    ':platform' => $platform,
    ':activity' => $activity,
    ':audience' => $audience,
    ':source' => 'generated',
    ':created_by' => $_SESSION['admin_user']['email'] ?? null,
]);

echo json_encode([
    'title' => $title,
    'content' => $content,
    'hashtags' => implode(' ', array_unique($hashtags)),
]);
