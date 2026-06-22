CREATE DATABASE IF NOT EXISTS db_nena_k3;
USE db_nena_k3;

CREATE TABLE IF NOT EXISTS users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('Admin','Manajer') NOT NULL
);

CREATE TABLE IF NOT EXISTS laporan_k3 (
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
);

CREATE TABLE IF NOT EXISTS catatan_admin (
  id_catatan INT AUTO_INCREMENT PRIMARY KEY,
  id_laporan INT NOT NULL,
  id_user INT NOT NULL,
  catatan TEXT NOT NULL,
  waktu_catatan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
