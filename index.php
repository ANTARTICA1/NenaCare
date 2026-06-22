<?php
require_once 'config.php';
require_once 'classes/ReportManager.php';

$manager = new ReportManager($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_anonim = isset($_POST['is_anonim']) ? 1 : 0;
    $nama_pelapor = $is_anonim ? null : $_POST['nama_pelapor'];
    $tipe_pelapor = $is_anonim ? null : $_POST['tipe_pelapor'];
    $kategori_masalah = $_POST['kategori_masalah'];
    $lokasi_kejadian = $_POST['lokasi_kejadian'];
    $deskripsi_kejadian = $_POST['deskripsi_kejadian'];

    if ($manager->saveReport($nama_pelapor, $tipe_pelapor, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian, $is_anonim)) {
        $message = '<div class="glass-alert success pulse-animation"><i class="bi bi-check-circle-fill"></i> Laporan berhasil terkirim & AI bertindak! Terdapat notifikasi Telegram ke Admin.</div>';
    } else {
        $message = '<div class="glass-alert danger"><i class="bi bi-exclamation-triangle-fill"></i> Gagal memproses laporan.</div>';
    }
}

$reports = $manager->getAllReports();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NenaCare K3 — Incident Reporting System</title>
    <meta name="description" content="Sistem pelaporan insiden K3 berbasis AI untuk Nena Cafe. Laporkan insiden keselamatan kerja dengan cepat dan aman.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .main-container {
            width: 90%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 0;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media(min-width: 992px) {
            .main-container { grid-template-columns: 380px 1fr; align-items: start; }
        }

        .main-header {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .main-header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }

        .main-header p {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
            font-weight: 300;
        }

        .panel-form {
            position: sticky;
            top: 2rem;
        }

        .admin-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            margin-top: 0.8rem;
            transition: color 0.3s;
        }
        .admin-link:hover { color: var(--primary); }
    </style>
</head>
<body>

<div class="shape shape-1"></div>
<div class="shape shape-2"></div>

<header class="main-header">
    <h1><i class="bi bi-shield-check"></i> NenaCare</h1>
    <p>AI-Powered K3 Incident Reporting System</p>
    <a href="login.php" class="admin-link"><i class="bi bi-box-arrow-in-right"></i> Admin Dashboard</a>
</header>

<div class="main-container">
    
    <aside>
        <div class="glass-panel panel-form">
            <h2 class="panel-heading"><i class="bi bi-broadcast-pin"></i> Lapor Insiden</h2>
            <form method="POST" action="" id="reportForm">
                
                <!-- Anonymous Checkbox -->
                <label class="checkbox-wrapper" for="is_anonim">
                    <input type="checkbox" id="is_anonim" name="is_anonim" value="1">
                    <div class="checkbox-label">
                        🕵️ Laporkan Secara Anonim
                        <small>Identitas Anda tidak akan dicatat</small>
                    </div>
                </label>

                <div class="form-group" id="group-nama">
                    <label class="form-label">Nama Pelapor</label>
                    <input type="text" name="nama_pelapor" id="nama_pelapor" class="form-control" required placeholder="Siapa nama Anda?">
                </div>
                <div class="form-group" id="group-tipe">
                    <label class="form-label">Tipe Pelapor</label>
                    <select name="tipe_pelapor" id="tipe_pelapor" class="form-control" required>
                        <option value="Customer">🧑‍🤝‍🧑 Customer</option>
                        <option value="Staf Kafe">👨‍🍳 Staf Kafe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori Insiden</label>
                    <select name="kategori_masalah" class="form-control" required>
                        <option value="Api & Gas">🔥 Api & Gas (LPG, Kompor)</option>
                        <option value="Kelistrikan">⚡ Kelistrikan (Konslet, Kabel)</option>
                        <option value="Lingkungan & Kebersihan">🧹 Lingkungan (Lantai licin, Asap)</option>
                        <option value="Ergonomi & APD">🧤 Ergonomi & APD (Tertusuk, Luka)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi Kejadian</label>
                    <input type="text" name="lokasi_kejadian" class="form-control" required placeholder="Cth: Area Dapur Utama">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi Kejadian</label>
                    <textarea name="deskripsi_kejadian" class="form-control" required placeholder="Jelaskan detail kejadian berbahaya yang Anda amati secara spesifik..."></textarea>
                </div>
                <button type="submit" class="btn-submit"><i class="bi bi-send-fill"></i> Kirim Laporan</button>
            </form>
            <?= $message ?>
        </div>
    </aside>

    <main>
        <div class="glass-panel">
            <h2 class="panel-heading"><i class="bi bi-activity"></i> Live Monitoring Feed</h2>
            <div class="reports-grid">
                <?php if (empty($reports)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox-fill"></i>
                        <p>Area Kafe aman. Belum ada rekam laporan saat ini.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $delay = 0;
                    foreach ($reports as $r): 
                        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $r['status']));
                        
                        $prioBadge = 'badge-normal';
                        if($r['prioritas'] == 'Tinggi') $prioBadge = 'badge-tinggi';
                        else if($r['prioritas'] == 'Rendah') $prioBadge = 'badge-rendah';
                        
                        $isAnonim = isset($r['is_anonim']) && $r['is_anonim'];
                        $displayName = $isAnonim ? '🕵️ Anonim' : htmlspecialchars($r['nama_pelapor']);
                        $displayType = $isAnonim ? 'Identitas Dirahasiakan' : htmlspecialchars($r['tipe_pelapor']);

                        $delay += 0.1;
                    ?>
                        <a href="detail.php?id=<?= $r['id_laporan'] ?>" class="report-card-link">
                        <div class="report-card <?= $statusClass ?>" style="animation-delay: <?= $delay ?>s">
                            
                            <div class="rc-header">
                                <div class="rc-user">
                                    <div class="rc-name"><i class="bi bi-person-fill"></i> <?= $displayName ?></div>
                                    <div class="rc-type"><?= $displayType ?></div>
                                </div>
                                <div class="rc-status"><?= htmlspecialchars($r['status']) ?></div>
                            </div>

                            <div class="rc-body">
                                <div class="rc-meta">
                                    <div class="rc-meta-item"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($r['lokasi_kejadian']) ?></div>
                                    <div class="rc-meta-item"><i class="bi bi-tags-fill"></i> <?= htmlspecialchars($r['kategori_masalah']) ?></div>
                                </div>
                                
                                <div class="rc-desc">
                                    "<?= htmlspecialchars($r['deskripsi_kejadian']) ?>"
                                </div>
                            </div>

                            <div class="rc-ai">
                                <div class="rc-ai-header">
                                    <span class="rc-ai-title"><i class="bi bi-robot"></i> AI Analyst</span>
                                    <span class="badge <?= $prioBadge ?>"><?= htmlspecialchars($r['prioritas']) ?></span>
                                </div>
                                <div class="rc-ai-text">
                                    <?= htmlspecialchars($r['saran_ai']) ?>
                                </div>
                            </div>
                        </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const anonCheckbox = document.getElementById('is_anonim');
    const namaInput = document.getElementById('nama_pelapor');
    const tipeSelect = document.getElementById('tipe_pelapor');
    const groupNama = document.getElementById('group-nama');
    const groupTipe = document.getElementById('group-tipe');

    function toggleAnonymous() {
        const isAnon = anonCheckbox.checked;
        namaInput.disabled = isAnon;
        tipeSelect.disabled = isAnon;
        namaInput.required = !isAnon;
        tipeSelect.required = !isAnon;
        
        if (isAnon) {
            namaInput.value = '';
            groupNama.style.opacity = '0.4';
            groupTipe.style.opacity = '0.4';
        } else {
            groupNama.style.opacity = '1';
            groupTipe.style.opacity = '1';
        }
    }

    anonCheckbox.addEventListener('change', toggleAnonymous);
});
</script>

</body>
</html>
