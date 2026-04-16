<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
logoutUser();
header('Location: ' . SITE_URL . '/pages/login.php');
exit();
?>
