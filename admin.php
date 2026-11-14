<?php
require_once 'config.php';
require_once 'classes/AuthManager.php';
require_once 'classes/ReportManager.php';

$auth = new AuthManager($db);
$auth->requireLogin();
$user = $auth->getCurrentUser();
$manager = new ReportManager($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $result = $manager->updateStatus($id, $status);
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_priority') {
    $id = intval($_POST['id']);
    $priority = $_POST['priority'];
    $result = $manager->updatePriority($id, $priority);
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
    exit;
}

$stats = $manager->getStatistics();

$filters = [];
if (!empty($_GET['q'])) $filters['q'] = trim($_GET['q']);
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
if (!empty($_GET['kategori'])) $filters['kategori'] = $_GET['kategori'];
if (!empty($_GET['prioritas'])) $filters['prioritas'] = $_GET['prioritas'];
if (!empty($_GET['tanggal_dari'])) $filters['tanggal_dari'] = $_GET['tanggal_dari'];
if (!empty($_GET['tanggal_sampai'])) $filters['tanggal_sampai'] = $_GET['tanggal_sampai'];

$reports = $manager->getFilteredReports($filters);

$exportParams = http_build_query($filters);
$exportPdfUrl = 'export_pdf.php' . ($exportParams ? '?' . $exportParams : '');
$exportCsvUrl = 'export_csv.php' . ($exportParams ? '?' . $exportParams : '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsol Pengawas K3 — NenaCare</title>
    <meta name="description" content="Dashboard monitoring dan manajemen insiden keselamatan kerja K3 Nena Cafe.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<nav class="navbar">
    <a href="admin.php" class="nav-brand">
        <i class="bi bi-shield-check"></i>
        <span>NenaCare K3</span>
    </a>
    <ul class="nav-links">
        <li><a href="admin.php" class="active"><i class="bi bi-grid-1x2"></i> Konsol Pemantauan</a></li>
        <li><a href="index.php"><i class="bi bi-megaphone"></i> Portal Publik</a></li>
    </ul>
    <div class="nav-user">
        <div class="nav-user-info">
            <div class="nav-user-name"><?= htmlspecialchars($user['username']) ?></div>
            <div class="nav-user-role"><?= htmlspecialchars($user['role']) ?></div>
        </div>
        <div class="nav-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
        <a href="logout.php" class="btn btn-outline btn-sm" title="Keluar"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</nav>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-grid-1x2"></i> Konsol Pengawas K3</h1>
    <p class="page-subtitle">Pusat kendali, verifikasi, dan audit keselamatan kerja operasional Nena Cafe</p>
</div>

<div class="admin-content">

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert-box success" style="margin-bottom: 1.5rem;">
            <i class="bi bi-check-circle"></i> Berkas laporan insiden berhasil dihapus secara permanen dari sistem.
        </div>
    <?php endif; ?>

    <div class="stat-grid">
        <div class="stat-card total">
            <div class="stat-card-header">
                <span class="stat-label">Total Insiden</span>
                <i class="bi bi-folder2-open stat-icon"></i>
            </div>
            <div class="stat-number"><?= $stats['total'] ?></div>
            <span class="stat-note">Seluruh rekam data</span>
        </div>
        <div class="stat-card menunggu">
            <div class="stat-card-header">
                <span class="stat-label">Menunggu Tindakan</span>
                <i class="bi bi-clock stat-icon"></i>
            </div>
            <div class="stat-number"><?= $stats['menunggu'] ?></div>
            <span class="stat-note">Perlu peninjauan</span>
        </div>
        <div class="stat-card diproses">
            <div class="stat-card-header">
                <span class="stat-label">Sedang Ditangani</span>
                <i class="bi bi-arrow-repeat stat-icon"></i>
            </div>
            <div class="stat-number"><?= $stats['diproses'] ?></div>
            <span class="stat-note">Dalam investigasi</span>
        </div>
        <div class="stat-card selesai">
            <div class="stat-card-header">
                <span class="stat-label">Selesai / Teratasi</span>
                <i class="bi bi-check2-circle stat-icon"></i>
            </div>
            <div class="stat-number"><?= $stats['selesai'] ?></div>
            <span class="stat-note">Rasio: <?= $stats['persen_selesai'] ?? 0 ?>% tuntas</span>
        </div>
        <div class="stat-card tinggi">
            <div class="stat-card-header">
                <span class="stat-label">Prioritas Tinggi</span>
                <i class="bi bi-exclamation-octagon stat-icon"></i>
            </div>
            <div class="stat-number"><?= $stats['tinggi'] ?></div>
            <span class="stat-note">Bahaya kritis</span>
        </div>
    </div>

    <div class="chart-grid">
        <div class="chart-card">
            <h3><i class="bi bi-pie-chart"></i> Distribusi Status Insiden</h3>
            <canvas id="statusChart" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h3><i class="bi bi-bar-chart"></i> Distribusi Berdasarkan Kategori Bahaya</h3>
            <canvas id="kategoriChart" height="200"></canvas>
        </div>
    </div>

    <form method="GET" action="admin.php" class="filter-bar" id="filterForm">
        <div class="filter-group" style="min-width: 200px;">
            <label>Pencarian Kata Kunci</label>
            <input type="text" name="q" class="form-control" placeholder="Pelapor, lokasi, deskripsi..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="Menunggu" <?= (isset($filters['status']) && $filters['status'] === 'Menunggu') ? 'selected' : '' ?>>Menunggu</option>
                <option value="Diproses" <?= (isset($filters['status']) && $filters['status'] === 'Diproses') ? 'selected' : '' ?>>Diproses</option>
                <option value="Selesai" <?= (isset($filters['status']) && $filters['status'] === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Kategori</label>
            <select name="kategori" class="form-control">
                <option value="">Semua Kategori</option>
                <option value="Api & Gas" <?= (isset($filters['kategori']) && $filters['kategori'] === 'Api & Gas') ? 'selected' : '' ?>>Api & Gas</option>
                <option value="Kelistrikan" <?= (isset($filters['kategori']) && $filters['kategori'] === 'Kelistrikan') ? 'selected' : '' ?>>Kelistrikan</option>
                <option value="Lingkungan & Kebersihan" <?= (isset($filters['kategori']) && $filters['kategori'] === 'Lingkungan & Kebersihan') ? 'selected' : '' ?>>Lingkungan & Kebersihan</option>
                <option value="Ergonomi & APD" <?= (isset($filters['kategori']) && $filters['kategori'] === 'Ergonomi & APD') ? 'selected' : '' ?>>Ergonomi & APD</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Prioritas</label>
            <select name="prioritas" class="form-control">
                <option value="">Semua Prioritas</option>
                <option value="Tinggi" <?= (isset($filters['prioritas']) && $filters['prioritas'] === 'Tinggi') ? 'selected' : '' ?>>Tinggi</option>
                <option value="Normal" <?= (isset($filters['prioritas']) && $filters['prioritas'] === 'Normal') ? 'selected' : '' ?>>Normal</option>
                <option value="Rendah" <?= (isset($filters['prioritas']) && $filters['prioritas'] === 'Rendah') ? 'selected' : '' ?>>Rendah</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Dari Tanggal</label>
            <input type="date" name="tanggal_dari" class="form-control" value="<?= htmlspecialchars($filters['tanggal_dari'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <label>Sampai Tanggal</label>
            <input type="date" name="tanggal_sampai" class="form-control" value="<?= htmlspecialchars($filters['tanggal_sampai'] ?? '') ?>">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Terapkan</button>
            <a href="admin.php" class="btn btn-outline" title="Reset filter"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            <a href="<?= $exportPdfUrl ?>" class="btn btn-accent" target="_blank" title="Unduh laporan PDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
            <a href="<?= $exportCsvUrl ?>" class="btn btn-accent" target="_blank" title="Unduh data CSV"><i class="bi bi-file-earmark-spreadsheet"></i> CSV</a>
        </div>
    </form>

    <div class="table-card">
        <div style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-default);">
            <h2 class="panel-heading" style="margin-bottom: 0;">
                <i class="bi bi-table"></i> Log Rekam Insiden
            </h2>
            <span class="badge badge-rendah"><?= count($reports) ?> Data Ditemukan</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Insiden</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Waktu Kejadian</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                <i class="bi bi-inbox" style="font-size: 1.75rem; display: block; margin-bottom: 0.5rem;"></i>
                                Tidak ada data insiden yang sesuai dengan parameter filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r): 
                            $isAnonim = isset($r['is_anonim']) && $r['is_anonim'];
                            $displayName = $isAnonim ? 'Pelapor Anonim' : htmlspecialchars($r['nama_pelapor'] ?? '-');
                            
                            $prioBadge = 'badge-normal';
                            if ($r['prioritas'] === 'Tinggi') $prioBadge = 'badge-tinggi';
                            elseif ($r['prioritas'] === 'Rendah') $prioBadge = 'badge-rendah';

                            $statusBadge = 'badge-menunggu';
                            if ($r['status'] === 'Diproses') $statusBadge = 'badge-diproses';
                            elseif ($r['status'] === 'Selesai') $statusBadge = 'badge-selesai';
                        ?>
                        <tr>
                            <td class="mono-cell">#LAP-<?= str_pad($r['id_laporan'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                                    <i class="bi <?= $isAnonim ? 'bi-shield-shaded' : 'bi-person' ?>" style="color: var(--text-muted);"></i>
                                    <?= $displayName ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-rendah"><?= htmlspecialchars($r['kategori_masalah']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($r['lokasi_kejadian']) ?></td>
                            <td>
                                <select class="status-select" data-id="<?= $r['id_laporan'] ?>" onchange="updatePriority(this)">
                                    <option value="Tinggi" <?= $r['prioritas'] === 'Tinggi' ? 'selected' : '' ?>>Tinggi</option>
                                    <option value="Normal" <?= $r['prioritas'] === 'Normal' ? 'selected' : '' ?>>Normal</option>
                                    <option value="Rendah" <?= $r['prioritas'] === 'Rendah' ? 'selected' : '' ?>>Rendah</option>
                                </select>
                            </td>
                            <td>
                                <select class="status-select" data-id="<?= $r['id_laporan'] ?>" onchange="updateStatus(this)">
                                    <option value="Menunggu" <?= $r['status'] === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                    <option value="Diproses" <?= $r['status'] === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                    <option value="Selesai" <?= $r['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                            </td>
                            <td class="mono-cell"><?= date('d/m/Y H:i', strtotime($r['waktu_lapor'])) ?></td>
                            <td style="text-align: right;">
                                <a href="detail.php?id=<?= $r['id_laporan'] ?>" class="btn btn-outline btn-sm">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// Chart.js Light Mode Clean Styling
Chart.defaults.color = '#475569';
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

const statusData = <?= json_encode($stats['by_status']) ?>;
const statusLabels = statusData.map(d => d.status);
const statusValues = statusData.map(d => parseInt(d.c));
const statusColors = statusLabels.map(s => {
    if (s === 'Menunggu') return '#94a3b8';
    if (s === 'Diproses') return '#0284c7';
    if (s === 'Selesai') return '#16a34a';
    return '#64748b';
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusValues,
            backgroundColor: statusColors,
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: '#475569', font: { size: 12 }, padding: 14 }
            }
        },
        cutout: '68%'
    }
});

const katData = <?= json_encode($stats['by_kategori']) ?>;
const katLabels = katData.map(d => d.kategori_masalah);
const katValues = katData.map(d => parseInt(d.c));

new Chart(document.getElementById('kategoriChart'), {
    type: 'bar',
    data: {
        labels: katLabels,
        datasets: [{
            label: 'Jumlah Insiden',
            data: katValues,
            backgroundColor: '#0284c7',
            borderColor: '#0369a1',
            borderWidth: 1,
            borderRadius: 6,
            barThickness: 28
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#64748b', stepSize: 1 },
                grid: { color: '#f1f5f9' }
            },
            x: {
                ticks: { color: '#64748b', font: { size: 11 }, maxRotation: 30 },
                grid: { display: false }
            }
        }
    }
});

function updateStatus(selectEl) {
    const id = selectEl.dataset.id;
    const status = selectEl.value;

    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&id=${id}&status=${encodeURIComponent(status)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectEl.style.borderColor = 'var(--success)';
            selectEl.style.boxShadow = '0 0 0 3px rgba(22, 163, 74, 0.2)';
            setTimeout(() => { selectEl.style.borderColor = ''; selectEl.style.boxShadow = ''; }, 1200);
        } else {
            alert('Gagal memperbarui status insiden.');
        }
    })
    .catch(() => alert('Terjadi gangguan jaringan saat memperbarui status.'));
}

function updatePriority(selectEl) {
    const id = selectEl.dataset.id;
    const priority = selectEl.value;

    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_priority&id=${id}&priority=${encodeURIComponent(priority)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectEl.style.borderColor = 'var(--success)';
            selectEl.style.boxShadow = '0 0 0 3px rgba(22, 163, 74, 0.2)';
            setTimeout(() => { selectEl.style.borderColor = ''; selectEl.style.boxShadow = ''; }, 1200);
        } else {
            alert('Gagal memperbarui tingkat prioritas.');
        }
    })
    .catch(() => alert('Terjadi gangguan jaringan saat memperbarui prioritas.'));
}
</script>

</body>
</html>
