<?php
include_once __DIR__ . "/../../Controller/user/chat.php";
include_once __DIR__ . "/../../config.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);
define('MESSAGE_LIMIT', 2); 
$account_id = (int)($_SESSION['id_user'] ?? 0);
$oldest_id = filter_var($data['oldest_id'] ?? null, FILTER_VALIDATE_INT); 
$csrf_token = trim($data['csrf_token'] ?? '');
if ($account_id === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập.']);
    exit;
}
if (!$oldest_id) {
    echo json_encode(['success' => true, 'messages' => [], 'error' => 'Không có ID cũ nhất hợp lệ.']);
    exit;
}
$chat_id = (int)getIDChat($conn, $account_id);
if (!is_int($chat_id) || $chat_id <= 0) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Lỗi lấy ID chat.']);
    exit;
}
$messages = getMessages($conn, $chat_id, $oldest_id, MESSAGE_LIMIT); 
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
?>