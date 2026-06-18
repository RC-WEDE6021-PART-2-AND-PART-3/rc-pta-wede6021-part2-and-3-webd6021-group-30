<?php
require_once 'includes/session.php';
unset($_SESSION['user_id'],$_SESSION['user_name'],$_SESSION['user_email'],$_SESSION['user_status']);

// Only allow redirecting to a known, local, non-admin-protected page to avoid open redirects.
$allowedRedirects = ['login.php', 'admin_login.php', 'index.php'];
$target = $_GET['redirect'] ?? 'login.php';
if (!in_array($target, $allowedRedirects, true)) {
    $target = 'login.php';
}

redirect($target);
