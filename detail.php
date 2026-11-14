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

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_report') {
        $result = $manager->deleteReport($id);
        if ($result) {
            header("Location: admin.php?deleted=1");
        } else {
            header("Location: detail.php?id=$id&delete_error=1");
        }
        exit;
    }
    if ($_POST['action'] === 'quick_status') {
        $newStatus = $_POST['status'] ?? '';
        if (in_array($newStatus, ['Menunggu', 'Diproses', 'Selesai'])) {
            $manager->updateStatus($id, $newStatus);
            header("Location: detail.php?id=$id&updated=1");
            exit;
        }
    }
    if ($_POST['action'] === 'update_details') {
        $newStatus = $_POST['status'];
        $newPriority = $_POST['priority'];
        $manager->updateStatus($id, $newStatus);
        $manager->updatePriority($id, $newPriority);
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

$report = $manager->getReportById($id);
$notes = $manager->getNotesByReport($id);

$isAnonim = isset($report['is_anonim']) && $report['is_anonim'];
$displayName = $isAnonim ? 'Pelapor Anonim' : htmlspecialchars($report['nama_pelapor'] ?? '-');
$displayType = $isAnonim ? 'Identitas Dirahasiakan' : htmlspecialchars($report['tipe_pelapor'] ?? '-');

$prioBadge = 'badge-normal';
if ($report['prioritas'] === 'Tinggi') $prioBadge = 'badge-tinggi';
elseif ($report['prioritas'] === 'Rendah') $prioBadge = 'badge-rendah';

$statusBadge = 'badge-menunggu';
if ($report['status'] === 'Diproses') $statusBadge = 'badge-diproses';
elseif ($report['status'] === 'Selesai') $statusBadge = 'badge-selesai';

$timelineLapor = date('d M Y, H:i', strtotime($report['waktu_lapor']));
$statusVal = $report['status'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkas Insiden #LAP-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?> — NenaCare K3</title>
    <meta name="description" content="Detail investigasi insiden keselamatan kerja K3 #LAP-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?> di Nena Cafe.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        @media print {
            .navbar, .breadcrumb-nav, .detail-actions-bar, .no-print, form { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .detail-card { border: 1px solid #ccc !important; box-shadow: none !important; }
            .badge { border: 1px solid #000 !important; color: #000 !important; background: #eee !important; }
        }
    </style>
</head>
<body>

<?php if ($isAdmin): ?>
<nav class="navbar no-print">
    <a href="admin.php" class="nav-brand">
        <i class="bi bi-shield-check"></i>
        <span>NenaCare K3</span>
    </a>
    <ul class="nav-links">
        <li><a href="admin.php"><i class="bi bi-grid-1x2"></i> Konsol Pemantauan</a></li>
        <li><a href="index.php"><i class="bi bi-megaphone"></i> Portal Publik</a></li>
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

<div class="detail-wrapper">

    <div class="breadcrumb-nav no-print">
        <a href="<?= $isAdmin ? 'admin.php' : 'index.php' ?>">
            <i class="bi bi-arrow-left"></i> <?= $isAdmin ? 'Konsol Pemantauan' : 'Beranda Pelaporan' ?>
        </a>
        <span>/</span>
        <span>Berkas #LAP-<?= str_pad($report['id_laporan'], 4, '0', STR_PAD_LEFT) ?></span>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert-box success" style="margin-bottom: 1.5rem;">
            <i class="bi bi-check-circle"></i> Status dan prioritas insiden berhasil diperbarui.
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['noted'])): ?>
        <div class="alert-box success" style="margin-bottom: 1.5rem;">
            <i class="bi bi-check-circle"></i> Catatan audit petugas berhasil ditambahkan ke riwayat.
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['delete_error'])): ?>
        <div class="alert-box danger" style="margin-bottom: 1.5rem;">
            <i class="bi bi-exclamation-triangle"></i> Gagal menghapus berkas laporan. Silakan coba kembali.
        </div>
    <?php endif; ?>

    <div class="detail-card">
        <div class="detail-header">
            <div>
                <div class="detail-id">BERKAS INVESTIGASI #LAP-<?= str_pad($report['id_laporan'], 4, '0', STR_PAD_LEFT) ?></div>
                <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin: 0.35rem 0 0; letter-spacing: -0.02em;">
                    <?= htmlspecialchars($report['kategori_masalah']) ?>
                </h1>
            </div>
            <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                <span class="badge <?= $prioBadge ?>" style="font-size: 0.825rem; padding: 0.35rem 0.75rem;">
                    Prioritas: <?= htmlspecialchars($report['prioritas']) ?>
                </span>
                <span class="badge <?= $statusBadge ?>" style="font-size: 0.825rem; padding: 0.35rem 0.75rem;">
                    Status: <?= htmlspecialchars($report['status']) ?>
                </span>
                <button type="button" onclick="window.print()" class="btn btn-outline btn-sm no-print" title="Cetak Berkas">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <label><i class="bi bi-person"></i> Nama Pelapor</label>
                <span><?= $displayName ?></span>
            </div>
            <div class="detail-item">
                <label><i class="bi bi-people"></i> Klasifikasi Peran</label>
                <span><?= $displayType ?></span>
            </div>
            <div class="detail-item">
                <label><i class="bi bi-geo-alt"></i> Lokasi Kejadian</label>
                <span><?= htmlspecialchars($report['lokasi_kejadian']) ?></span>
            </div>
            <div class="detail-item">
                <label><i class="bi bi-clock"></i> Waktu Pelaporan</label>
                <span class="mono-cell" style="color: var(--text-primary); font-size: 0.95rem; font-weight: 600;"><?= date('d M Y, H:i:s', strtotime($report['waktu_lapor'])) ?></span>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.4rem;">
                <i class="bi bi-card-text"></i> Uraian Kronologi Kejadian
            </label>
            <div class="rc-desc" style="font-size: 1rem; margin-bottom: 0;">
                "<?= htmlspecialchars($report['deskripsi_kejadian']) ?>"
            </div>
        </div>

        <div class="rc-ai-box">
            <div class="rc-ai-label">
                <i class="bi bi-cpu"></i> Rekomendasi Keselamatan K3 (AI Assistant)
            </div>
            <div class="rc-ai-content" style="font-size: 0.95rem;">
                <?= htmlspecialchars($report['saran_ai']) ?>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.75rem;">
        <div class="detail-card" style="margin-bottom: 0;">
            <div class="panel-header" style="margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-subtle);">
                <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-clock-history" style="color: var(--primary);"></i> Tahapan Progres Insiden
                </h2>
            </div>
            <div class="timeline">
                <div class="timeline-item done">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Laporan Diterima</div>
                    <div class="timeline-time"><?= $timelineLapor ?></div>
                </div>
                <div class="timeline-item <?= ($statusVal === 'Diproses' || $statusVal === 'Selesai') ? 'done' : '' ?> <?= $statusVal === 'Diproses' ? 'active' : '' ?>">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Penanganan Lapangan</div>
                    <div class="timeline-time"><?= ($statusVal === 'Diproses' || $statusVal === 'Selesai') ? 'Tindakan perbaikan sedang/telah dilakukan' : 'Menunggu alokasi tim teknis' ?></div>
                </div>
                <div class="timeline-item <?= $statusVal === 'Selesai' ? 'done' : '' ?> <?= $statusVal === 'Selesai' ? 'active' : '' ?>">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">Verifikasi Tuntas</div>
                    <div class="timeline-time"><?= $statusVal === 'Selesai' ? 'Insiden telah tertangani dan dinyatakan aman' : 'Menunggu konfirmasi penyelesaian' ?></div>
                </div>
            </div>
        </div>

        <div class="detail-card no-print" style="margin-bottom: 0;">
            <div class="panel-header" style="margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-subtle);">
                <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-sliders" style="color: var(--primary);"></i> Tindakan Pengawas K3
                </h2>
            </div>
            <?php if ($isAdmin): ?>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem;">
                    <?php if ($report['status'] !== 'Diproses'): ?>
                        <form method="POST" action="" style="flex: 1;">
                            <input type="hidden" name="action" value="quick_status">
                            <input type="hidden" name="status" value="Diproses">
                            <button type="submit" class="btn btn-outline btn-sm" style="width: 100%;">
                                <i class="bi bi-arrow-repeat"></i> Mulai Penanganan
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if ($report['status'] !== 'Selesai'): ?>
                        <form method="POST" action="" style="flex: 1;">
                            <input type="hidden" name="action" value="quick_status">
                            <input type="hidden" name="status" value="Selesai">
                            <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                                <i class="bi bi-check2"></i> Tandai Selesai
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_details">
                    <div class="form-group">
                        <label class="form-label">Status Penanganan</label>
                        <select name="status" class="form-control">
                            <option value="Menunggu" <?= $report['status'] === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="Diproses" <?= $report['status'] === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="Selesai" <?= $report['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tingkat Prioritas</label>
                        <select name="priority" class="form-control">
                            <option value="Tinggi" <?= $report['prioritas'] === 'Tinggi' ? 'selected' : '' ?>>Tinggi</option>
                            <option value="Normal" <?= $report['prioritas'] === 'Normal' ? 'selected' : '' ?>>Normal</option>
                            <option value="Rendah" <?= $report['prioritas'] === 'Rendah' ? 'selected' : '' ?>>Rendah</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-arrow-clockwise"></i> Perbarui Status & Prioritas
                    </button>
                </form>

                <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas laporan ini secara permanen dari sistem?');" style="margin-top: 0.85rem;">
                    <input type="hidden" name="action" value="delete_report">
                    <button type="submit" class="btn btn-danger-outline btn-sm" style="width: 100%;">
                        <i class="bi bi-trash"></i> Hapus Berkas Laporan
                    </button>
                </form>
            <?php else: ?>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                    <i class="bi bi-lock"></i> Masuk sebagai petugas K3 berwenang untuk mengubah status atau mencatat tindak lanjut.
                </p>
                <a href="login.php" class="btn btn-outline" style="width: 100%;">
                    <i class="bi bi-shield-lock"></i> Masuk Portal Petugas
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-card">
        <div class="panel-header" style="margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-journal-text" style="color: var(--primary);"></i> Log Catatan & Tindak Lanjut
                </h2>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">Catatan investigasi internal petugas keselamatan kerja.</div>
            </div>
            <span class="badge badge-rendah"><?= count($notes) ?> Catatan</span>
        </div>

        <?php if ($isAdmin): ?>
            <form method="POST" action="" class="no-print" style="margin-bottom: 1.5rem;">
                <input type="hidden" name="action" value="add_note">
                <div class="form-group">
                    <label class="form-label">Tambah Catatan Investigasi / Penanganan</label>
                    <textarea name="catatan" class="form-control" required placeholder="Tuliskan tindakan korektif, nama teknisi yang bertugas, atau catatan verifikasi lapangan..." rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-accent btn-sm">
                    <i class="bi bi-plus-circle"></i> Simpan Catatan Tindak Lanjut
                </button>
            </form>
        <?php endif; ?>

        <div class="notes-list">
            <?php if (empty($notes)): ?>
                <div class="empty-state" style="padding: 2.5rem 1rem;">
                    <i class="bi bi-journal" style="font-size: 1.75rem;"></i>
                    <p>Belum ada catatan tindak lanjut yang direkam untuk berkas ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notes as $note): ?>
                    <div class="note-item">
                        <div class="note-meta">
                            <span class="note-author">
                                <i class="bi bi-person-badge"></i>
                                <?= htmlspecialchars($note['username']) ?>
                                <span class="badge badge-diproses" style="font-size: 0.7rem; padding: 0.15rem 0.45rem;"><?= htmlspecialchars($note['role']) ?></span>
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
