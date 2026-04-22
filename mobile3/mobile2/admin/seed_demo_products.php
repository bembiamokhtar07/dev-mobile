<?php
declare(strict_types=1);
require __DIR__ . '/../config.php';

if (empty($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === '1') {
    $skip = __DIR__ . '/../data/.skip_demo_autofill';
    if (is_file($skip)) {
        unlink($skip);
    }
    insert_demo_products($pdo);
    $_SESSION['flash'] = 'Les 10 produits de demonstration ont ete ajoutes au catalogue.';
    header('Location: index.php');
    exit;
}
header('Location: index.php');
exit;
