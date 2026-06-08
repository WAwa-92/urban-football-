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
