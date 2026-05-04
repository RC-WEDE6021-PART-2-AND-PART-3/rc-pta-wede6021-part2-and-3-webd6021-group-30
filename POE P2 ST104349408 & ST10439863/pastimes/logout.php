<?php
require_once 'includes/session.php';
unset($_SESSION['user_id'],$_SESSION['user_name'],$_SESSION['user_email'],$_SESSION['user_status']);
redirect('login.php');
