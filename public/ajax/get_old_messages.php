<?php
include_once __DIR__ . "/../../controller/user/chat.php";
include_once __DIR__ . "/../../config.php";
header('Content-Type: application/json');

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);
define('MESSAGE_LIMIT', 5);
$account_id = (int)($_SESSION['account_id'] ?? 0);
$oldest_id = filter_var($data['oldest_id'] ?? null, FILTER_VALIDATE_INT);
$csrf_token = trim($data['csrf_token'] ?? '');
$chat_id = filter_var($data['chat_id'] ?? null, FILTER_VALIDATE_INT);
if (!function_exists('checkCsrfToken')) {
    function checkCsrfToken(string $token): bool
    {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if ($account_id === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập.']);
    exit;
}

if (!checkCsrfToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF Token mismatch.', 'step' => 'CSRF_CHECK']);
    exit;
}

if (!$oldest_id || !$chat_id) {
    echo json_encode(['success' => false, 'messages' => [], 'error' => 'Thiếu ID chat hoặc ID tin nhắn cũ nhất hợp lệ.']);
    exit;
}
$messages = getMessages($conn, $chat_id, MESSAGE_LIMIT, $oldest_id);
if ($messages === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Lỗi truy vấn cơ sở dữ liệu.']);
    exit;
}
$safe_messages = array_map(function ($message) {
    $message['message'] = nl2br(htmlspecialchars($message['message']));
    $message['chat_detail_id'] = $message['chat_detail_id'] ?? null;
    return $message;
}, $messages);
echo json_encode([
    'success' => true,
    'messages' => $safe_messages,
    'count' => count($messages)
]);
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
