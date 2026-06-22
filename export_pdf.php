<?php
require_once 'config.php';
require_once 'classes/AuthManager.php';
require_once 'classes/ReportManager.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$auth = new AuthManager($db);
$auth->requireLogin();

$manager = new ReportManager($db);

// Get filter values
$filters = [];
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
if (!empty($_GET['kategori'])) $filters['kategori'] = $_GET['kategori'];
if (!empty($_GET['prioritas'])) $filters['prioritas'] = $_GET['prioritas'];
if (!empty($_GET['tanggal_dari'])) $filters['tanggal_dari'] = $_GET['tanggal_dari'];
if (!empty($_GET['tanggal_sampai'])) $filters['tanggal_sampai'] = $_GET['tanggal_sampai'];

$reports = $manager->getFilteredReports($filters);
$stats = $manager->getStatistics();

// Build filter description
$filterDesc = [];
if (!empty($filters['status'])) $filterDesc[] = "Status: " . $filters['status'];
if (!empty($filters['kategori'])) $filterDesc[] = "Kategori: " . $filters['kategori'];
if (!empty($filters['prioritas'])) $filterDesc[] = "Prioritas: " . $filters['prioritas'];
if (!empty($filters['tanggal_dari'])) $filterDesc[] = "Dari: " . $filters['tanggal_dari'];
if (!empty($filters['tanggal_sampai'])) $filterDesc[] = "Sampai: " . $filters['tanggal_sampai'];
$filterText = empty($filterDesc) ? 'Semua Data' : implode(' | ', $filterDesc);

$tanggalCetak = date('d M Y, H:i');

// Build HTML for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #1e293b;
        margin: 0;
        padding: 20px;
    }
    .header {
        text-align: center;
        border-bottom: 3px solid #0ea5e9;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .header h1 {
        font-size: 22px;
        color: #0369a1;
        margin: 0;
    }
    .header p {
        color: #64748b;
        margin: 4px 0 0;
        font-size: 11px;
    }
    .meta-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 9px;
        color: #64748b;
    }
    .meta-left, .meta-right {
        display: inline-block;
    }
    .meta-right {
        text-align: right;
    }
    .stats-row {
        width: 100%;
        margin-bottom: 20px;
    }
    .stats-row td {
        text-align: center;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
    }
    .stats-row .stat-val {
        font-size: 20px;
        font-weight: bold;
        color: #0369a1;
    }
    .stats-row .stat-lbl {
        font-size: 9px;
        color: #64748b;
        text-transform: uppercase;
    }
    table.data {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    table.data th {
        background: #0ea5e9;
        color: #fff;
        padding: 8px 6px;
        text-align: left;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    table.data td {
        padding: 7px 6px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 9px;
        vertical-align: top;
    }
    table.data tr:nth-child(even) {
        background: #f8fafc;
    }
    .badge {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: bold;
    }
    .b-tinggi { background: #fee2e2; color: #dc2626; }
    .b-normal { background: #dcfce7; color: #16a34a; }
    .b-rendah { background: #f1f5f9; color: #64748b; }
    .b-menunggu { background: #f1f5f9; color: #64748b; }
    .b-diproses { background: #fef3c7; color: #d97706; }
    .b-selesai { background: #dcfce7; color: #16a34a; }
    .footer {
        margin-top: 30px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        text-align: center;
        font-size: 8px;
        color: #94a3b8;
    }
    .section-title {
        font-size: 13px;
        font-weight: bold;
        color: #0f172a;
        margin: 15px 0 8px;
        padding-bottom: 5px;
        border-bottom: 2px solid #e2e8f0;
    }
    .filter-info {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        padding: 8px 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        font-size: 9px;
        color: #0369a1;
    }
</style>
</head>
<body>

<div class="header">
    <h1>☕ NenaCare — Laporan K3</h1>
    <p>AI-Powered K3 Incident Reporting System | Nena Cafe</p>
</div>

<table width="100%" style="margin-bottom: 15px; font-size: 9px; color: #64748b;">
<tr>
    <td style="text-align: left;">Dicetak: ' . $tanggalCetak . '</td>
    <td style="text-align: right;">Total Data: ' . count($reports) . ' laporan</td>
</tr>
</table>

<div class="filter-info">
    <strong>Filter:</strong> ' . htmlspecialchars($filterText) . '
</div>

<table class="stats-row" cellspacing="5">
<tr>
    <td><div class="stat-val">' . $stats['total'] . '</div><div class="stat-lbl">Total</div></td>
    <td><div class="stat-val">' . $stats['menunggu'] . '</div><div class="stat-lbl">Menunggu</div></td>
    <td><div class="stat-val">' . $stats['diproses'] . '</div><div class="stat-lbl">Diproses</div></td>
    <td><div class="stat-val">' . $stats['selesai'] . '</div><div class="stat-lbl">Selesai</div></td>
    <td><div class="stat-val">' . $stats['tinggi'] . '</div><div class="stat-lbl">Prioritas Tinggi</div></td>
</tr>
</table>

<div class="section-title">📋 Data Laporan Insiden</div>

<table class="data">
<thead>
    <tr>
        <th>No</th>
        <th>Pelapor</th>
        <th>Kategori</th>
        <th>Lokasi</th>
        <th>Deskripsi</th>
        <th>Prioritas</th>
        <th>Saran AI</th>
        <th>Status</th>
        <th>Waktu</th>
    </tr>
</thead>
<tbody>';

$no = 1;
foreach ($reports as $r) {
    $isAnonim = isset($r['is_anonim']) && $r['is_anonim'];
    $nama = $isAnonim ? 'Anonim' : htmlspecialchars($r['nama_pelapor'] ?? '-');
    
    $priClass = 'b-normal';
    if ($r['prioritas'] === 'Tinggi') $priClass = 'b-tinggi';
    elseif ($r['prioritas'] === 'Rendah') $priClass = 'b-rendah';

    $stClass = 'b-menunggu';
    if ($r['status'] === 'Diproses') $stClass = 'b-diproses';
    elseif ($r['status'] === 'Selesai') $stClass = 'b-selesai';

    $desc = htmlspecialchars(mb_substr($r['deskripsi_kejadian'], 0, 80));
    if (mb_strlen($r['deskripsi_kejadian']) > 80) $desc .= '...';

    $saran = htmlspecialchars(mb_substr($r['saran_ai'] ?? '', 0, 80));
    if (mb_strlen($r['saran_ai'] ?? '') > 80) $saran .= '...';

    $html .= '
    <tr>
        <td>' . $no . '</td>
        <td>' . $nama . '</td>
        <td>' . htmlspecialchars($r['kategori_masalah']) . '</td>
        <td>' . htmlspecialchars($r['lokasi_kejadian']) . '</td>
        <td>' . $desc . '</td>
        <td><span class="badge ' . $priClass . '">' . htmlspecialchars($r['prioritas']) . '</span></td>
        <td style="font-size: 8px;">' . $saran . '</td>
        <td><span class="badge ' . $stClass . '">' . htmlspecialchars($r['status']) . '</span></td>
        <td style="white-space: nowrap;">' . date('d/m/Y H:i', strtotime($r['waktu_lapor'])) . '</td>
    </tr>';
    $no++;
}

if (empty($reports)) {
    $html .= '<tr><td colspan="9" style="text-align:center; padding:20px; color:#94a3b8;">Tidak ada data ditemukan.</td></tr>';
}

$html .= '
</tbody>
</table>

<div class="footer">
    <p>Dokumen ini digenerate secara otomatis oleh sistem NenaCare K3 — ' . $tanggalCetak . '</p>
    <p>© ' . date('Y') . ' Nena Cafe — AI-Powered K3 Incident Reporting System</p>
</div>

</body>
</html>';

// Generate PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = 'NenaCare_Laporan_K3_' . date('Y-m-d_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
