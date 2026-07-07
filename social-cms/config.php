<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/csrf.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function cmsBasePath(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/'));
    $basePath = preg_replace('#/social-cms(?:/.*)?$#', '', $scriptName);
    $basePath = is_string($basePath) ? rtrim($basePath, '/') : '';

    return $basePath === '/' ? '' : $basePath;
}

function cmsUrl(string $path): string
{
    $normalizedPath = '/' . ltrim($path, '/');
    return cmsBasePath() . $normalizedPath;
}

function cmsNormalizedRole(): string
{
    $role = strtolower(trim((string) ($_SESSION['admin_user']['role'] ?? 'admin')));
    return str_replace([' ', '-'], '_', $role);
}

function cmsPdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = getPDO();
    ensureSocialCmsTables($pdo);

    return $pdo;
}

function cmsCurrentUser(): ?array
{
    return $_SESSION['admin_user'] ?? null;
}

function cmsIsConnected(): bool
{
    return !empty($_SESSION['admin_user']);
}

function cmsRequireAuth(): void
{
    if (!cmsIsConnected()) {
        header('Location: ' . cmsUrl('/social-cms/pages/login.php'));
        exit;
    }
}

function cmsEnsureManagerAccess(): void
{
    cmsRequireAuth();

    $role = cmsNormalizedRole();
    if (!in_array($role, ['admin', 'manager', 'content_manager', 'super_admin', 'superadmin'], true)) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}

function cmsActiveClass(string $activePage, string $currentPage): string
{
    return $activePage === $currentPage ? ' is-active' : '';
}

function cmsFormatBytes(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', ' ') . ' Mo';
    }

    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 1, ',', ' ') . ' Ko';
    }

    return $bytes . ' o';
}

function cmsEnv(string $key, ?string $default = null): ?string
{
    static $localEnv = null;

    $value = getenv($key);

    if ($value === false || $value === '') {
        $serverValue = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        if (is_string($serverValue) && $serverValue !== '') {
            return $serverValue;
        }

        if ($localEnv === null) {
            $localEnv = [];
            $envPath = dirname(__DIR__) . '/.env.local';

            if (is_file($envPath) && is_readable($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if ($trimmedLine === '' || str_starts_with($trimmedLine, '#') || !str_contains($trimmedLine, '=')) {
                        continue;
                    }

                    [$envKey, $envValue] = explode('=', $trimmedLine, 2);
                    $envKey = trim($envKey);
                    $envValue = trim($envValue);

                    if ($envKey !== '') {
                        $localEnv[$envKey] = trim($envValue, "\"'");
                    }
                }
            }
        }

        if (array_key_exists($key, $localEnv) && $localEnv[$key] !== '') {
            return $localEnv[$key];
        }

        return $default;
    }

    return $value;
}

function cmsBufferConfig(): array
{
    return [
        'token' => cmsEnv('BUFFER_API_TOKEN'),
        'organization_id' => cmsEnv('BUFFER_ORGANIZATION_ID'),
        'graphql_endpoint' => cmsEnv('BUFFER_GRAPHQL_ENDPOINT', 'https://api.buffer.com/graphql'),
    ];
}

function cmsAyrshareConfig(): array
{
    return [
        'api_key' => cmsEnv('AYRSHARE_API_KEY'),
        'api_base' => rtrim((string) cmsEnv('AYRSHARE_API_BASE', 'https://app.ayrshare.com/api'), '/'),
    ];
}
