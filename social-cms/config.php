<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/csrf.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
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
        header('Location: /Urban-Center-main/social-cms/pages/login.php');
        exit;
    }
}

function cmsEnsureManagerAccess(): void
{
    cmsRequireAuth();

    $role = $_SESSION['admin_user']['role'] ?? 'admin';
    if (!in_array($role, ['admin', 'manager', 'content_manager', 'super_admin'], true)) {
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
    $value = getenv($key);

    if ($value === false || $value === '') {
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
