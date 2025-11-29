<?php
include_once __DIR__ . '/../../config.php'; 

header('Content-Type: application/json');
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}
$chat_id = filter_input(INPUT_GET, 'chat_id', FILTER_VALIDATE_INT);
$last_id = filter_input(INPUT_GET, 'last_id', FILTER_VALIDATE_INT);
$account_id = $_SESSION['id_user'] ?? null;
$new_messages = [];
$sql = "SELECT chat_detail_id, sender_id, message, sent_at 
        FROM chat_detail 
        WHERE chat_id = ? and sender_id != $account_id
          AND chat_detail_id > ? 
        ORDER BY chat_detail_id ASC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $chat_id, $last_id); // "ii" cho 2 integer
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $new_messages[] = $row;
    }
    $stmt->close();
    echo json_encode([
        'success' => true,
        'messages' => $new_messages, 
        'account_id' => $account_id
    ]);

} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error]);
}
?>