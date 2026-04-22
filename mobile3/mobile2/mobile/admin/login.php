<?php
declare(strict_types=1);
require __DIR__ . '/../config.php';

if (!empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged'] = true;
        header('Location: index.php');
        exit;
    }
    $error = 'Identifiants invalides.';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <main class="container">
        <section class="panel" style="max-width:520px; margin: 80px auto;">
            <h2>Connexion administrateur</h2>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <form method="post">
                <div>
                    <label>Nom utilisateur</label>
                    <input type="text" name="username" required>
                </div>
                <div style="margin-top:12px">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required>
                </div>
                <div style="margin-top:14px" class="row">
                    <button class="btn btn-primary" type="submit">Se connecter</button>
                    <a class="btn btn-dark" href="../index.php">Retour au site</a>
                </div>
            </form>
            <p class="muted" style="margin-top: 10px;">Demo: admin / admin123</p>
        </section>
    </main>
</body>
</html>

