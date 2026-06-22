<?php
require_once 'config.php';
require_once 'classes/AuthManager.php';
require_once 'classes/ReportManager.php';

$auth = new AuthManager($db);
$manager = new ReportManager($db);
$isAdmin = $auth->isLoggedIn();
$user = $isAdmin ? $auth->getCurrentUser() : null;

// Get report ID
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$report = $manager->getReportById($id);
if (!$report) {
    header('Location: index.php');
    exit;
}

$noteMessage = '';

// Handle status update (admin only)
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $newStatus = $_POST['status'];
        $manager->updateStatus($id, $newStatus);
        header("Location: detail.php?id=$id&updated=1");
        exit;
    }
    if ($_POST['action'] === 'add_note') {
        $catatan = trim($_POST['catatan'] ?? '');
        if (!empty($catatan)) {
            $manager->addNote($id, $user['id'], $catatan);
            header("Location: detail.php?id=$id&noted=1");
            exit;
        }
    }
}

// Refresh report data after potential update
$report = $manager->getReportById($id);
$notes = $manager->getNotesByReport($id);

$isAnonim = isset($report['is_anonim']) && $report['is_anonim'];
$displayName = $isAnonim ? '🕵️ Pelapor Anonim' : htmlspecialchars($report['nama_pelapor'] ?? '-');
$displayType = $isAnonim ? 'Identitas Dirahasiakan' : htmlspecialchars($report['tipe_pelapor'] ?? '-');

$prioBadge = 'badge-normal';
if ($report['prioritas'] === 'Tinggi') $prioBadge = 'badge-tinggi';
elseif ($report['prioritas'] === 'Rendah') $prioBadge = 'badge-rendah';

$statusBadge = 'badge-menunggu';
if ($report['status'] === 'Diproses') $statusBadge = 'badge-diproses';
elseif ($report['status'] === 'Selesai') $statusBadge = 'badge-selesai';

// Timeline logic
$timelineLapor = date('d M Y, H:i', strtotime($report['waktu_lapor']));
$statusVal = $report['status'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan #<?= $id ?> — NenaCare</title>
    <meta name="description" content="Detail laporan insiden K3 #<?= $id ?> di Nena Cafe.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="shape shape-1"></div>
<div class="shape shape-2"></div>

<?php if ($isAdmin): ?>
<!-- Navbar for admin -->
<nav class="navbar">
    <a href="admin.php" class="nav-brand"><i class="bi bi-shield-check"></i> NenaCare</a>
    <ul class="nav-links">
        <li><a href="admin.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
        <li><a href="index.php"><i class="bi bi-megaphone-fill"></i> Pelaporan</a></li>
    </ul>
    <div class="nav-user">
        <div class="nav-user-info">
            <div class="nav-user-name"><?= htmlspecialchars($user['username']) ?></div>
            <div class="nav-user-role"><?= htmlspecialchars($user['role']) ?></div>
        </div>
        <div class="nav-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
        <a href="logout.php" class="btn btn-outline btn-sm"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</nav>
<?php endif; ?>

<div class="detail-wrapper" style="animation: fadeInUp 0.5s ease;">

    <!-- Success messages -->
    <?php if (isset($_GET['updated'])): ?>
        <div class="glass-alert success" style="margin-bottom: 1.5rem;"><i class="bi bi-check-circle-fill"></i> Status berhasil diperbarui!</div>
    <?php endif; ?>
    <?php if (isset($_GET['noted'])): ?>
        <div class="glass-alert success" style="margin-bottom: 1.5rem;"><i class="bi bi-check-circle-fill"></i> Catatan berhasil ditambahkan!</div>
    <?php endif; ?>

    <!-- Back button -->
    <div style="margin-bottom: 1.5rem;">
        <a href="<?= $isAdmin ? 'admin.php' : 'index.php' ?>" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <!-- Header -->
    <div class="glass-panel" style="margin-bottom: 1.5rem;">
        <div class="detail-header">
            <div>
                <div class="detail-id">LAPORAN #<?= str_pad($report['id_laporan'], 4, '0', STR_PAD_LEFT) ?></div>
                <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.8rem; font-weight: 700; color: #fff; margin: 0.3rem 0 0;">
                    <?= htmlspecialchars($report['kategori_masalah']) ?>
                </h1>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="badge <?= $prioBadge ?>" style="font-size: 0.85rem; padding: 6px 12px;"><?= htmlspecialchars($report['prioritas']) ?></span>
                <span class="badge <?= $statusBadge ?>" style="font-size: 0.85rem; padding: 6px 12px;"><?= htmlspecialchars($report['status']) ?></span>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="detail-grid">
            <div class="detail-item">
                <label><i class="bi bi-person-fill"></i> Pelapor</label>
                <span><?= $displayName ?></span>
            </div>
            <div class="detail-item">
                <label><i class="bi bi-people-fill"></i> Tipe Pelapor</label>
                <span><?= $displayType ?></span>
            </div>
            <div class="detail-item">
                <label><i class="bi bi-geo-alt-fill"></i> Lokasi Kejadian</label>
                <span><?= htmlspecialchars($report['lokasi_kejadian']) ?></span>
            </div>
            <div class="detail-item">
                <label><i class="bi bi-clock-fill"></i> Waktu Lapor</label>
                <span><?= date('d M Y, H:i:s', strtotime($report['waktu_lapor'])) ?></span>
            </div>
        </div>

        <!-- Description -->
        <div style="margin-top: 0.5rem;">
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;"><i class="bi bi-chat-text-fill"></i> Deskripsi Kejadian</label>
            <div class="rc-desc" style="margin-bottom: 0;">
                "<?= htmlspecialchars($report['deskripsi_kejadian']) ?>"
            </div>
        </div>
    </div>

    <!-- AI Analysis -->
    <div class="glass-panel" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, rgba(79, 172, 254, 0.03), rgba(79, 172, 254, 0.06)); border-color: rgba(79, 172, 254, 0.15);">
        <h2 class="panel-heading" style="color: var(--primary);"><i class="bi bi-robot"></i> Analisis AI <span class="badge <?= $prioBadge ?>" style="margin-left: 8px;"><?= htmlspecialchars($report['prioritas']) ?></span></h2>
        <p style="font-size: 1.05rem; line-height: 1.7; color: var(--text-main); margin: 0;">
            <?= htmlspecialchars($report['saran_ai']) ?>
        </p>
    </div>

    <!-- Timeline & Status Update -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Timeline -->
        <div class="glass-panel">
            <h2 class="panel-heading"><i class="bi bi-clock-history"></i> Timeline Status</h2>
            <div class="timeline">
                <div class="timeline-item done">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">📋 Dilaporkan</div>
                    <div class="timeline-time"><?= $timelineLapor ?></div>
                </div>
                <div class="timeline-item <?= ($statusVal === 'Diproses' || $statusVal === 'Selesai') ? 'done' : '' ?> <?= $statusVal === 'Diproses' ? 'active' : '' ?>">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">🔧 Diproses</div>
                    <div class="timeline-time"><?= ($statusVal === 'Diproses' || $statusVal === 'Selesai') ? 'Sudah ditangani' : 'Belum diproses' ?></div>
                </div>
                <div class="timeline-item <?= $statusVal === 'Selesai' ? 'done' : '' ?> <?= $statusVal === 'Selesai' ? 'active' : '' ?>">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">✅ Selesai</div>
                    <div class="timeline-time"><?= $statusVal === 'Selesai' ? 'Insiden diselesaikan' : 'Belum selesai' ?></div>
                </div>
            </div>
        </div>

        <!-- Status Update (admin only) -->
        <div class="glass-panel">
            <h2 class="panel-heading"><i class="bi bi-pencil-square"></i> Ubah Status</h2>
            <?php if ($isAdmin): ?>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_status">
                    <div class="form-group">
                        <label class="form-label">Status Saat Ini</label>
                        <select name="status" class="form-control">
                            <option value="Menunggu" <?= $report['status'] === 'Menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                            <option value="Diproses" <?= $report['status'] === 'Diproses' ? 'selected' : '' ?>>🔧 Diproses</option>
                            <option value="Selesai" <?= $report['status'] === 'Selesai' ? 'selected' : '' ?>>✅ Selesai</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100;"><i class="bi bi-check2-circle"></i> Update Status</button>
                </form>
            <?php else: ?>
                <p style="color: var(--text-muted); font-size: 0.95rem;"><i class="bi bi-lock-fill"></i> Login sebagai admin untuk mengubah status.</p>
                <a href="login.php" class="btn btn-outline"><i class="bi bi-box-arrow-in-right"></i> Login Admin</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Notes -->
    <div class="glass-panel">
        <h2 class="panel-heading"><i class="bi bi-journal-text"></i> Catatan Admin</h2>

        <?php if ($isAdmin): ?>
            <form method="POST" action="" style="margin-bottom: 1.5rem;">
                <input type="hidden" name="action" value="add_note">
                <div class="form-group">
                    <textarea name="catatan" class="form-control" required placeholder="Tulis catatan atau tindak lanjut..." rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-accent"><i class="bi bi-plus-circle-fill"></i> Tambah Catatan</button>
            </form>
        <?php endif; ?>

        <div class="notes-list">
            <?php if (empty($notes)): ?>
                <div class="empty-state" style="padding: 2rem;">
                    <i class="bi bi-journal-x" style="font-size: 2rem;"></i>
                    <p>Belum ada catatan untuk laporan ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notes as $note): ?>
                    <div class="note-item">
                        <div class="note-meta">
                            <span class="note-author">
                                <i class="bi bi-person-badge-fill"></i>
                                <?= htmlspecialchars($note['username']) ?>
                                <span class="badge badge-diproses" style="font-size: 0.65rem; padding: 2px 6px;"><?= htmlspecialchars($note['role']) ?></span>
                            </span>
                            <span class="note-time"><i class="bi bi-clock"></i> <?= date('d M Y, H:i', strtotime($note['waktu_catatan'])) ?></span>
                        </div>
                        <div class="note-content"><?= nl2br(htmlspecialchars($note['catatan'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<style>
    @media (max-width: 768px) {
        .detail-wrapper > div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

</body>
</html>
