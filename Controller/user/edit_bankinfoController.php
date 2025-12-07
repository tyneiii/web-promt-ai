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
$error = null;
$redirect_url = "profile.php";

// Lấy thông tin Bank Info
$sql = "SELECT * FROM userpayoutinfo WHERE account_id = $acc_id";
$res = mysqli_query($conn, $sql);
$payout = mysqli_fetch_assoc($res);

// Submit Form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name']);
    $bank_account_name = mysqli_real_escape_string($conn, $_POST['bank_account_name']);
    $bank_account_number = mysqli_real_escape_string($conn, $_POST['bank_account_number']);
    $bank_branch = mysqli_real_escape_string($conn, $_POST['bank_branch']);

    if ($payout) {
        // UPDATE
        $sqlQuery = "
            UPDATE userpayoutinfo
            SET bank_name='$bank_name',
                bank_account_name='$bank_account_name',
                bank_account_number='$bank_account_number',
                bank_branch='$bank_branch'
            WHERE account_id=$acc_id
        ";
    } else {
        // INSERT
        $sqlQuery = "
            INSERT INTO userpayoutinfo 
            (account_id, bank_name, bank_account_name, bank_account_number, bank_branch, created_at)
            VALUES ($acc_id, '$bank_name', '$bank_account_name', '$bank_account_number', '$bank_branch', NOW())
        ";
    }

    if (mysqli_query($conn, $sqlQuery)) {
        $_SESSION['success'] = "Cập nhật thông tin ngân hàng thành công!";
        header("Location: profile.php");
        exit;
    } else {
        $error = "Lỗi cập nhật: " . mysqli_error($conn);
    }
}

// Trả dữ liệu về cho View
return [
    "payout" => $payout,
    "redirect_url" => $redirect_url,
    "error" => $error
];
