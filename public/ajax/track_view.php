<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../config.php'; 

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$prompt_id = (int)($data['prompt_id'] ?? 0);
$account_id = (int)($_SESSION['account_id'] ?? $data['account_id'] ?? 0); 

if (!$account_id || $prompt_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data or user not logged in']);
    exit;
}

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$check_sql = "SELECT account_id FROM seen_prompt WHERE account_id = ? AND prompt_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, 'ii', $account_id, $prompt_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'message' => 'Already viewed']);
    exit;
}
mysqli_stmt_close($stmt);

$insert_sql = "INSERT INTO seen_prompt (account_id, prompt_id) VALUES (?, ?)";
$stmt = mysqli_prepare($conn, $insert_sql);
mysqli_stmt_bind_param($stmt, 'ii', $account_id, $prompt_id);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'message' => 'View recorded successfully']);
} else {
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'Failed to record view: ' . mysqli_error($conn)]);
}
?>