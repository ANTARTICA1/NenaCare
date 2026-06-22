<?php
class ReportManager {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function getAiAdvice($kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . GEMINI_API_KEY;
        
        $prompt = "Kamu adalah Auditor K3 di lingkungan Food & Beverage (Kafe).
Analisis masalah berikut:
- Kategori: $kategori_masalah
- Lokasi: $lokasi_kejadian
- Deskripsi: $deskripsi_kejadian

Tentukan prioritas (Tinggi/Normal/Rendah) berdasarkan risiko dan berikan saran tindakan yang praktis 1 kalimat singkat.
WAJIB: Output HANYA JSON valid tanpa teks tambahan atau format markdown.
Format:
{\"prioritas\": \"Tinggi\", \"saran_ai\": \"Tindakan perbaikan...\"}";
        
        $data = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ]
        ];

        $options = [
            "http" => [
                "header"  => "Content-type: application/json\r\n",
                "method"  => "POST",
                "content" => json_encode($data),
                "ignore_errors" => true 
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result) {
            $response = json_decode($result, true);
            
            if (isset($response['error'])) {
                return json_encode(["prioritas" => "Normal", "saran_ai" => "AI Error: " . $response['error']['message']]);
            }

            if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                $text = trim($response['candidates'][0]['content']['parts'][0]['text']);
                $text = str_replace(['```json', '```'], '', $text);
                return trim($text);
            }
        }
        return json_encode(["prioritas" => "Normal", "saran_ai" => "Saran perbaikan AI tidak tersedia (Cek Koneksi/API Key)."]);
    }

    public function saveReport($nama_pelapor, $tipe_pelapor, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian, $is_anonim = 0) {
        $aiResultRaw = $this->getAiAdvice($kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian);
        $aiData = json_decode($aiResultRaw, true);
        
        $prioritas = isset($aiData['prioritas']) ? $aiData['prioritas'] : 'Normal';
        $saran_ai = isset($aiData['saran_ai']) ? $aiData['saran_ai'] : 'Saran perbaikan AI tidak tersedia.';

        if ($is_anonim) {
            $nama_pelapor = null;
            $tipe_pelapor = null;
        }

        $query = "INSERT INTO laporan_k3 (nama_pelapor, tipe_pelapor, kategori_masalah, lokasi_kejadian, deskripsi_kejadian, is_anonim, prioritas, saran_ai, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu')";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssiss", $nama_pelapor, $tipe_pelapor, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian, $is_anonim, $prioritas, $saran_ai);
        
        if ($stmt->execute()) {
            $reportId = $stmt->insert_id;
            $displayName = $is_anonim ? 'Anonim' : $nama_pelapor;
            $displayType = $is_anonim ? '-' : $tipe_pelapor;
            $this->sendTelegramNotification($reportId, $displayName, $displayType, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian, $prioritas, $saran_ai);
            return true;
        }
        return false;
    }

    private function sendTelegramNotification($reportId, $nama_pelapor, $tipe_pelapor, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian, $prioritas, $saran_ai) {
        $message = "🚨 *LAPORAN K3 NENA CAFE* 🚨\n\n";
        $message .= "👨‍💼 *Pelapor:* $nama_pelapor ($tipe_pelapor)\n";
        $message .= "📍 *Lokasi:* $lokasi_kejadian\n";
        $message .= "📂 *Kategori:* $kategori_masalah\n";
        $message .= "📝 *Deskripsi:* $deskripsi_kejadian\n\n";
        $message .= "🤖 *Analisis AI:*\n";
        $message .= "⚠️ [$prioritas] - $saran_ai\n";

        $keyboard = [
            'inline_keyboard' => [[
                ['text' => '🚀 Proses', 'callback_data' => "action_proses_$reportId"],
                ['text' => '✅ Selesai', 'callback_data' => "action_selesai_$reportId"]
            ]]
        ];

        $data = [
            'chat_id' => ADMIN_ID,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ];

        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
        $options = [
            "http" => [
                "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "POST",
                "content" => http_build_query($data),
                "ignore_errors" => true
            ],
            "ssl" => ["verify_peer" => false, "verify_peer_name" => false]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result) {
            $response = json_decode($result, true);
            if (isset($response['result']['message_id'])) {
                $messageId = $response['result']['message_id'];
                $stmt = $this->db->prepare("UPDATE laporan_k3 SET telegram_message_id = ? WHERE id_laporan = ?");
                $stmt->bind_param("ii", $messageId, $reportId);
                $stmt->execute();
            }
        }
    }

    public function getAllReports() {
        $result = $this->db->query("SELECT * FROM laporan_k3 ORDER BY waktu_lapor DESC");
        $reports = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
        }
        return $reports;
    }

    // ===================== NEW METHODS =====================

    public function getReportById($id) {
        $stmt = $this->db->prepare("SELECT * FROM laporan_k3 WHERE id_laporan = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateStatus($id, $status) {
        $allowed = ['Menunggu', 'Diproses', 'Selesai'];
        if (!in_array($status, $allowed)) return false;

        $stmt = $this->db->prepare("UPDATE laporan_k3 SET status = ? WHERE id_laporan = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function getStatistics() {
        $stats = [];

        $res = $this->db->query("SELECT COUNT(*) as total FROM laporan_k3");
        $stats['total'] = $res->fetch_assoc()['total'];

        $res = $this->db->query("SELECT COUNT(*) as c FROM laporan_k3 WHERE status='Menunggu'");
        $stats['menunggu'] = $res->fetch_assoc()['c'];

        $res = $this->db->query("SELECT COUNT(*) as c FROM laporan_k3 WHERE status='Diproses'");
        $stats['diproses'] = $res->fetch_assoc()['c'];

        $res = $this->db->query("SELECT COUNT(*) as c FROM laporan_k3 WHERE status='Selesai'");
        $stats['selesai'] = $res->fetch_assoc()['c'];

        $res = $this->db->query("SELECT COUNT(*) as c FROM laporan_k3 WHERE prioritas='Tinggi'");
        $stats['tinggi'] = $res->fetch_assoc()['c'];

        // Distribution by category
        $res = $this->db->query("SELECT kategori_masalah, COUNT(*) as c FROM laporan_k3 GROUP BY kategori_masalah ORDER BY c DESC");
        $stats['by_kategori'] = [];
        while ($row = $res->fetch_assoc()) {
            $stats['by_kategori'][] = $row;
        }

        // Distribution by status
        $res = $this->db->query("SELECT status, COUNT(*) as c FROM laporan_k3 GROUP BY status");
        $stats['by_status'] = [];
        while ($row = $res->fetch_assoc()) {
            $stats['by_status'][] = $row;
        }

        return $stats;
    }

    public function getFilteredReports($filters = []) {
        $where = [];
        $params = [];
        $types = '';

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        if (!empty($filters['kategori'])) {
            $where[] = "kategori_masalah = ?";
            $params[] = $filters['kategori'];
            $types .= 's';
        }
        if (!empty($filters['prioritas'])) {
            $where[] = "prioritas = ?";
            $params[] = $filters['prioritas'];
            $types .= 's';
        }
        if (!empty($filters['tanggal_dari'])) {
            $where[] = "DATE(waktu_lapor) >= ?";
            $params[] = $filters['tanggal_dari'];
            $types .= 's';
        }
        if (!empty($filters['tanggal_sampai'])) {
            $where[] = "DATE(waktu_lapor) <= ?";
            $params[] = $filters['tanggal_sampai'];
            $types .= 's';
        }

        $sql = "SELECT * FROM laporan_k3";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY waktu_lapor DESC";

        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }

        $reports = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
        }
        return $reports;
    }

    public function addNote($id_laporan, $id_user, $catatan) {
        $stmt = $this->db->prepare("INSERT INTO catatan_admin (id_laporan, id_user, catatan) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_laporan, $id_user, $catatan);
        return $stmt->execute();
    }

    public function getNotesByReport($id_laporan) {
        $stmt = $this->db->prepare("SELECT c.*, u.username, u.role FROM catatan_admin c JOIN users u ON c.id_user = u.id_user WHERE c.id_laporan = ? ORDER BY c.waktu_catatan DESC");
        $stmt->bind_param("i", $id_laporan);
        $stmt->execute();
        $result = $stmt->get_result();
        $notes = [];
        while ($row = $result->fetch_assoc()) {
            $notes[] = $row;
        }
        return $notes;
    }
}
?>