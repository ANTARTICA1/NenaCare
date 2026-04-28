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

    public function saveReport($nama_pelapor, $tipe_pelapor, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian) {
        $aiResultRaw = $this->getAiAdvice($kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian);
        $aiData = json_decode($aiResultRaw, true);
        
        $prioritas = isset($aiData['prioritas']) ? $aiData['prioritas'] : 'Normal';
        $saran_ai = isset($aiData['saran_ai']) ? $aiData['saran_ai'] : 'Saran perbaikan AI tidak tersedia.';

        $query = "INSERT INTO laporan_k3 (nama_pelapor, tipe_pelapor, kategori_masalah, lokasi_kejadian, deskripsi_kejadian, prioritas, saran_ai, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu')";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssss", $nama_pelapor, $tipe_pelapor, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian, $prioritas, $saran_ai);
        
        if ($stmt->execute()) {
            $reportId = $stmt->insert_id;
            $this->sendTelegramNotification($reportId, $nama_pelapor, $tipe_pelapor, $kategori_masalah, $lokasi_kejadian, $deskripsi_kejadian, $prioritas, $saran_ai);
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
}
?>