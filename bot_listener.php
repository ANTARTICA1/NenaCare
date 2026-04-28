<?php


require_once 'config.php';

echo "==============================================\n";
echo "🤖 CampusCare Telegram Bot Listener Berjalan!\n";
echo "Menunggu interaksi dari admin melalui Telegram...\n";
echo "Tekan Ctrl+C untuk berhenti.\n";
echo "==============================================\n";

$lastUpdateId = 0;

while (true) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getUpdates?offset=" . ($lastUpdateId + 1) . "&timeout=30";
    
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['result']) && is_array($data['result'])) {
            foreach ($data['result'] as $update) {
                $lastUpdateId = $update['update_id'];

                if (isset($update['callback_query'])) {
                    $callbackQuery = $update['callback_query'];
                    $callbackData = $callbackQuery['data'];
                    $chatId = $callbackQuery['message']['chat']['id'];
                    $messageId = $callbackQuery['message']['message_id'];
                    $callbackQueryId = $callbackQuery['id'];

                    $status = null;
                    $reportId = null;

                    if (strpos($callbackData, 'action_proses_') === 0) {
                        $reportId = str_replace('action_proses_', '', $callbackData);
                        $status = 'Diproses';
                    } elseif (strpos($callbackData, 'action_selesai_') === 0) {
                        $reportId = str_replace('action_selesai_', '', $callbackData);
                        $status = 'Selesai';
                    }

                    if ($status && $reportId) {
                        $stmt = $db->prepare("UPDATE laporan_k3 SET status = ? WHERE id_laporan = ?");
                        $stmt->bind_param("si", $status, $reportId);
                        
                        if ($stmt->execute()) {
                            $waktu = date('Y-m-d H:i:s');
                            echo "[$waktu] Laporan #$reportId di-update menjadi: $status.\n";

                            $originalText = $callbackQuery['message']['text'];
                            
                            $newText = $originalText . "\n\n✔️ *Status Saat Ini:* " . $status;

                            $editUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/editMessageText";
                            $editData = [
                                'chat_id' => $chatId,
                                'message_id' => $messageId,
                                'text' => $newText,
                                'parse_mode' => 'Markdown'
                            ];

                            $options = [
                                "http" => [
                                    "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
                                    "method"  => "POST",
                                    "content" => http_build_query($editData),
                                    "ignore_errors" => true
                                ]
                            ];
                            $context = stream_context_create($options);
                            @file_get_contents($editUrl, false, $context);

                            $answerUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery";
                            $answerData = [
                                'callback_query_id' => $callbackQueryId,
                                'text' => "Status diupdate menjadi $status!"
                            ];
                            $optAnswer = [
                                "http" => [
                                    "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
                                    "method"  => "POST",
                                    "content" => http_build_query($answerData)
                                ]
                            ];
                            $ctxAnswer = stream_context_create($optAnswer);
                            @file_get_contents($answerUrl, false, $ctxAnswer);
                        }
                    }
                }
            }
        }
    }
    sleep(1);
}
?>
