<?php
require_once __DIR__ . '/../php/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function ensureDefaultAdmin(PDO $pdo): void
{
    ensureSiteEventsTable($pdo);
    ensureNewsTable($pdo);

    $defaultEmail = 'admin@urbancenter.com';

    $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $defaultEmail]);
    $adminId = (int) ($stmt->fetchColumn() ?: 0);

    if ($adminId > 0) {
        // Pour garder un accès démo/stage fiable, on normalise le compte admin par défaut.
        $activate = $pdo->prepare('UPDATE admin_users SET status = :status, role = :role, password_hash = :password_hash WHERE id = :id');
        $activate->execute([
            ':status' => 'active',
            ':role' => 'super_admin',
            ':password_hash' => password_hash('Admin123!', PASSWORD_DEFAULT),
            ':id' => $adminId,
        ]);
        return;
    }

    $create = $pdo->prepare('INSERT INTO admin_users (full_name, email, password_hash, role, status) VALUES (:full_name, :email, :password_hash, :role, :status)');
    $create->execute([
        ':full_name' => 'Administrateur',
        ':email' => $defaultEmail,
        ':password_hash' => password_hash('Admin123!', PASSWORD_DEFAULT),
        ':role' => 'super_admin',
        ':status' => 'active',
    ]);
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_user'])) {
        header('Location: ../pages/login.php');
        exit;
    }
}
