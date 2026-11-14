<?php
require_once 'config.php';
require_once 'classes/AuthManager.php';
require_once 'classes/ReportManager.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$auth = new AuthManager($db);
$auth->requireLogin();
$user = $auth->getCurrentUser();

$manager = new ReportManager($db);

$filters = [];
if (!empty($_GET['q'])) $filters['q'] = trim($_GET['q']);
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
if (!empty($_GET['kategori'])) $filters['kategori'] = $_GET['kategori'];
if (!empty($_GET['prioritas'])) $filters['prioritas'] = $_GET['prioritas'];
if (!empty($_GET['tanggal_dari'])) $filters['tanggal_dari'] = $_GET['tanggal_dari'];
if (!empty($_GET['tanggal_sampai'])) $filters['tanggal_sampai'] = $_GET['tanggal_sampai'];

$reports = $manager->getFilteredReports($filters);
$stats = $manager->getStatistics();

$filterDesc = [];
if (!empty($filters['q'])) $filterDesc[] = "Kata Kunci: " . $filters['q'];
if (!empty($filters['status'])) $filterDesc[] = "Status: " . $filters['status'];
if (!empty($filters['kategori'])) $filterDesc[] = "Kategori: " . $filters['kategori'];
if (!empty($filters['prioritas'])) $filterDesc[] = "Prioritas: " . $filters['prioritas'];
if (!empty($filters['tanggal_dari'])) $filterDesc[] = "Dari: " . $filters['tanggal_dari'];
if (!empty($filters['tanggal_sampai'])) $filterDesc[] = "Sampai: " . $filters['tanggal_sampai'];
$filterText = empty($filterDesc) ? 'Seluruh Data (Tanpa Filter)' : implode(' | ', $filterDesc);

$tanggalCetak = date('d M Y, H:i');

$html = '
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-size: 9px;
        color: #1e293b;
        margin: 0;
        padding: 15px;
        line-height: 1.4;
    }
    .header-table {
        width: 100%;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 10px;
        margin-bottom: 12px;
    }
    .header-table td {
        vertical-align: middle;
    }
    .company-title {
        font-size: 16px;
        font-weight: bold;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.5px;
    }
    .company-subtitle {
        font-size: 9px;
        color: #475569;
        margin: 2px 0 0;
    }
    .doc-meta {
        text-align: right;
        font-size: 8px;
        color: #64748b;
    }
    .filter-box {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        margin-bottom: 12px;
        font-size: 8px;
        color: #334155;
    }
    .stats-table {
        width: 100%;
        margin-bottom: 14px;
        border-collapse: collapse;
    }
    .stats-table td {
        text-align: center;
        padding: 8px 6px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        width: 20%;
    }
    .stat-val {
        font-size: 14px;
        font-weight: bold;
        color: #0284c7;
    }
    .stat-lbl {
        font-size: 7.5px;
        color: #64748b;
        text-transform: uppercase;
        margin-top: 2px;
    }
    .section-title {
        font-size: 10px;
        font-weight: bold;
        color: #0f172a;
        text-transform: uppercase;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
    }
    table.data {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }
    table.data th {
        background: #0f172a;
        color: #ffffff;
        padding: 6px 5px;
        text-align: left;
        font-size: 7.5px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border: 1px solid #0f172a;
    }
    table.data td {
        padding: 6px 5px;
        border: 1px solid #e2e8f0;
        font-size: 8px;
        vertical-align: top;
    }
    table.data tr:nth-child(even) td {
        background: #f8fafc;
    }
    .badge {
        padding: 2px 5px;
        font-size: 7px;
        font-weight: bold;
        display: inline-block;
        border-radius: 2px;
    }
    .b-tinggi { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
    .b-normal { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .b-rendah { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .b-menunggu { background: #f1f5f9; color: #475569; }
    .b-diproses { background: #e0f2fe; color: #0369a1; }
    .b-selesai { background: #dcfce7; color: #15803d; }
    
    .signature-table {
        width: 100%;
        margin-top: 20px;
        page-break-inside: avoid;
    }
    .signature-table td {
        width: 50%;
        text-align: center;
        vertical-align: top;
        font-size: 8.5px;
    }
    .sign-space {
        height: 50px;
    }
    .footer {
        margin-top: 20px;
        padding-top: 8px;
        border-top: 1px solid #cbd5e1;
        font-size: 7.5px;
        color: #94a3b8;
        text-align: center;
    }
</style>
</head>
<body>

<table class="header-table">
    <tr>
        <td>
            <div class="company-title">NENACARE K3 — AUDIT KESELAMATAN KERJA</div>
            <div class="company-subtitle">Divisi Health, Safety & Environment (HSE) | Nena Cafe Enterprise</div>
        </td>
        <td class="doc-meta">
            <div><strong>Format:</strong> Berkas Audit Resmi</div>
            <div><strong>Waktu Ekspor:</strong> ' . $tanggalCetak . '</div>
            <div><strong>Petugas:</strong> ' . htmlspecialchars($user['username']) . ' (' . htmlspecialchars($user['role']) . ')</div>
        </td>
    </tr>
</table>

<div class="filter-box">
    <strong>Kriteria Data:</strong> ' . htmlspecialchars($filterText) . ' &nbsp;|&nbsp; <strong>Total Hasil:</strong> ' . count($reports) . ' Rekam Insiden
</div>

<table class="stats-table">
    <tr>
        <td><div class="stat-val">' . $stats['total'] . '</div><div class="stat-lbl">Total Insiden</div></td>
        <td><div class="stat-val">' . $stats['menunggu'] . '</div><div class="stat-lbl">Menunggu Tindakan</div></td>
        <td><div class="stat-val">' . $stats['diproses'] . '</div><div class="stat-lbl">Dalam Penanganan</div></td>
        <td><div class="stat-val">' . $stats['selesai'] . '</div><div class="stat-lbl">Telah Selesai</div></td>
        <td><div class="stat-val" style="color: #dc2626;">' . $stats['tinggi'] . '</div><div class="stat-lbl">Prioritas Tinggi</div></td>
    </tr>
</table>

<div class="section-title">TABEL REKAM KRONOLOGI INSIDEN & TINDAK LANJUT</div>

<table class="data">
<thead>
    <tr>
        <th style="width: 20px;">No</th>
        <th style="width: 55px;">ID</th>
        <th style="width: 80px;">Pelapor</th>
        <th style="width: 80px;">Kategori</th>
        <th style="width: 75px;">Lokasi</th>
        <th>Deskripsi Kejadian</th>
        <th style="width: 55px;">Prioritas</th>
        <th>Rekomendasi Tindakan K3</th>
        <th style="width: 55px;">Status</th>
        <th style="width: 65px;">Waktu Lapor</th>
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

    $desc = htmlspecialchars($r['deskripsi_kejadian']);
    $saran = htmlspecialchars($r['saran_ai'] ?? '-');

    $html .= '
    <tr>
        <td style="text-align: center;">' . $no . '</td>
        <td style="font-family: DejaVu Sans Mono, monospace;">#LAP-' . str_pad($r['id_laporan'], 4, '0', STR_PAD_LEFT) . '</td>
        <td>' . $nama . '</td>
        <td>' . htmlspecialchars($r['kategori_masalah']) . '</td>
        <td>' . htmlspecialchars($r['lokasi_kejadian']) . '</td>
        <td>' . $desc . '</td>
        <td><span class="badge ' . $priClass . '">' . htmlspecialchars($r['prioritas']) . '</span></td>
        <td>' . $saran . '</td>
        <td><span class="badge ' . $stClass . '">' . htmlspecialchars($r['status']) . '</span></td>
        <td style="white-space: nowrap;">' . date('d/m/Y H:i', strtotime($r['waktu_lapor'])) . '</td>
    </tr>';
    $no++;
}

if (empty($reports)) {
    $html .= '<tr><td colspan="10" style="text-align: center; padding: 18px; color: #64748b;">Tidak ada data laporan yang sesuai dengan kriteria filter.</td></tr>';
}

$html .= '
</tbody>
</table>

<table class="signature-table">
    <tr>
        <td>
            <div>Disusun Oleh:</div>
            <div class="sign-space"></div>
            <div><strong>(' . htmlspecialchars($user['username']) . ')</strong></div>
            <div style="color: #64748b;">Petugas / Auditor K3</div>
        </td>
        <td>
            <div>Mengetahui & Menyetujui:</div>
            <div class="sign-space"></div>
            <div><strong>( ___________________________ )</strong></div>
            <div style="color: #64748b;">Manajer Operasional Nena Cafe</div>
        </td>
    </tr>
</table>

<div class="footer">
    Dokumen rahasia operasional internal Nena Cafe. Dilarang menggandakan atau menyebarluaskan tanpa izin tertulis dari manajemen K3. Dicetak pada ' . $tanggalCetak . '.
</div>

</body>
</html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = 'NenaCare_Audit_K3_' . date('Y-m-d_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
