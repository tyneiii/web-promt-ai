<?php
// api/run_api.php - PHIÊN BẢN 2025: DÙNG INFERENCE PROVIDERS + CHAT COMPLETIONS (HTTP 200 OK!)

$logFile = __DIR__ . '/debug.log';
function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $msg . "\n", FILE_APPEND | LOCK_EX);
}

logMsg("=== YÊU CẦU MỚI (2025 FIXED) ===");

$keyPath = __DIR__ . '/key.php';
if (!file_exists($keyPath)) {
    logMsg("KHÔNG TÌM THẤY key.php");
    echo json_encode(["success" => false, "error" => "Thiếu file key.php"]);
    exit;
}
require_once $keyPath;

$API_TOKEN = $API_TOKEN ?? '';
if (empty($API_TOKEN)) {
    logMsg("TOKEN RỖNG");
    echo json_encode(["success" => false, "error" => "Token HF trống trong key.php"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$prompt = trim($input["prompt"] ?? "");
if (empty($prompt)) {
    echo json_encode(["success" => false, "error" => "Prompt trống"]);
    exit;
}
logMsg("Prompt: " . substr($prompt, 0, 100));

// MODEL ĐÃ TEST CHẠY NGON 28/11/2025 (Miễn phí, hỗ trợ tiếng Việt tốt)
$models = [
    "HuggingFaceTB/SmolLM3-3B:hf-inference",           // Nhanh, CPU, luôn available
    "openai/gpt-oss-120b",                             // Mạnh, open-weights
    "zai-org/GLM-4.6:cerebras",                        // Tiếng Việt siêu tốt
    "moonshotai/Kimi-K2-Instruct-0905",                // Multilingual, nhanh
    "CohereLabs/command-a-vision-07-2025:cohere"       // Vision + text, fallback
];

$endpoint = "https://router.huggingface.co/v1/chat/completions";  // ENDPOINT MỚI 2025!

foreach ($models as $model) {
    logMsg("Đang thử: $model");

    // SYSTEM PROMPT ÉP TIẾNG VIỆT
    $systemPrompt = "Bạn là trợ lý AI hữu ích. Trả lời bằng tiếng Việt tự nhiên, chính xác. Nếu là code, giải thích rõ ràng.";

    $payload = [
        "model" => $model,
        "messages" => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $prompt]
        ],
        "max_tokens" => 1024,
        "temperature" => 0.7,
        "top_p" => 0.95
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $API_TOKEN",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    logMsg("HTTP $httpCode - Model: $model | Response preview: " . substr($response, 0, 200));

    if ($error) {
        logMsg("cURL Error: $error");
        continue;
    }

    if ($httpCode === 503 || $httpCode === 429) {
        logMsg("Model đang load hoặc rate limit");
        sleep(2);  // Đợi 2s trước khi thử model khác
        continue;
    }

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $result = trim($data['choices'][0]['message']['content']);
            if (strlen($result) > 5) {  // Kết quả hợp lệ
                logMsg("THÀNH CÔNG với $model! Độ dài: " . strlen($result));
                echo json_encode([
                    "success" => true,
                    "result" => $result,
                    "model" => $model
                ]);
                exit;
            }
        }
        logMsg("Response 200 nhưng không có content hợp lệ");
    } else {
        logMsg("HTTP $httpCode - Không phải 200");
    }
}

// Nếu tất cả fail
logMsg("TẤT CẢ MODEL FAIL (kiểm tra token hoặc quota)");
echo json_encode([
    "success" => false,
    "error" => "Các model đang bận (HTTP 410/503). Thử lại sau 1 phút hoặc tạo token mới tại https://huggingface.co/settings/tokens"
]);
?>