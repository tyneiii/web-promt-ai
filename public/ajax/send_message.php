<?php
include_once __DIR__ . "/../../controller/user/chat.php";
include_once __DIR__ . "/../../config.php";
header('Content-Type: application/json');
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);
if (!function_exists('checkCsrfToken')) {
    function checkCsrfToken(string $token): bool
    {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($data['message']) || !isset($data['csrf_token'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request method or missing data (message/token).']);
    exit;
}
if (!checkCsrfToken($data['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF Token mismatch or missing.', 'step' => 'CSRF_CHECK']);
    exit;
}
$account_id = (int)($_SESSION['account_id'] ?? 0);
$message = trim($data['message']);
$chat_id = (int)($data['chat_id'] ?? 0);
if ($account_id === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User ID is missing or 0. Did you forget session_start()?', 'step' => 'AUTH_CHECK']);
    exit;
}
if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message content cannot be empty.', 'step' => 'INPUT_VALIDATION']);
    exit;
}

if (!$conn || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'step' => 'DB_CONNECTION', 'error' => 'Database connection failed.']);
    exit;
}

if (!is_int($chat_id) || $chat_id <= 0) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'step' => 'GET_CHAT_ID',
        'error' => 'Failed to retrieve or create chat ID. Chat ID returned: ' . $chat_id
    ]);
    exit;
}

$result = saveMessage($conn, $chat_id, $account_id, $message);

if ($result === true) {
    $conn->close();
    echo json_encode([
        'success' => true,
        'chat_id' => $chat_id,
        'message_sent' => htmlspecialchars($message)
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'step' => 'SAVE_MESSAGE',
        'error' => 'Failed to save message to database.',
        'details' => $result
    ]);
    $conn->close();
}
