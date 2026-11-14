<?php
require_once 'config.php';
require_once 'classes/AuthManager.php';
require_once 'classes/ReportManager.php';

$auth = new AuthManager($db);
$auth->requireLogin();

$manager = new ReportManager($db);

$filters = [];
if (!empty($_GET['q'])) $filters['q'] = trim($_GET['q']);
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
if (!empty($_GET['kategori'])) $filters['kategori'] = $_GET['kategori'];
if (!empty($_GET['prioritas'])) $filters['prioritas'] = $_GET['prioritas'];
if (!empty($_GET['tanggal_dari'])) $filters['tanggal_dari'] = $_GET['tanggal_dari'];
if (!empty($_GET['tanggal_sampai'])) $filters['tanggal_sampai'] = $_GET['tanggal_sampai'];

$reports = $manager->getFilteredReports($filters);

$filename = 'NenaCare_Laporan_K3_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header row
fputcsv($output, [
    'ID Laporan',
    'Waktu Lapor',
    'Nama Pelapor',
    'Tipe Pelapor',
    'Anonim',
    'Kategori Masalah',
    'Lokasi Kejadian',
    'Deskripsi Kejadian',
    'Prioritas',
    'Rekomendasi AI K3',
    'Status Penanganan'
]);

// Data rows
foreach ($reports as $r) {
    $isAnon = (isset($r['is_anonim']) && $r['is_anonim']) ? 'Ya' : 'Tidak';
    $nama = ($isAnon === 'Ya') ? 'Anonim' : ($r['nama_pelapor'] ?? '-');
    $tipe = ($isAnon === 'Ya') ? 'Dirahasiakan' : ($r['tipe_pelapor'] ?? '-');

    fputcsv($output, [
        '#LAP-' . str_pad($r['id_laporan'], 4, '0', STR_PAD_LEFT),
        $r['waktu_lapor'],
        $nama,
        $tipe,
        $isAnon,
        $r['kategori_masalah'],
        $r['lokasi_kejadian'],
        $r['deskripsi_kejadian'],
        $r['prioritas'],
        $r['saran_ai'] ?? '-',
        $r['status']
    ]);
}

fclose($output);
exit;
