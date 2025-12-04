<?php
// Controller for create post page
include_once __DIR__ . '/../../config.php';

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: ../login/login.php");
    exit;
}

$acc_id = $_SESSION['account_id'];
$payoutMessage = null;

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $short = $_POST['short_description'] ?? '';
    $contents = $_POST['content'] ?? [];

    $rawTags = $_POST['tags'] ?? '[]';
    $tags = json_decode($rawTags, true);
    
    if (!is_array($tags)) {
        $tags = [];
    }

    // Validate at least 1 tag
    if (empty($tags)) {
        $_SESSION['create_post_error'] = "Vui lòng chọn ít nhất 1 chủ đề cho bài viết.";
        header("Location: create_post.php");
        exit;
    }

    // Validate required fields
    if (empty($title) || empty($short) || empty($contents)) {
        $_SESSION['create_post_error'] = "Vui lòng điền đầy đủ thông tin bài viết.";
        header("Location: create_post.php");
        exit;
    }

    $imageName = "";

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $imageName = "../../public/img/" . time() . "_" . $_FILES['image']['name'];
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imageName)) {
            $_SESSION['create_post_error'] = "Lỗi khi upload ảnh.";
            header("Location: create_post.php");
            exit;
        }
    }

    // Insert prompt
    $stmt = $conn->prepare("
        INSERT INTO prompt (account_id, title, short_description, image, status, love_count, comment_count, save_count, create_at)
        VALUES (?, ?, ?, ?, 'waiting', 0, 0, 0, NOW())
    ");
    
    if (!$stmt) {
        $_SESSION['create_post_error'] = "Lỗi database: " . $conn->error;
        header("Location: create_post.php");
        exit;
    }

    $stmt->bind_param("isss", $acc_id, $title, $short, $imageName);
    
    if (!$stmt->execute()) {
        $_SESSION['create_post_error'] = "Lỗi khi thêm bài viết.";
        header("Location: create_post.php");
        exit;
    }

    $prompt_id = $stmt->insert_id;
    $stmt->close();

    // Insert admin notification
    $notifMsg = "Có bài viết mới chờ duyệt (#$prompt_id)";
    $conn->query("
        INSERT INTO admin_notifications (type, prompt_id, message)
        VALUES ('waiting', $prompt_id, '$notifMsg')
    ");

    // Insert prompt details
    $detailStmt = $conn->prepare("INSERT INTO promptdetail (prompt_id, content) VALUES (?, ?)");
    
    foreach ($contents as $ct) {
        if (!empty($ct)) {
            $detailStmt->bind_param("is", $prompt_id, $ct);
            $detailStmt->execute();
        }
    }
    $detailStmt->close();

    // Insert prompt tags
    if (!empty($tags)) {
        $tagStmt = $conn->prepare("INSERT INTO prompttag (prompt_id, tag_id) VALUES (?, ?)");
        
        foreach ($tags as $tag_id) {
            $tag_id = (int)$tag_id;
            $tagStmt->bind_param("ii", $prompt_id, $tag_id);
            $tagStmt->execute();
        }
        $tagStmt->close();
    }

    // Set success session
    $_SESSION['create_post_success'] = true;
    $_SESSION['success_message'] = "Bài viết của bạn đã được gửi thành công và đang chờ duyệt bởi quản trị viên!";
    header("Location: create_post.php?show_success_modal=1");
    exit;
}

// Load user data
$sql_user = "SELECT * FROM account WHERE account_id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $acc_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Tags list (có thể lấy từ DB sau)
$tags_list = [
    ['tag_id' => 1, 'tag_name' => 'Công việc'],
    ['tag_id' => 2, 'tag_name' => 'Công nghệ'],
    ['tag_id' => 3, 'tag_name' => 'Học tập'],
    ['tag_id' => 4, 'tag_name' => 'Sáng tạo nội dung'],
    ['tag_id' => 5, 'tag_name' => 'Giải trí'],
    ['tag_id' => 6, 'tag_name' => 'Phát triển bản thân'],
    ['tag_id' => 7, 'tag_name' => 'Cuộc sống'],
    ['tag_id' => 8, 'tag_name' => 'Kinh doanh'],
    ['tag_id' => 9, 'tag_name' => 'Công cụ'],
    ['tag_id' => 10, 'tag_name' => 'Khác']
];
