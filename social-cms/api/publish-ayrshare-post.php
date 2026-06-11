<?php
require_once __DIR__ . '/../../social-cms/config.php';

cmsEnsureManagerAccess();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (!isValidCsrfToken($payload['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['error' => 'Session expirée. Merci de recharger la page.']);
    exit;
}

if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL indisponible sur ce serveur.']);
    exit;
}

$cfg = cmsAyrshareConfig();
if (empty($cfg['api_key'])) {
    http_response_code(422);
    echo json_encode(['error' => 'AYRSHARE_API_KEY manquant dans l’environnement.']);
    exit;
}

$platforms = $payload['platforms'] ?? ['facebook'];
if (!is_array($platforms) || $platforms === []) {
    http_response_code(422);
    echo json_encode(['error' => 'platforms doit être un tableau non vide.']);
    exit;
}

$postId = (int) ($payload['post_id'] ?? 0);
$postText = trim((string) ($payload['post'] ?? ''));

$pdo = cmsPdo();
if ($postId > 0 && $postText === '') {
    $stmt = $pdo->prepare('SELECT post_title, content, hashtags FROM cms_social_posts WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $postId]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Post introuvable.']);
        exit;
    }

    $postText = trim($row['post_title'] . "\n\n" . $row['content'] . "\n\n" . $row['hashtags']);
}

if ($postText === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Le contenu du post est requis (champ post ou post_id).']);
    exit;
}

$body = [
    'post' => $postText,
    'platforms' => array_values(array_unique(array_map('strval', $platforms))),
];

if (!empty($payload['mediaUrls']) && is_array($payload['mediaUrls'])) {
    $body['mediaUrls'] = $payload['mediaUrls'];
}

if (!empty($payload['scheduleDate'])) {
    $body['scheduleDate'] = (string) $payload['scheduleDate'];
}

$endpoint = $cfg['api_base'] . '/post';
$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $cfg['api_key'],
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
]);

$raw = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($raw === false || $curlError !== '') {
    http_response_code(502);
    echo json_encode(['error' => 'Erreur Ayrshare (cURL): ' . $curlError]);
    exit;
}

$decoded = json_decode($raw, true);
if (!is_array($decoded)) {
    http_response_code(502);
    echo json_encode(['error' => 'Réponse Ayrshare invalide.', 'raw' => $raw]);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300 || isset($decoded['status']) && (string) $decoded['status'] === 'error') {
    http_response_code(502);
    echo json_encode([
        'error' => 'Ayrshare a rejeté la requête.',
        'details' => $decoded,
    ]);
    exit;
}

$primaryId = $decoded['id'] ?? ($decoded['postIds'][0]['id'] ?? null);

if ($postId > 0) {
    if (!tableHasColumn($pdo, 'cms_social_posts', 'ayrshare_post_id')) {
        $pdo->exec("ALTER TABLE cms_social_posts ADD COLUMN ayrshare_post_id VARCHAR(120) NULL AFTER buffer_synced_at");
    }
    if (!tableHasColumn($pdo, 'cms_social_posts', 'ayrshare_synced_at')) {
        $pdo->exec("ALTER TABLE cms_social_posts ADD COLUMN ayrshare_synced_at TIMESTAMP NULL DEFAULT NULL AFTER ayrshare_post_id");
    }

    $save = $pdo->prepare('UPDATE cms_social_posts SET ayrshare_post_id = :ayrshare_post_id, ayrshare_synced_at = NOW() WHERE id = :id');
    $save->execute([
        ':ayrshare_post_id' => $primaryId ? (string) $primaryId : null,
        ':id' => $postId,
    ]);
}

echo json_encode([
    'success' => true,
    'provider' => 'ayrshare',
    'response' => $decoded,
    'source_post_id' => $postId > 0 ? $postId : null,
]);
