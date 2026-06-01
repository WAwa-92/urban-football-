<?php
session_start();

unset($_SESSION['site_user']);
session_regenerate_id(true);

header('Location: login.php');
exit;
