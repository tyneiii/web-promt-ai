<?php
header('Content-Type: application/json');
include_once __DIR__ . "/../../config.php";
include_once __DIR__ . "/../../controller/user/prompt.php";
function sendResponse($success, $message)
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Yêu cầu không hợp lệ.');
}
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);
$action = $data['action'] ?? null;
$promptId = $data['prompt_id'] ?? null;
$comment = $data['comment'] ?? null;
if (empty($action) || empty($promptId)) {
    sendResponse(false, 'Thiếu thông tin hành động hoặc ID Prompt.');
}
$result = updateStatus($conn, $promptId, $action, $comment);
sendResponse($result['success'], $result['message']);
