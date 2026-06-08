<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

unset($_SESSION['admin_user']);
session_destroy();

header('Location: /Urban-Center-main/social-cms/pages/login.php');
exit;
