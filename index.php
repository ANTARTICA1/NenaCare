<?php
require_once 'config.php';
require_once 'classes/ReportManager.php';

$manager = new ReportManager($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pelapor = $_POST['nama_pelapor'];
    $tipe_pelapor = $_POST['tipe_pelapor'];
    $kategori_masalah = $_POST['kategori_masalah'];
    $lokasi_kejadian = $_POST['lokasi_kejadian'];
    $deskripsi_kejadian = $_POST['deskripsi_kejadian'];

    if ($manager->saveReport($nama_pelapor, $tipe_pelapor, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian)) {
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
    <title>Nena K3 - Incident Reporting System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #00f2fe;
            --secondary: #4facfe;
            --accent: #ff0844;
            --accent2: #ffb199;
            --dark: #0a0f1d;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background-color: var(--dark);
            color: var(--text-main);
            min-height: 100vh;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(79, 172, 254, 0.15), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(255, 8, 68, 0.15), transparent 25%);
            background-attachment: fixed;
            overflow-x: hidden;
            line-height: 1.6;
        }

        .shape {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            border-radius: 50%;
            animation: float 20s infinite alternate cubic-bezier(0.5, 0, 0.5, 1);
        }
        .shape-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: rgba(79, 172, 254, 0.1); }
        .shape-2 { bottom: -10%; right: -10%; width: 40vw; height: 40vw; background: rgba(255, 8, 68, 0.1); }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(5%, 10%) scale(1.1); }
        }

        .container {
            width: 90%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 0;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media(min-width: 992px) {
            .container { grid-template-columns: 380px 1fr; align-items: start; }
        }

        header {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }

        header p {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
            font-weight: 300;
        }

        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .panel-form {
            position: sticky;
            top: 2rem;
        }

        .panel-heading {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            transition: all 0.3s;
        }

        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 1rem 1.2rem;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 242, 254, 0.1);
        }

        .form-control::placeholder { color: rgba(255, 255, 255, 0.2); }
        select.form-control option { background: var(--dark); color: #fff; }
        textarea.form-control { resize: vertical; min-height: 100px; }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff;
            border: none;
            padding: 1rem;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 8px 20px rgba(255, 8, 68, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(255, 8, 68, 0.4);
        }

        .btn-submit:active { transform: translateY(1px); }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .report-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: fadeInUp 0.5s ease backwards;
        }

        .report-card:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .report-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(to right, transparent, var(--primary), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .report-card:hover::before { opacity: 1; }

        .rc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed rgba(255,255,255,0.05);
        }

        .rc-user {
            display: flex;
            flex-direction: column;
        }

        .rc-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .rc-type {
            font-size: 0.8rem;
            color: var(--secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .rc-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.05);
        }

        .rc-status::before {
            content: '';
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
        }

        .status-menunggu .rc-status::before { background: #94a3b8; box-shadow: 0 0 8px #94a3b8; }
        .status-diproses .rc-status::before { background: #f59e0b; box-shadow: 0 0 8px #f59e0b; }
        .status-selesai .rc-status::before { background: #10b981; box-shadow: 0 0 8px #10b981; }

        .rc-body { flex-grow: 1; }

        .rc-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .rc-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .rc-meta-item i { color: var(--primary); }

        .rc-desc {
            font-size: 0.95rem;
            color: var(--text-main);
            line-height: 1.6;
            margin-bottom: 1.5rem;
            background: rgba(0,0,0,0.2);
            padding: 1rem;
            border-radius: 12px;
            border-left: 3px solid var(--accent);
        }

        .rc-ai {
            background: linear-gradient(135deg, rgba(79, 172, 254, 0.05), rgba(79, 172, 254, 0.1));
            border: 1px solid rgba(79, 172, 254, 0.2);
            border-radius: 12px;
            padding: 1rem;
            margin-top: auto;
        }

        .rc-ai-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .rc-ai-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-tinggi { background: rgba(255, 8, 68, 0.2); color: #ff477e; border: 1px solid rgba(255, 8, 68, 0.3); }
        .badge-normal { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-rendah { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.3); }

        .rc-ai-text {
            font-size: 0.9rem;
            color: #e2e8f0;
            font-weight: 300;
        }

        .glass-alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .glass-alert.success { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; }
        .glass-alert.danger { background: rgba(255, 8, 68, 0.1); border: 1px solid rgba(255, 8, 68, 0.3); color: #ff477e; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .pulse-animation i { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }

        .empty-state { text-align: center; padding: 3rem; color: var(--text-muted); grid-column: 1/-1; }
        .empty-state i { font-size: 3rem; opacity: 0.5; margin-bottom: 1rem; display: block; }
    </style>
</head>
<body>

<div class="shape shape-1"></div>
<div class="shape shape-2"></div>

<header>
    <h1><i class="bi bi-shield-check"></i> Nena Cafe</h1>
    <p>AI-Powered K3 Incident Reporting System</p>
</header>

<div class="container">
    
    <aside>
        <div class="glass-panel panel-form">
            <h2 class="panel-heading"><i class="bi bi-broadcast_pin"></i> Lapor Insiden</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Nama Pelapor</label>
                    <input type="text" name="nama_pelapor" class="form-control" required placeholder="Siapa nama Anda?">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipe Pelapor</label>
                    <select name="tipe_pelapor" class="form-control" required>
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
                        
                        $delay += 0.1;
                    ?>
                        <div class="report-card <?= $statusClass ?>" style="animation-delay: <?= $delay ?>s">
                            
                            <div class="rc-header">
                                <div class="rc-user">
                                    <div class="rc-name"><i class="bi bi-person-fill"></i> <?= htmlspecialchars($r['nama_pelapor']) ?></div>
                                    <div class="rc-type"><?= htmlspecialchars($r['tipe_pelapor']) ?></div>
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
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

</div>

</body>
</html>
