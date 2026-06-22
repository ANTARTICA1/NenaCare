<?php

function loadEnv($path) {
    if(!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim(trim($value, '"'), "'"); 
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root'); 
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');     
define('DB_NAME', getenv('DB_NAME') ?: 'db_nena_k3');

define('BOT_TOKEN', getenv('BOT_TOKEN'));
define('ADMIN_ID', getenv('ADMIN_ID'));
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY'));

$db = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($db->connect_error) {
    die("Koneksi database gagal: " . $db->connect_error);
}

$db->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
$db->select_db(DB_NAME);

$tableUsers = "CREATE TABLE IF NOT EXISTS users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('Admin','Manajer') NOT NULL
)";
$db->query($tableUsers);

$tableLaporan = "CREATE TABLE IF NOT EXISTS laporan_k3 (
  id_laporan INT AUTO_INCREMENT PRIMARY KEY,
  nama_pelapor VARCHAR(100) NULL,
  tipe_pelapor VARCHAR(20) NULL,
  kategori_masalah VARCHAR(50) NOT NULL,
  lokasi_kejadian VARCHAR(100) NOT NULL,
  deskripsi_kejadian TEXT NOT NULL,
  is_anonim TINYINT(1) DEFAULT 0,
  prioritas ENUM('Rendah','Normal','Tinggi') DEFAULT 'Normal',
  saran_ai TEXT,
  status ENUM('Menunggu','Diproses','Selesai') DEFAULT 'Menunggu',
  telegram_message_id BIGINT,
  waktu_lapor TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$db->query($tableLaporan);

$db->query("ALTER TABLE laporan_k3 MODIFY COLUMN nama_pelapor VARCHAR(100) NULL");
$db->query("ALTER TABLE laporan_k3 MODIFY COLUMN tipe_pelapor VARCHAR(20) NULL");

$colCheck = $db->query("SHOW COLUMNS FROM laporan_k3 LIKE 'is_anonim'");
if ($colCheck && $colCheck->num_rows === 0) {
    $db->query("ALTER TABLE laporan_k3 ADD COLUMN is_anonim TINYINT(1) DEFAULT 0 AFTER deskripsi_kejadian");
}

$tableCatatan = "CREATE TABLE IF NOT EXISTS catatan_admin (
  id_catatan INT AUTO_INCREMENT PRIMARY KEY,
  id_laporan INT NOT NULL,
  id_user INT NOT NULL,
  catatan TEXT NOT NULL,
  waktu_catatan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$db->query($tableCatatan);

$userCount = $db->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'];
if ($userCount == 0) {
    $defaultUser = 'admin';
    $defaultPass = password_hash('admin123', PASSWORD_DEFAULT);
    $defaultRole = 'Admin';
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $defaultUser, $defaultPass, $defaultRole);
    $stmt->execute();
}
?>
