<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/user/prompt.php'; // chứa lovePrompt(), savePrompt()

header('Content-Type: application/json');

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$account_id = (int)$_SESSION['account_id'];
$action = $_POST['action'] ?? '';
$prompt_id = (int)($_POST['prompt_id'] ?? 0);

if ($prompt_id <= 0 || !in_array($action, ['love', 'unlove', 'save', 'unsave'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Xác định hành động thực tế
$isLove = in_array($action, ['love', 'unlove']);
$isSave = in_array($action, ['save', 'unsave']);

if ($isLove) {
    $result = lovePrompt($account_id, $prompt_id, $conn);
} else {
    $result = savePrompt($account_id, $prompt_id, $conn);
}

// Phân tích kết quả từ hàm cũ
if (strpos($result, 'đã tim') !== false || strpos($result, 'đã lưu') !== false) {
    $newStatus = $isLove ? 'loved' : 'saved';
    $countChange = 1;
} else {
    $newStatus = $isLove ? 'unloved' : 'unsaved';
    $countChange = -1;
}

// Lấy lại số liệu mới nhất
$stmt = $conn->prepare("SELECT love_count, save_count FROM prompt WHERE prompt_id = ?");
$stmt->bind_param("i", $prompt_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'success' => true,
    'action' => $newStatus,
    'love_count' => (int)$data['love_count'],
    'save_count' => (int)$data['save_count'],
    'change' => $countChange
]);
exit;