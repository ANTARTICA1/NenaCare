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

$stats = $manager->getStatistics();

$filters = [];
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
if (!empty($_GET['kategori'])) $filters['kategori'] = $_GET['kategori'];
if (!empty($_GET['prioritas'])) $filters['prioritas'] = $_GET['prioritas'];
if (!empty($_GET['tanggal_dari'])) $filters['tanggal_dari'] = $_GET['tanggal_dari'];
if (!empty($_GET['tanggal_sampai'])) $filters['tanggal_sampai'] = $_GET['tanggal_sampai'];

$reports = $manager->getFilteredReports($filters);

$exportParams = http_build_query($filters);
$exportUrl = 'export_pdf.php' . ($exportParams ? '?' . $exportParams : '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — NenaCare Admin</title>
    <meta name="description" content="Dashboard admin untuk monitoring dan manajemen laporan K3 Nena Cafe.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<div class="shape shape-1"></div>
<div class="shape shape-2"></div>

<nav class="navbar">
    <a href="admin.php" class="nav-brand"><i class="bi bi-shield-check"></i> NenaCare</a>
    <ul class="nav-links">
        <li><a href="admin.php" class="active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
        <li><a href="index.php"><i class="bi bi-megaphone-fill"></i> Pelaporan</a></li>
    </ul>
    <div class="nav-user">
        <div class="nav-user-info">
            <div class="nav-user-name"><?= htmlspecialchars($user['username']) ?></div>
            <div class="nav-user-role"><?= htmlspecialchars($user['role']) ?></div>
        </div>
        <div class="nav-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
        <a href="logout.php" class="btn btn-outline btn-sm" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</nav>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-grid-1x2-fill"></i> Dashboard K3</h1>
    <p class="page-subtitle">Monitoring & manajemen laporan keselamatan kerja Nena Cafe</p>
</div>

<div class="admin-content">

    <div class="stat-grid">
        <div class="stat-card total" style="animation-delay: 0.1s">
            <div class="stat-icon"><i class="bi bi-clipboard2-data-fill"></i></div>
            <div class="stat-number"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Laporan</div>
        </div>
        <div class="stat-card menunggu" style="animation-delay: 0.2s">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-number"><?= $stats['menunggu'] ?></div>
            <div class="stat-label">Menunggu</div>
        </div>
        <div class="stat-card diproses" style="animation-delay: 0.3s">
            <div class="stat-icon"><i class="bi bi-gear-fill"></i></div>
            <div class="stat-number"><?= $stats['diproses'] ?></div>
            <div class="stat-label">Diproses</div>
        </div>
        <div class="stat-card selesai" style="animation-delay: 0.4s">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-number"><?= $stats['selesai'] ?></div>
            <div class="stat-label">Selesai</div>
        </div>
        <div class="stat-card tinggi" style="animation-delay: 0.5s">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-number"><?= $stats['tinggi'] ?></div>
            <div class="stat-label">Prioritas Tinggi</div>
        </div>
    </div>

    <div class="chart-grid">
        <div class="chart-card">
            <h3><i class="bi bi-pie-chart-fill"></i> Distribusi Status</h3>
            <canvas id="statusChart"></canvas>
        </div>
        <div class="chart-card">
            <h3><i class="bi bi-bar-chart-fill"></i> Laporan per Kategori</h3>
            <canvas id="kategoriChart"></canvas>
        </div>
    </div>

    <form method="GET" action="admin.php" class="filter-bar" id="filterForm">
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
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filter</button>
            <a href="admin.php" class="btn btn-outline"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            <a href="<?= $exportUrl ?>" class="btn btn-accent" target="_blank"><i class="bi bi-file-earmark-pdf-fill"></i> Export PDF</a>
        </div>
    </form>

    <div class="glass-panel" style="padding: 0; overflow: hidden;">
        <div style="padding: 1.5rem 1.5rem 0;">
            <h2 class="panel-heading" style="margin-bottom: 0;"><i class="bi bi-table"></i> Data Laporan <span style="font-weight: 400; font-size: 0.9rem; color: var(--text-muted);">(<?= count($reports) ?> data)</span></h2>
        </div>
        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr><td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);"><i class="bi bi-inbox"></i> Tidak ada data ditemukan</td></tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r): 
                            $isAnonim = isset($r['is_anonim']) && $r['is_anonim'];
                            $displayName = $isAnonim ? '🕵️ Anonim' : htmlspecialchars($r['nama_pelapor'] ?? '-');
                            
                            $prioBadge = 'badge-normal';
                            if ($r['prioritas'] === 'Tinggi') $prioBadge = 'badge-tinggi';
                            elseif ($r['prioritas'] === 'Rendah') $prioBadge = 'badge-rendah';

                            $statusBadge = 'badge-menunggu';
                            if ($r['status'] === 'Diproses') $statusBadge = 'badge-diproses';
                            elseif ($r['status'] === 'Selesai') $statusBadge = 'badge-selesai';
                        ?>
                        <tr>
                            <td><strong>#<?= $r['id_laporan'] ?></strong></td>
                            <td><?= $displayName ?></td>
                            <td><?= htmlspecialchars($r['kategori_masalah']) ?></td>
                            <td><?= htmlspecialchars($r['lokasi_kejadian']) ?></td>
                            <td><span class="badge <?= $prioBadge ?>"><?= htmlspecialchars($r['prioritas']) ?></span></td>
                            <td>
                                <select class="status-select" data-id="<?= $r['id_laporan'] ?>" onchange="updateStatus(this)">
                                    <option value="Menunggu" <?= $r['status'] === 'Menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                                    <option value="Diproses" <?= $r['status'] === 'Diproses' ? 'selected' : '' ?>>🔧 Diproses</option>
                                    <option value="Selesai" <?= $r['status'] === 'Selesai' ? 'selected' : '' ?>>✅ Selesai</option>
                                </select>
                            </td>
                            <td style="white-space: nowrap; font-size: 0.85rem; color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($r['waktu_lapor'])) ?></td>
                            <td>
                                <a href="detail.php?id=<?= $r['id_laporan'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-eye-fill"></i> Detail</a>
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
const statusData = <?= json_encode($stats['by_status']) ?>;
const statusLabels = statusData.map(d => d.status);
const statusValues = statusData.map(d => parseInt(d.c));
const statusColors = statusLabels.map(s => {
    if (s === 'Menunggu') return '#94a3b8';
    if (s === 'Diproses') return '#f59e0b';
    if (s === 'Selesai') return '#10b981';
    return '#64748b';
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusValues,
            backgroundColor: statusColors,
            borderColor: 'rgba(10, 15, 29, 0.8)',
            borderWidth: 3,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: '#94a3b8', font: { family: 'Outfit', size: 13 }, padding: 16 }
            }
        },
        cutout: '60%'
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
            label: 'Jumlah Laporan',
            data: katValues,
            backgroundColor: [
                'rgba(255, 8, 68, 0.6)',
                'rgba(79, 172, 254, 0.6)',
                'rgba(16, 185, 129, 0.6)',
                'rgba(245, 158, 11, 0.6)'
            ],
            borderColor: [
                'rgba(255, 8, 68, 0.9)',
                'rgba(79, 172, 254, 0.9)',
                'rgba(16, 185, 129, 0.9)',
                'rgba(245, 158, 11, 0.9)'
            ],
            borderWidth: 1,
            borderRadius: 8,
            barThickness: 40
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
                ticks: { color: '#94a3b8', font: { family: 'Outfit' }, stepSize: 1 },
                grid: { color: 'rgba(255,255,255,0.04)' }
            },
            x: {
                ticks: { color: '#94a3b8', font: { family: 'Outfit', size: 11 }, maxRotation: 45 },
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
            selectEl.style.boxShadow = '0 0 12px rgba(0, 242, 254, 0.4)';
            setTimeout(() => { selectEl.style.boxShadow = 'none'; }, 1000);
        } else {
            alert('Gagal mengubah status.');
        }
    })
    .catch(() => alert('Error koneksi.'));
}
</script>

</body>
</html>
