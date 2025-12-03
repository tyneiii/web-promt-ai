<?php

include_once __DIR__ . '/../../config.php';

$acc_id = isset($_SESSION['account_id']) ? intval($_SESSION['account_id']) : 0;

// =======================
// 1. XỬ LÝ AJAX FOLLOW/UNFOLLOW
// =======================
if (isset($_POST['action']) && $_POST['action'] === "follow_toggle") {
    header("Content-Type: application/json");

    $follower = intval($_SESSION['account_id']);
    $following = intval($_POST['following_id']);

    if ($follower == $following) {
        echo json_encode(["status" => "error"]);
        exit;
    }

    // Kiểm tra đã follow?
    $sql_check = "SELECT * FROM follow WHERE follower_id = $follower AND following_id = $following";
    $check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM follow WHERE follower_id = $follower AND following_id = $following");
        $action = "unfollow";
    } else {
        mysqli_query($conn, "INSERT INTO follow (follower_id, following_id) VALUES ($follower, $following)");
        $action = "follow";
    }

    // Lấy lại số follower
    $followerCount = mysqli_fetch_row(mysqli_query(
        $conn,
        "SELECT COUNT(*) FROM follow WHERE following_id = $following"
    ))[0];

    // Số đang follow
    $followingCount = mysqli_fetch_row(mysqli_query(
        $conn,
        "SELECT COUNT(*) FROM follow WHERE follower_id = $following"
    ))[0];

    echo json_encode([
        "status" => $action,
        "followerCount" => $followerCount,
        "followingCount" => $followingCount
    ]);
    exit;
}

// =======================
// 2. LẤY DỮ LIỆU CHO TRANG PROFILE
// =======================

$profile_id = isset($_GET['id']) ? intval($_GET['id']) : $acc_id;

// Lấy thông tin người dùng
$sql_user = "SELECT * FROM account WHERE account_id = $profile_id";
$user = mysqli_fetch_assoc(mysqli_query($conn, $sql_user));

$avatar = $user['avatar'];

// Đã follow chưa?
$sql_check_follow = "SELECT * FROM follow WHERE follower_id = $acc_id AND following_id = $profile_id";
$is_following = mysqli_num_rows(mysqli_query($conn, $sql_check_follow)) > 0;

// Follower count
$followerCount = mysqli_fetch_row(
    mysqli_query($conn, "SELECT COUNT(*) FROM follow WHERE following_id = $profile_id")
)[0];

// Following count
$followingCount = mysqli_fetch_row(
    mysqli_query($conn, "SELECT COUNT(*) FROM follow WHERE follower_id = $profile_id")
)[0];

// Thu nhập tháng
$currentMonth = date('Y-m');
$sql_money = "SELECT money_received 
              FROM user_payout 
              WHERE account_id = $profile_id AND month_year = '$currentMonth'";
$earnedMoney = mysqli_fetch_row(mysqli_query($conn, $sql_money))[0] ?? 0;
// Lấy thông tin ngân hàng
$sql_bank = "SELECT * FROM userpayoutinfo WHERE account_id = $profile_id LIMIT 1";
$bankInfo = mysqli_fetch_assoc(mysqli_query($conn, $sql_bank));

// Tab chọn
$tab = $_GET['tab'] ?? '';

// Lấy danh sách bài viết
if ($tab === 'favorites') {
    $sql_posts = "SELECT p.*, a.username, a.avatar 
                  FROM love l 
                  JOIN prompt p ON l.prompt_id = p.prompt_id 
                  JOIN account a ON p.account_id = a.account_id
                  WHERE l.account_id = $profile_id AND l.status = 'OPEN'
                  ORDER BY l.love_at DESC";
} elseif ($tab === 'saves') {
    $sql_posts = "SELECT p.*, a.username, a.avatar 
                  FROM save s 
                  JOIN prompt p ON s.prompt_id = p.prompt_id 
                  JOIN account a ON p.account_id = a.account_id
                  WHERE s.account_id = $profile_id 
                  ORDER BY s.save_id DESC";
} else {
    $sql_posts = "SELECT p.*, a.username, a.avatar
                  FROM prompt p 
                  JOIN account a ON p.account_id = a.account_id
                  WHERE p.account_id = $profile_id 
                  ORDER BY prompt_id DESC";
}

$posts = mysqli_query($conn, $sql_posts);
$result = $posts;
// ========== TRẢ VỀ DATA CHO VIEW ==========
return [
    "acc_id" => $acc_id,
    "profile_id" => $profile_id,
    "user" => $user,
    "avatar" => $avatar,
    "is_following" => $is_following,
    "followerCount" => $followerCount,
    "followingCount" => $followingCount,
    "earnedMoney" => $earnedMoney,
    "tab" => $tab,
    "result" => $result,     // thêm
    "bankInfo" => $bankInfo  
];

?>