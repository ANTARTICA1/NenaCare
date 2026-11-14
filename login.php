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
        $error = 'Kombinasi nama pengguna atau kata sandi tidak valid.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Petugas — NenaCare K3</title>
    <meta name="description" content="Autentikasi masuk petugas dan auditor K3 NenaCare.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-box">
        <div class="login-header-block">
            <div class="login-icon-badge">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1>NenaCare K3</h1>
            <p>Otorisasi Akses Pengawas Keselamatan Kerja</p>
        </div>

        <div class="login-card">
            <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-subtle);">
                <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-key" style="color: var(--primary);"></i> Autentikasi Sistem
                </h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert-box danger" style="margin-top: 0; margin-bottom: 1.25rem;">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Nama Pengguna (Username)</label>
                    <input type="text" name="username" class="form-control" required placeholder="Masukkan nama pengguna" autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Kata Sandi (Password)</label>
                    <input type="password" name="password" id="password" class="form-control" required placeholder="Masukkan kata sandi">
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                    <input type="checkbox" id="show_pass" style="width: 16px; height: 16px; accent-color: #0f172a; cursor: pointer;">
                    <label for="show_pass" style="font-size: 0.85rem; color: var(--text-secondary); cursor: pointer; user-select: none;">Perlihatkan kata sandi</label>
                </div>

                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk ke Konsol
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-subtle);">
                <a href="index.php" style="color: var(--text-secondary); font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Portal Pelaporan
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
