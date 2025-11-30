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

/* ======================
   Lấy thông tin Bank Info
========================= */
$sql = "SELECT * FROM userpayoutinfo WHERE account_id = $acc_id";
$res = mysqli_query($conn, $sql);
$payout = mysqli_fetch_assoc($res);

/* ======================
   Khi Submit Form
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name']);
    $bank_account_name = mysqli_real_escape_string($conn, $_POST['bank_account_name']);
    $bank_account_number = mysqli_real_escape_string($conn, $_POST['bank_account_number']);
    $bank_branch = mysqli_real_escape_string($conn, $_POST['bank_branch']);

    if ($payout) {
        // UPDATE
        $sqlUpdate = "
            UPDATE userpayoutinfo
            SET bank_name='$bank_name',
                bank_account_name='$bank_account_name',
                bank_account_number='$bank_account_number',
                bank_branch='$bank_branch'
            WHERE account_id=$acc_id
        ";
    } else {
        // INSERT
        $sqlUpdate = "
            INSERT INTO userpayoutinfo 
            (account_id, bank_name, bank_account_name, bank_account_number, bank_branch, created_at)
            VALUES ($acc_id, '$bank_name', '$bank_account_name', '$bank_account_number', '$bank_branch', NOW())
        ";
    }

    if (mysqli_query($conn, $sqlUpdate)) {
        $_SESSION['success'] = "Cập nhật thông tin ngân hàng thành công!";
        header("Location: profile.php");
        exit;
    } else {
        $error = "Lỗi cập nhật: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>s
    <meta charset="UTF-8">
    <title>Chỉnh sửa thông tin ngân hàng</title>
    <link rel="stylesheet" href="../../public/css/user/edit_profile.css">
</head>

<body>
    <div class="overlay" id="overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>Điền thông tin tài khoản ngân hàng</h2>
                <button class="close-btn" onclick="confirmCancel()">✕</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <label>Tên ngân hàng</label>
        <input type="text" name="bank_name" 
            value="<?= htmlspecialchars($payout['bank_name'] ?? '') ?>" required>

        <label>Tên chủ tài khoản</label>
        <input type="text" name="bank_account_name"
            value="<?= htmlspecialchars($payout['bank_account_name'] ?? '') ?>" required>

        <label>Số tài khoản</label>
        <input type="text" name="bank_account_number"
            value="<?= htmlspecialchars($payout['bank_account_number'] ?? '') ?>" required>

        <label>Chi nhánh</label>
        <input type="text" name="bank_branch"
            value="<?= htmlspecialchars($payout['bank_branch'] ?? '') ?>">

                <div class="modal-footer">
                    <button type="button" class="cancel" onclick="confirmCancel()">Hủy</button>
                    <button type="submit" class="save" id="saveBtn">Lưu</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
<script>
    // Xác nhận khi hủy
    function confirmCancel() {
        const confirmExit = confirm("Bạn có chắc muốn hủy chỉnh sửa và quay lại trang hồ sơ?");
        if (confirmExit) {
            window.history.back();
        }
    };
</script>