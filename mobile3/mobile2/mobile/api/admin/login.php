<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Method not allowed.', 405);
}

$data = api_read_json();
$username = trim((string) ($data['username'] ?? ''));
$password = (string) ($data['password'] ?? '');

if ($username === '' || $password === '') {
    api_error('Identifiants obligatoires.');
}

$admin = admin_find_by_username($pdo, $username);
if (!$admin || !password_verify($password, (string) $admin['password_hash'])) {
    api_error('Identifiants invalides.', 401);
}

$token = admin_create_token($pdo, (int) $admin['id']);
api_ok([
    'token' => $token,
    'admin' => [
        'id' => (int) $admin['id'],
        'username' => (string) $admin['username'],
    ],
]);
