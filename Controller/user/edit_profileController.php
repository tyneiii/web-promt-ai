<?php
include_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['account_id'])) {
    header("Location: ../../login.php");
    exit;
}

$acc_id = $_SESSION['account_id'];

// Lấy thông tin user
$sql_user = "SELECT * FROM account WHERE account_id = $acc_id";
$user_result = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($user_result);

$error = null;
$redirect_url = "profile.php";


// ===============================
//  AJAX CHECK USERNAME
// ===============================
if (isset($_GET['check_username'])) {
    $username = trim($_GET['check_username']);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT account_id FROM account WHERE username = ? AND account_id != ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "si", $username, $acc_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    echo (mysqli_stmt_num_rows($stmt) > 0) ? "exists" : "ok";

    mysqli_stmt_close($stmt);
    exit;
}


// ===============================
//  HANDLE POST UPDATE
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['name']);
    $description = trim($_POST['bio']);
    $username = trim($_POST['username']);

    // =======================
    //  CHECK USERNAME SERVER-SIDE
    // =======================
    $checkStmt = mysqli_prepare(
        $conn,
        "SELECT account_id FROM account WHERE username = ? AND account_id != ? LIMIT 1"
    );
    mysqli_stmt_bind_param($checkStmt, "si", $username, $acc_id);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        $error = "Tên người dùng đã tồn tại. Vui lòng chọn tên khác.";
        mysqli_stmt_close($checkStmt);
    } else {
        mysqli_stmt_close($checkStmt);

        // =======================
        //  Xử lý Avatar
        // =======================
        $avatarPath = $user['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            $fileName = time() . '_' . basename($_FILES['avatar']['name']);
            $uploadDir = __DIR__ . '/../../public/img/';
            $savePath = '../../public/img/' . $fileName;

            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $fileName)) {
                $avatarPath = $savePath;
            }
            $_SESSION['avatar'] = $avatarPath;
        }

        // =======================
        //  Xử lý Background
        // =======================
        $bgPath = $user['bg_avatar'];
        if (!empty($_FILES['background']['name'])) {
            $fileNameBg = time() . '_bg_' . basename($_FILES['background']['name']);
            $uploadDir = __DIR__ . '/../../public/img/';
            $savePathBg = '../../public/img/' . $fileNameBg;

            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            if (move_uploaded_file($_FILES['background']['tmp_name'], $uploadDir . $fileNameBg)) {
                $bgPath = $savePathBg;
            }
            $_SESSION['background'] = $bgPath;
        }

        // =======================
        //  UPDATE DB (Prepared)
        // =======================
        $updateStmt = mysqli_prepare(
            $conn,
            "UPDATE account
             SET username = ?, fullname = ?, description = ?, avatar = ?, bg_avatar = ?
             WHERE account_id = ?"
        );

        if ($updateStmt) {
            mysqli_stmt_bind_param(
                $updateStmt,
                "sssssi",
                $username,
                $fullname,
                $description,
                $avatarPath,
                $bgPath,
                $acc_id
            );

            if (mysqli_stmt_execute($updateStmt)) {
                $_SESSION['success'] = "Cập nhật hồ sơ thành công!";
                mysqli_stmt_close($updateStmt);
                header("Location: profile.php");
                exit;
            } else {
                $error = "Lỗi cập nhật: " . mysqli_error($conn);
            }
        } else {
            $error = "Không thể chuẩn bị câu lệnh UPDATE.";
        }
    }
}

return [
    "user" => $user,
    "avatar" => $user["avatar"],
    "background" => $user["bg_avatar"],
    "redirect_url" => $redirect_url,
    "error" => $error,
];
