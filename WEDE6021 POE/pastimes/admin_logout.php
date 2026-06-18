<?php
require_once 'includes/session.php';
unset($_SESSION['admin_id'],$_SESSION['admin_name'],$_SESSION['admin_email']);
redirect('admin_login.php');
