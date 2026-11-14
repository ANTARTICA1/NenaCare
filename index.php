<?php
require_once 'config.php';
require_once 'classes/ReportManager.php';

$manager = new ReportManager($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_anonim = isset($_POST['is_anonim']) ? 1 : 0;
    $nama_pelapor = $is_anonim ? null : trim($_POST['nama_pelapor'] ?? '');
    $tipe_pelapor = $is_anonim ? null : trim($_POST['tipe_pelapor'] ?? '');
    $kategori_masalah = trim($_POST['kategori_masalah'] ?? '');
    $lokasi_kejadian = trim($_POST['lokasi_kejadian'] ?? '');
    $deskripsi_kejadian = trim($_POST['deskripsi_kejadian'] ?? '');

    if (!empty($kategori_masalah) && !empty($lokasi_kejadian) && !empty($deskripsi_kejadian)) {
        if ($manager->saveReport($nama_pelapor, $tipe_pelapor, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian, $is_anonim)) {
            $message = '<div class="alert-box success"><i class="bi bi-check-circle"></i> Laporan insiden berhasil dikirim. Analisis risiko telah dihasilkan dan notifikasi telah diteruskan ke tim pengawas.</div>';
        } else {
            $message = '<div class="alert-box danger"><i class="bi bi-exclamation-triangle"></i> Gagal menyimpan laporan ke basis data. Silakan coba kembali.</div>';
        }
    } else {
        $message = '<div class="alert-box danger"><i class="bi bi-exclamation-triangle"></i> Harap lengkapi seluruh kolom yang wajib diisi.</div>';
    }
}

$reports = $manager->getAllReports();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NenaCare K3 — Sistem Pelaporan & Manajemen Keselamatan Kerja</title>
    <meta name="description" content="Portal pelaporan insiden dan pemantauan bahaya keselamatan kerja (HSE) Nena Cafe.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="brand-block">
            <div class="brand-logo-badge">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="brand-meta">
                <h1>NenaCare K3</h1>
                <p>Sistem Pelaporan & Manajemen Keselamatan Kerja</p>
            </div>
        </div>
        <div class="header-actions">
            <a href="login.php" class="btn btn-outline btn-sm">
                <i class="bi bi-shield-lock"></i> Portal Petugas K3
            </a>
        </div>
    </div>
</header>

<div class="main-container">

    <aside>
        <div class="form-card">
            <div class="form-header">
                <h2><i class="bi bi-pencil-square"></i> Formulir Pelaporan Insiden</h2>
                <p>Sampaikan potensi bahaya atau insiden kerja secara presisi untuk segera ditangani.</p>
            </div>

            <?php if (!empty($message)): ?>
                <?= $message ?>
            <?php endif; ?>

            <form method="POST" action="" id="reportForm">
                
                <div class="anon-toggle">
                    <input type="checkbox" id="is_anonim" name="is_anonim" value="1">
                    <label for="is_anonim" class="anon-label">
                        <span class="anon-title">Laporkan Sebagai Anonim</span>
                        <span class="anon-desc">Identitas nama dan peran Anda tidak akan dicatat atau ditampilkan ke publik.</span>
                    </label>
                </div>

                <div class="form-group" id="group-nama">
                    <label class="form-label">Nama Pelapor <span class="required-mark">*</span></label>
                    <input type="text" name="nama_pelapor" id="nama_pelapor" class="form-control" required placeholder="Contoh: Budi Santoso">
                </div>

                <div class="form-group" id="group-tipe">
                    <label class="form-label">Peran Pelapor <span class="required-mark">*</span></label>
                    <select name="tipe_pelapor" id="tipe_pelapor" class="form-control" required>
                        <option value="Customer">Customer / Pelanggan</option>
                        <option value="Staf Kafe">Staf Kafe / Operasional</option>
                        <option value="Barista">Barista / Bar Floor</option>
                        <option value="Dapur">Staf Dapur / Kitchen</option>
                        <option value="Teknisi">Teknisi / Pemeliharaan Fasilitas</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori Insiden <span class="required-mark">*</span></label>
                    <select name="kategori_masalah" id="kategori_masalah" class="form-control" required>
                        <option value="Api & Gas">Api & Bahan Bakar Gas (LPG, Kompor, Oven)</option>
                        <option value="Kelistrikan">Kelistrikan (Konsleting, Kabel Terbuka, Panel Daya)</option>
                        <option value="Lingkungan & Kebersihan">Lingkungan & Sanitasi (Lantai Licin, Tumpahan, Asap)</option>
                        <option value="Ergonomi & APD">Ergonomi & APD (Cedera Benda Tajam, Luka Bakar, APD)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Lokasi Kejadian <span class="required-mark">*</span></label>
                    <input type="text" name="lokasi_kejadian" class="form-control" required placeholder="Contoh: Meja Bar Utama / Area Cuci Piring">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Kejadian <span class="required-mark">*</span></label>
                    <textarea name="deskripsi_kejadian" class="form-control" required placeholder="Uraikan kondisi berbahaya, waktu pengamatan, serta dampak fisik yang timbul..."></textarea>
                    <span class="form-hint">Harap deskripsikan fakta kejadian secara objektif tanpa opini spekulatif.</span>
                </div>

                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-send"></i> Kirim Laporan Insiden
                </button>
            </form>
        </div>
    </aside>

    <main class="feed-section">
        <div class="feed-header">
            <div>
                <h2><i class="bi bi-activity"></i> Log Pemantauan Insiden</h2>
                <p>Daftar rekam insiden keselamatan kerja yang dilaporkan secara langsung.</p>
            </div>
            <div>
                <span class="badge badge-rendah" id="reportCountBadge"><?= count($reports) ?> Data Tercatat</span>
            </div>
        </div>

        <div class="feed-controls">
            <div class="feed-tabs" id="categoryFilterTabs">
                <button type="button" class="feed-tab active" data-filter="all">Semua</button>
                <button type="button" class="feed-tab" data-filter="Api & Gas">Api & Gas</button>
                <button type="button" class="feed-tab" data-filter="Kelistrikan">Kelistrikan</button>
                <button type="button" class="feed-tab" data-filter="Lingkungan & Kebersihan">Lingkungan</button>
                <button type="button" class="feed-tab" data-filter="Ergonomi & APD">Ergonomi & APD</button>
            </div>

            <div class="feed-search">
                <i class="bi bi-search"></i>
                <input type="text" id="liveSearchInput" placeholder="Cari insiden, lokasi, isi...">
            </div>
        </div>

        <div class="reports-grid" id="reportsGridContainer">
            <?php if (empty($reports)): ?>
                <div class="empty-state">
                    <i class="bi bi-shield-check"></i>
                    <p>Kondisi operasional kafe terpantau kondusif. Belum ada catatan insiden aktif saat ini.</p>
                </div>
            <?php else: ?>
                <?php 
                foreach ($reports as $r): 
                    $prioBadge = 'badge-normal';
                    if ($r['prioritas'] === 'Tinggi') $prioBadge = 'badge-tinggi';
                    elseif ($r['prioritas'] === 'Rendah') $prioBadge = 'badge-rendah';

                    $statusBadge = 'badge-menunggu';
                    if ($r['status'] === 'Diproses') $statusBadge = 'badge-diproses';
                    elseif ($r['status'] === 'Selesai') $statusBadge = 'badge-selesai';
                    
                    $isAnonim = isset($r['is_anonim']) && $r['is_anonim'];
                    $displayName = $isAnonim ? 'Pelapor Anonim' : htmlspecialchars($r['nama_pelapor']);
                    $displayType = $isAnonim ? 'Identitas Terlindungi' : htmlspecialchars($r['tipe_pelapor']);

                    $category = htmlspecialchars($r['kategori_masalah']);
                    $location = htmlspecialchars($r['lokasi_kejadian']);
                    $desc = htmlspecialchars($r['deskripsi_kejadian']);
                    $advice = htmlspecialchars($r['saran_ai'] ?? 'Belum ada rekomendasi');
                    $timeFormatted = date('d M Y, H:i', strtotime($r['waktu_lapor']));
                ?>
                    <a href="detail.php?id=<?= $r['id_laporan'] ?>" class="report-card-link" data-category="<?= $category ?>" data-search="<?= strtolower($displayName . ' ' . $category . ' ' . $location . ' ' . $desc) ?>">
                        <div class="report-card">
                            
                            <div class="rc-top">
                                <span class="rc-id-tag">#LAP-<?= str_pad($r['id_laporan'], 4, '0', STR_PAD_LEFT) ?></span>
                                <div class="rc-badges">
                                    <span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($r['status']) ?></span>
                                    <span class="badge <?= $prioBadge ?>">Prioritas <?= htmlspecialchars($r['prioritas']) ?></span>
                                </div>
                            </div>

                            <div class="rc-title-block">
                                <div class="rc-name">
                                    <i class="bi <?= $isAnonim ? 'bi-shield-shaded' : 'bi-person' ?>"></i>
                                    <?= $displayName ?>
                                </div>
                                <div class="rc-role"><?= $displayType ?></div>
                            </div>

                            <div class="rc-meta">
                                <div class="rc-meta-item">
                                    <i class="bi bi-geo-alt"></i> <?= $location ?>
                                </div>
                                <div class="rc-meta-item">
                                    <i class="bi bi-tag"></i> <?= $category ?>
                                </div>
                                <div class="rc-meta-item">
                                    <i class="bi bi-clock"></i> <?= $timeFormatted ?>
                                </div>
                            </div>
                            
                            <div class="rc-desc">
                                "<?= $desc ?>"
                            </div>

                            <div class="rc-ai-box">
                                <div class="rc-ai-label">
                                    <i class="bi bi-cpu"></i> Rekomendasi Keselamatan K3
                                </div>
                                <div class="rc-ai-content">
                                    <?= $advice ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div id="noMatchState" class="empty-state" style="display: none; margin-top: 1rem;">
            <i class="bi bi-funnel"></i>
            <p>Tidak ditemukan insiden yang sesuai dengan kriteria filter atau pencarian.</p>
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
            groupNama.style.opacity = '0.35';
            groupTipe.style.opacity = '0.35';
        } else {
            groupNama.style.opacity = '1';
            groupTipe.style.opacity = '1';
        }
    }

    if (anonCheckbox) {
        anonCheckbox.addEventListener('change', toggleAnonymous);
    }

    const searchInput = document.getElementById('liveSearchInput');
    const filterTabs = document.querySelectorAll('.feed-tab');
    const cards = document.querySelectorAll('.report-card-link');
    const noMatchState = document.getElementById('noMatchState');
    const countBadge = document.getElementById('reportCountBadge');

    let activeFilter = 'all';

    function filterFeed() {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            const searchData = card.getAttribute('data-search') || '';

            const matchCat = (activeFilter === 'all') || (cardCat === activeFilter);
            const matchQuery = (query === '') || (searchData.indexOf(query) !== -1);

            if (matchCat && matchQuery) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noMatchState) {
            noMatchState.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
        }

        if (countBadge) {
            countBadge.textContent = `${visibleCount} Menampilkan`;
        }
    }

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            activeFilter = this.getAttribute('data-filter');
            filterFeed();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', filterFeed);
    }
});
</script>

</body>
</html>
