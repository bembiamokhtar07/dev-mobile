<?php
declare(strict_types=1);
require __DIR__ . '/../config.php';

unset($_SESSION['admin_logged']);
header('Location: login.php');
exit;

