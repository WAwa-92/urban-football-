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

$cfg = cmsBufferConfig();
if (empty($cfg['token'])) {
    http_response_code(422);
    echo json_encode(['error' => 'BUFFER_API_TOKEN manquant dans l’environnement.']);
    exit;
}

// NOTE STAGE: Intégration GraphQL Buffer réalisée avec assistance GitHub Copilot puis testée via mutation createIdea.

$organizationId = trim((string) ($payload['organization_id'] ?? $cfg['organization_id'] ?? ''));
if ($organizationId === '') {
    http_response_code(422);
    echo json_encode(['error' => 'organization_id est requis.']);
    exit;
}

$postId = (int) ($payload['post_id'] ?? 0);
$title = trim((string) ($payload['title'] ?? ''));
$text = trim((string) ($payload['text'] ?? ''));

$pdo = cmsPdo();

if ($postId > 0 && ($title === '' || $text === '')) {
    $stmt = $pdo->prepare('SELECT post_title, content FROM cms_social_posts WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $postId]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Post introuvable.']);
        exit;
    }

    if ($title === '') {
        $title = (string) $row['post_title'];
    }
    if ($text === '') {
        $text = (string) $row['content'];
    }
}

if ($title === '' || $text === '') {
    http_response_code(422);
    echo json_encode(['error' => 'title et text sont requis.']);
    exit;
}

$mutation = <<<'GQL'
mutation CreateIdea($organizationId: ID!, $title: String!, $text: String!) {
  createIdea(input: {
    organizationId: $organizationId,
    content: {
      title: $title,
      text: $text
    }
  }) {
    ... on Idea {
      id
      content {
        title
        text
      }
    }
  }
}
GQL;

$graphqlBody = [
    'query' => $mutation,
    'variables' => [
        'organizationId' => $organizationId,
        'title' => $title,
        'text' => $text,
    ],
];

$ch = curl_init((string) $cfg['graphql_endpoint']);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $cfg['token'],
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($graphqlBody, JSON_UNESCAPED_UNICODE),
]);

$raw = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($raw === false || $curlError !== '') {
    http_response_code(502);
    echo json_encode(['error' => 'Erreur Buffer (cURL): ' . $curlError]);
    exit;
}

$decoded = json_decode($raw, true);
if (!is_array($decoded)) {
    http_response_code(502);
    echo json_encode(['error' => 'Réponse Buffer invalide.']);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300 || !empty($decoded['errors'])) {
    http_response_code(502);
    echo json_encode([
        'error' => 'Buffer a rejeté la requête.',
        'details' => $decoded['errors'] ?? $decoded,
    ]);
    exit;
}

$idea = $decoded['data']['createIdea'] ?? null;
if (!is_array($idea) || empty($idea['id'])) {
    http_response_code(502);
    echo json_encode(['error' => 'Idea non retournée par Buffer.', 'raw' => $decoded]);
    exit;
}

if ($postId > 0) {
    if (!tableHasColumn($pdo, 'cms_social_posts', 'buffer_idea_id')) {
        $pdo->exec("ALTER TABLE cms_social_posts ADD COLUMN buffer_idea_id VARCHAR(80) NULL AFTER source");
    }
    if (!tableHasColumn($pdo, 'cms_social_posts', 'buffer_synced_at')) {
        $pdo->exec("ALTER TABLE cms_social_posts ADD COLUMN buffer_synced_at TIMESTAMP NULL DEFAULT NULL AFTER buffer_idea_id");
    }

    $save = $pdo->prepare('UPDATE cms_social_posts SET buffer_idea_id = :buffer_idea_id, buffer_synced_at = NOW() WHERE id = :id');
    $save->execute([
        ':buffer_idea_id' => (string) $idea['id'],
        ':id' => $postId,
    ]);
}

echo json_encode([
    'success' => true,
    'idea' => $idea,
    'source_post_id' => $postId > 0 ? $postId : null,
]);
