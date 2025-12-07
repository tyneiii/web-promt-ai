<?php
session_start();
include_once __DIR__ . '/../../config.php';

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../user/home.php");
    exit;
}

// Lấy action
$action    = isset($_POST['action']) ? $_POST['action'] : '';
$prompt_id = isset($_POST['prompt_id']) ? (int)$_POST['prompt_id'] : 0;

if ($prompt_id <= 0 || $action === '') {
    echo "<script>alert('Yêu cầu không hợp lệ'); window.history.back();</script>";
    exit;
}

// Kiểm tra đăng nhập: DÙNG account_id 
if (!isset($_SESSION['account_id'])) {
    echo "<script>alert('Bạn cần đăng nhập để thực hiện thao tác này'); window.location.href='../login/login.php';</script>";
    exit;
}

$account_id = (int)$_SESSION['account_id'];

switch ($action) {
    // THÊM BÌNH LUẬN
    case 'add':
        if (!isset($_POST['comment_content'])) {
            echo "<script>alert('Dữ liệu không hợp lệ'); window.history.back();</script>";
            exit;
        }

        $content = trim($_POST['comment_content']);
        if ($content === '') {
            echo "<script>alert('Nội dung bình luận không được để trống'); window.history.back();</script>";
            exit;
        }

        $sql_insert = "INSERT INTO comment (prompt_id, account_id, content, created_at)
                       VALUES (?, ?, ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iis", $prompt_id, $account_id, $content);
        $stmt_insert->execute();

        // Tăng comment_count
        $sql_update_count = "UPDATE prompt SET comment_count = comment_count + 1 WHERE prompt_id = ?";
        $stmt_update = $conn->prepare($sql_update_count);
        $stmt_update->bind_param("i", $prompt_id);
        $stmt_update->execute();

        break;

    case 'delete':
    header("Content-Type: application/json");

    if (!isset($_POST['comment_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu comment_id'
        ]);
        exit;
    }

    $comment_id = (int)$_POST['comment_id'];
    if ($comment_id <= 0 || $prompt_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ'
        ]);
        exit;
    }

    // Kiểm tra quyền xoá
    $sql_check = "SELECT account_id FROM comment WHERE comment_id = ? AND prompt_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $comment_id, $prompt_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();

    if (!$row_check || (int)$row_check['account_id'] !== $account_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền xoá bình luận này'
        ]);
        exit;
    }

    // Xoá bình luận
    $sql_delete = "DELETE FROM comment WHERE comment_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $comment_id);
    $stmt_delete->execute();

    // Giảm comment_count (tránh âm)
    $sql_update_count = "UPDATE prompt 
                         SET comment_count = CASE 
                             WHEN comment_count > 0 THEN comment_count - 1 
                             ELSE 0 
                         END
                         WHERE prompt_id = ?";
    $stmt_update = $conn->prepare($sql_update_count);
    $stmt_update->bind_param("i", $prompt_id);
    $stmt_update->execute();

    echo json_encode([
        'success' => true
    ]);
    exit;


        break;

    default:
        echo "<script>alert('Hành động không hợp lệ'); window.history.back();</script>";
        exit;
}

// Sau khi xử lý xong, quay lại trang chi tiết
header("Location: ../../views/user/detail_post.php?id=" . $prompt_id);
exit;
?>