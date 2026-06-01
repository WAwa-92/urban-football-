<?php
require __DIR__ . '/../php/db.php';

session_start();

function ensureDefaultAdmin(PDO $pdo): void
{
    ensureSiteEventsTable($pdo);

    $defaultEmail = 'admin@urbancenter.com';

    $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $defaultEmail]);
    $adminId = (int) ($stmt->fetchColumn() ?: 0);

    if ($adminId > 0) {
        $activate = $pdo->prepare('UPDATE admin_users SET status = :status WHERE id = :id');
        $activate->execute([
            ':status' => 'active',
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
        header('Location: login.php');
        exit;
    }
}
