<?php
require_once __DIR__ . '/../../social-cms/config.php';

cmsEnsureManagerAccess();

header('Content-Type: application/json; charset=utf-8');
/*
 * NOTE STAGE:
 * Cette partie (fallback local + logique OpenAI) a été implémentée
 * avec l'aide de GitHub Copilot, puis relue et adaptée au besoin du projet.
 */
function buildLocalGeneration(string $activity, string $audience, string $date, string $platform): array
{
    $platformNormalized = strtolower(trim($platform));
    $dateLabel = date('d/m/Y', strtotime($date));
    $sportEmoji = '🔥';

    if (stripos($activity, 'football') !== false) {
        $sportEmoji = '⚽';
    } elseif (stripos($activity, 'padel') !== false) {
        $sportEmoji = '🎾';
    } elseif (stripos($activity, 'fitness') !== false) {
        $sportEmoji = '💪';
    } elseif (stripos($activity, 'tennis') !== false) {
        $sportEmoji = '🎾';
    }

    $title = sprintf('%s %s à ne pas manquer', $sportEmoji, $activity);

    if (str_contains($platformNormalized, 'instagram')) {
        $content = sprintf(
            "%s %s débarque le %s !\n\nAmbiance énergie + motivation pour %s. Viens avec ton équipe et réserve ton créneau maintenant.\n\nTag ton partenaire de jeu en commentaire 👇",
            $sportEmoji,
            $activity,
            $dateLabel,
            $audience
        );
    } elseif (str_contains($platformNormalized, 'facebook')) {
        $content = sprintf(
            "%s Rendez-vous le %s pour %s à Urban Center.\n\nUn moment sportif et convivial pensé pour %s : animation, bonne ambiance et places limitées.\n\nInscription ouverte dès maintenant ✅",
            $sportEmoji,
            $dateLabel,
            $activity,
            $audience
        );
    } elseif (str_contains($platformNormalized, 'tiktok')) {
        $content = sprintf(
            "%s %s le %s : prêt(e) à relever le défi ?\n\nOn veut voir ton niveau, ton énergie et ton meilleur move.\n\nRéserve vite et viens créer du contenu avec nous 🎥",
            $sportEmoji,
            $activity,
            $dateLabel
        );
    } elseif (str_contains($platformNormalized, 'linkedin')) {
        $content = sprintf(
            "%s Urban Center organise %s le %s.\n\nCette activité vise %s et s'inscrit dans notre démarche de valorisation du sport, de la cohésion et de l'engagement local.\n\nContactez-nous pour participer ou relayer l'événement.",
            $sportEmoji,
            $activity,
            $dateLabel,
            $audience
        );
    } else {
        $content = sprintf(
            "%s %s approche le %s chez Urban Center.\n\nUne ambiance sportive et conviviale pour %s. Réservez dès maintenant pour ne pas rater l'événement.",
            $sportEmoji,
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
    if (stripos($activity, 'tennis') !== false) {
        $hashtags[] = '#Tennis';
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
        'source' => 'generated',
    ];
}

/*
 * NOTE STAGE:
 * L'appel API OpenAI ci-dessous a été construit avec assistance GitHub Copilot
 * (structure de requête, parsing JSON, fallback), puis validé manuellement.
 */
function tryOpenAiGeneration(string $activity, string $audience, string $date, string $platform): ?array
{
    $apiKey = cmsEnv('OPENAI_API_KEY');
    if ($apiKey === null) {
        return null;
    }

    if (!function_exists('curl_init')) {
        return null;
    }

    $model = cmsEnv('OPENAI_MODEL', 'gpt-4o-mini');
    $endpoint = rtrim((string) cmsEnv('OPENAI_API_BASE', 'https://api.openai.com/v1'), '/') . '/chat/completions';
    $dateLabel = date('d/m/Y', strtotime($date));

    $prompt = "Génère un contenu social media en français pour Urban Center.\n"
        . "Retourne uniquement un JSON valide avec les clés: title, content, hashtags.\n"
        . "Contexte:\n"
        . "- Activité: {$activity}\n"
        . "- Public visé: {$audience}\n"
        . "- Date: {$dateLabel}\n"
        . "- Plateforme: {$platform}\n"
        . "Contraintes:\n"
        . "- Ton naturel, humain, engageant, pas robotique\n"
        . "- Utilise un hook en première ligne et un mini appel à l'action\n"
        . "- Adapte le style à la plateforme (Instagram/Facebook/TikTok/LinkedIn)\n"
        . "- content: 3 à 6 lignes\n"
        . "- hashtags: 6 à 12 hashtags séparés par des espaces";

    $body = [
        'model' => $model,
        'temperature' => 0.7,
        'response_format' => ['type' => 'json_object'],
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un copywriter social media en français. Tu écris des posts vivants et orientés engagement.'],
            ['role' => 'user', 'content' => $prompt],
        ],
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
    ]);

    $raw = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $curlError !== '' || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }

    $decoded = json_decode($raw, true);
    $aiContent = $decoded['choices'][0]['message']['content'] ?? '';
    if (!is_string($aiContent) || trim($aiContent) === '') {
        return null;
    }

    $payload = json_decode($aiContent, true);
    if (!is_array($payload)) {
        return null;
    }

    $title = trim((string) ($payload['title'] ?? ''));
    $content = trim((string) ($payload['content'] ?? ''));
    $hashtags = trim((string) ($payload['hashtags'] ?? ''));

    if ($title === '' || $content === '' || $hashtags === '') {
        return null;
    }

    return [
        'title' => $title,
        'content' => $content,
        'hashtags' => $hashtags,
        'source' => 'ai',
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

// Cette orchestration (IA puis fallback local) a été finalisée avec aide GitHub Copilot.
$result = tryOpenAiGeneration($activity, $audience, $date, $platform) ?? buildLocalGeneration($activity, $audience, $date, $platform);

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
