<?php
require_once __DIR__ . '/key.php';  

// Bật debug tạm thời (tắt sau test bằng cách comment)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers cho JSON và CORS
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS (cho CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Lấy API token từ config
$API_TOKEN = HF_API_TOKEN ?? ''; // Fallback nếu chưa define

$input = json_decode(file_get_contents("php://input"), true);
$prompt = trim($input["prompt"] ?? "");

if (empty($prompt)) {
    http_response_code(400);
    echo json_encode(["error" => "Prompt cannot be empty"]);
    exit;
}

if (empty($API_TOKEN)) {
    http_response_code(500);
    echo json_encode(["error" => "Hugging Face API token not configured in config.php"]);
    exit;
}

// Model: openai/gpt-oss-120b (hỗ trợ free tier 2025, chat generation tốt, dài output)
$model = "openai/gpt-oss-120b";
$endpoint = "https://router.huggingface.co/v1/chat/completions";  // OpenAI-compatible endpoint 2025

$payload = [
    "model" => $model,
    "messages" => [
        ["role" => "user", "content" => $prompt]
    ],
    "max_tokens" => 300,  // Tăng giới hạn cho ý tưởng dài
    "temperature" => 0.7
];  // Payload chat format

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $API_TOKEN",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle cURL error
if ($response === false || !empty($curlError)) {
    http_response_code(500);
    echo json_encode(["error" => "cURL error: " . $curlError]);
    exit;
}

// Log raw response để debug (xóa sau khi test OK)
file_put_contents(__DIR__ . '/hf_debug.txt', date('Y-m-d H:i:s') . " - Raw response: " . $response . "\n", FILE_APPEND | LOCK_EX);

// Parse response an toàn (check JSON and array)
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid response from Hugging Face (raw: " . substr($response, 0, 200) . "). Thử lại sau 1-2 phút nếu model đang load."]);
    exit;
}

// Handle Hugging Face errors
if (isset($data['error'])) {
    http_response_code(400);
    echo json_encode(["error" => "Hugging Face API error: " . $data['error']['message']]);
    exit;
}

// Parse response (OpenAI-compatible: choices[0].message.content)
if (empty($data['choices']) || !isset($data['choices'][0]['message']['content'])) {
    echo json_encode(["error" => "No generated text from model. Thử prompt khác hoặc chờ model load."]);
    exit;
}

$result = $data['choices'][0]['message']['content'] ?? '';

// Trả về kết quả (loại bỏ prompt gốc nếu lặp lại)
$result = str_replace($prompt, '', $result);  // Clean output
if (empty(trim($result))) {
    $result = $data['choices'][0]['message']['content'] ?? 'No text generated';
}

echo json_encode([
    "result" => trim($result)
]);

// Log debug
file_put_contents(__DIR__ . '/log.txt', date('Y-m-d H:i:s') . " - Prompt: $prompt | Result length: " . strlen($result) . "\n", FILE_APPEND | LOCK_EX);
?>