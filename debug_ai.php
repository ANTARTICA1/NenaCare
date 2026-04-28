<?php
require_once 'config.php';

echo "<h1>Debug Gemini API</h1>";

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . GEMINI_API_KEY;

$prompt = "Ini hanya test. Jawab singkat: OK.";
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

echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>";
echo "<strong>--- HTTP Headers dari Google ---</strong>\n";
if (isset($http_response_header)) {
    print_r($http_response_header);
} else {
    echo "Tidak ada HTTP Headers. Mungkin koneksi gagal sepenuhnya.\n";
}

echo "\n<strong>--- Hasil Raw Response (JSON) ---</strong>\n";
if ($result !== false) {
    echo htmlspecialchars($result);
    echo "\n\n<strong>--- Parsing JSON ---</strong>\n";
    $json = json_decode($result, true);
    if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
        echo "Teks didapat: " . $json['candidates'][0]['content']['parts'][0]['text'];
    } else {
        echo "Gagal menemukan teks di dalam struktur candidates.\n";
        print_r($json);
    }
} else {
    echo "file_get_contents mengembalikan FALSE.";
}
echo "</pre>";
?>
