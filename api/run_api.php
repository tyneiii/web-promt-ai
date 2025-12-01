<?php 
require_once __DIR__ . '/key.php';  

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$API_TOKEN = HF_API_TOKEN ?? '';
$input = json_decode(file_get_contents("php://input"), true);
$prompt = trim($input["prompt"] ?? "");

if (empty($prompt)) {
    http_response_code(400);
    echo json_encode(["error" => "Prompt cannot be empty"]);
    exit;
}
if (empty($API_TOKEN)) {
    http_response_code(500);
    echo json_encode(["error" => "API token not configured"]);
    exit;
}

$model = "openai/gpt-oss-120b";
//$model = "Llava-1.5";
$endpoint = "https://router.huggingface.co/v1/chat/completions";

$payload = [
    "model" => $model,
    "messages" => [["role" => "user", "content" => $prompt]],
    "max_tokens" => 1024,
    "temperature" => 0.7
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
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HEADER => true // LẤY CẢ HEADER ĐỂ CHECK STATUS
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// Nếu lỗi khi gọi cURL
if ($response === false || !empty($curlError)) {
    http_response_code(500);
    echo json_encode(["error" => "cURL error: $curlError"]);
    exit;
}

// Tách Header + Body
list($rawHeader, $rawBody) = explode("\r\n\r\n", $response, 2);

// Nếu HuggingFace trả về HTTP lỗi -> gửi lại nguyên body để debug
if ($httpCode >= 400) {
    http_response_code($httpCode);
    echo json_encode([
        "error" => "Hugging Face HTTP error $httpCode",
        "raw"   => $rawBody
    ]);
    exit;
}

// Parse JSON
$data = json_decode($rawBody, true);

// JSON lỗi
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "error" => "Invalid JSON from Hugging Face",
        "raw"   => $rawBody
    ]);
    exit;
}

// HuggingFace API error
if (isset($data['error'])) {
    http_response_code(400);
    echo json_encode(["error" => $data['error']['message']]);
    exit;
}

// Không có choices
if (!isset($data['choices']) || empty($data['choices'])) {
    http_response_code(400);
    echo json_encode([
        "error" => "No 'choices' in API response",
        "raw"   => $data
    ]);
    exit;
}

$result = $data['choices'][0]['message']['content'] ?? 'No text generated';

echo json_encode(["result" => trim($result)]);
exit;
