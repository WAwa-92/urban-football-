<?php
require_once __DIR__ . '/../../social-cms/config.php';
require_once __DIR__ . '/../../social-cms/php/layout.php';

cmsEnsureManagerAccess();

$pdo = cmsPdo();
$roles = ['admin', 'manager', 'content_manager', 'super_admin'];
$statuses = ['active', 'disabled'];
$success = '';
$error = '';
$editingUser = null;

if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT id, full_name, email, role, status FROM admin_users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $editingUser = $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Session expirée. Merci de recharger la page.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'create') {
                $fullName = trim($_POST['full_name'] ?? '');
                $email = strtolower(trim($_POST['email'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $role = $_POST['role'] ?? 'manager';
                $status = $_POST['status'] ?? 'active';

                if ($fullName === '' || $email === '' || $password === '') {
                    throw new RuntimeException('Merci de remplir le nom, l’email et le mot de passe.');
                }

                if (!in_array($role, $roles, true)) {
                    $role = 'manager';
                }

                if (!in_array($status, $statuses, true)) {
                    $status = 'active';
                }

                $check = $pdo->prepare('SELECT id FROM admin_users WHERE email = :email LIMIT 1');
                $check->execute([':email' => $email]);
                if ($check->fetchColumn()) {
                    throw new RuntimeException('Cet email existe déjà.');
                }

                $insert = $pdo->prepare('INSERT INTO admin_users (full_name, email, password_hash, role, status) VALUES (:full_name, :email, :password_hash, :role, :status)');
                $insert->execute([
                    ':full_name' => $fullName,
                    ':email' => $email,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ':role' => $role,
                    ':status' => $status,
                ]);

                $success = 'Compte créé avec succès.';
            }

            if ($action === 'update') {
                $userId = (int) ($_POST['user_id'] ?? 0);
                $fullName = trim($_POST['full_name'] ?? '');
                $email = strtolower(trim($_POST['email'] ?? ''));
                $role = $_POST['role'] ?? 'manager';
                $status = $_POST['status'] ?? 'active';
                $password = trim($_POST['password'] ?? '');

                if ($userId <= 0 || $fullName === '' || $email === '') {
                    throw new RuntimeException('Les champs nom et email sont obligatoires.');
                }

                if (!in_array($role, $roles, true)) {
                    $role = 'manager';
                }

                if (!in_array($status, $statuses, true)) {
                    $status = 'active';
                }

                $check = $pdo->prepare('SELECT id FROM admin_users WHERE email = :email AND id <> :id LIMIT 1');
                $check->execute([':email' => $email, ':id' => $userId]);
                if ($check->fetchColumn()) {
                    throw new RuntimeException('Un autre compte utilise déjà cet email.');
                }

                if ($password !== '') {
                    $update = $pdo->prepare('UPDATE admin_users SET full_name = :full_name, email = :email, password_hash = :password_hash, role = :role, status = :status WHERE id = :id');
                    $update->execute([
                        ':full_name' => $fullName,
                        ':email' => $email,
                        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        ':role' => $role,
                        ':status' => $status,
                        ':id' => $userId,
                    ]);
                } else {
                    $update = $pdo->prepare('UPDATE admin_users SET full_name = :full_name, email = :email, role = :role, status = :status WHERE id = :id');
                    $update->execute([
                        ':full_name' => $fullName,
                        ':email' => $email,
                        ':role' => $role,
                        ':status' => $status,
                        ':id' => $userId,
                    ]);
                }

                $success = 'Compte modifié avec succès.';
            }

            if ($action === 'delete') {
                $userId = (int) ($_POST['user_id'] ?? 0);
                if ($userId <= 0) {
                    throw new RuntimeException('Identifiant invalide.');
                }

                if (!empty($_SESSION['admin_user']['id']) && (int) $_SESSION['admin_user']['id'] === $userId) {
                    throw new RuntimeException('Vous ne pouvez pas supprimer votre propre compte.');
                }

                $delete = $pdo->prepare('DELETE FROM admin_users WHERE id = :id');
                $delete->execute([':id' => $userId]);

                $success = 'Compte supprimé.';
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$users = $pdo->query('SELECT id, full_name, email, role, status, last_login_at FROM admin_users ORDER BY id DESC')->fetchAll();
$editingUserId = $editingUser['id'] ?? null;

cmsRenderHeader('Utilisateurs CMS', 'Créer, modifier et gérer les accès de l’équipe.', 'users');
?>
        <section class="cms-layout-two">
            <article class="cms-card">
                <h3><?php echo $editingUser ? 'Modifier un compte' : 'Créer un compte'; ?></h3>

                <?php if ($success !== ''): ?>
                    <p class="cms-chip success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <p class="cms-chip warning"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <form method="post" class="cms-form" style="margin-top:14px;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="<?php echo $editingUser ? 'update' : 'create'; ?>">
                    <?php if ($editingUser): ?>
                        <input type="hidden" name="user_id" value="<?php echo (int) $editingUser['id']; ?>">
                    <?php endif; ?>

                    <div class="cms-field">
                        <label for="full_name">Nom complet</label>
                        <input id="full_name" name="full_name" required value="<?php echo htmlspecialchars($editingUser['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="cms-field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($editingUser['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="cms-field">
                        <label for="password"><?php echo $editingUser ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe'; ?></label>
                        <input id="password" name="password" type="password" <?php echo $editingUser ? '' : 'required'; ?> placeholder="Laisser vide pour conserver le mot de passe actuel">
                    </div>
                    <div class="cms-field">
                        <label for="role">Rôle</label>
                        <select id="role" name="role">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($editingUser['role'] ?? 'manager') === $role) ? 'selected' : ''; ?>><?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cms-field">
                        <label for="status">Statut</label>
                        <select id="status" name="status">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($editingUser['status'] ?? 'active') === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cms-actions">
                        <button class="cms-button" type="submit"><?php echo $editingUser ? 'Mettre à jour' : 'Créer le compte'; ?></button>
                        <?php if ($editingUser): ?>
                            <a class="cms-button cms-button-ghost" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/admin/users.php'), ENT_QUOTES, 'UTF-8'); ?>">Annuler</a>
                        <?php endif; ?>
                    </div>
                </form>
            </article>

            <article class="cms-card">
                <h3>Accès rapides</h3>
                <p class="cms-muted">Les comptes contenus dans cette section servent à gérer le CMS. Le rôle <strong>content_manager</strong> permet d’ouvrir les contenus sans donner tout le back-office complet.</p>
                <div class="cms-preview-box" style="margin-top:14px;">
                    <strong>Conseil de gestion</strong>
                    <p class="cms-muted" style="margin-bottom:0;">Gardez un seul super_admin, un ou deux managers, et les content_manager pour la partie communication.</p>
                </div>
            </article>
        </section>

        <section class="cms-card" style="margin-top:18px;">
            <h3>Comptes enregistrés</h3>
            <div class="cms-table" style="margin-top:14px;">
                <table>
                    <thead>
                        <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Dernière connexion</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$users): ?>
                            <tr><td colspan="6" class="cms-muted">Aucun compte trouvé.</td></tr>
                        <?php else: foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="cms-chip info"><?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><span class="cms-chip <?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($user['last_login_at'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="cms-actions">
                                        <a class="cms-button cms-button-ghost" href="<?php echo htmlspecialchars(cmsUrl('/social-cms/admin/users.php'), ENT_QUOTES, 'UTF-8'); ?>?edit=<?php echo (int) $user['id']; ?>">Modifier</a>
                                        <form method="post" onsubmit="return confirm('Supprimer ce compte ?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                            <button class="cms-button" type="submit" style="background:#b91c1c;">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
<?php
cmsRenderFooter();
