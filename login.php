<?php
require_once 'config.php';
require_once 'classes/AuthManager.php';

$auth = new AuthManager($db);

if ($auth->isLoggedIn()) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($auth->login($username, $password)) {
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — NenaCare Admin</title>
    <meta name="description" content="Halaman login untuk admin NenaCare K3 Incident Reporting System.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="shape shape-1"></div>
<div class="shape shape-2"></div>

<div class="login-wrapper">
    <div class="login-box">
        <div class="logo">
            <h1><i class="bi bi-shield-check"></i> NenaCare</h1>
            <p>Admin Control Panel</p>
        </div>

        <div class="glass-panel">
            <h2 class="panel-heading"><i class="bi bi-lock-fill"></i> Masuk ke Dashboard</h2>
            
            <?php if ($error): ?>
                <div class="glass-alert danger" style="margin-top: 0; margin-bottom: 1.5rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="Masukkan username" autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required placeholder="Masukkan password">
                </div>

                <label class="checkbox-wrapper" for="show_pass" style="margin-bottom: 1.5rem;">
                    <input type="checkbox" id="show_pass">
                    <div class="checkbox-label">Tampilkan password</div>
                </label>

                <button type="submit" class="btn-submit"><i class="bi bi-box-arrow-in-right"></i> Login</button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; transition: color 0.3s;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Pelaporan
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('show_pass').addEventListener('change', function() {
    const pw = document.getElementById('password');
    pw.type = this.checked ? 'text' : 'password';
});
</script>

</body>
</html>
