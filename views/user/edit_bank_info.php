<?php
$data = include_once __DIR__ . '/../../controller/user/edit_bankinfoController.php';

$payout = $data["payout"];
$redirect_url = $data["redirect_url"];
$error = $data["error"] ?? null;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa thông tin ngân hàng</title>
    <link rel="icon" href="../../public/img/T1.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="../../public/css/user/edit_profile.css">
</head>

<body>

<div class="overlay" id="overlay">
    <div class="modal">
        <div class="modal-header">
            <h2>Điền thông tin tài khoản ngân hàng</h2>
            <button class="close-btn" onclick="confirmCancel()">✕</button>
        </div>

        <?php if ($error): ?>
            <p style="color:red; text-align:center;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
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

            </div>
        </form>
    </div>
</div>

<script>
    function confirmCancel() {
        const confirmExit = confirm("Bạn có chắc muốn hủy chỉnh sửa và quay lại trang hồ sơ?");
        if (confirmExit) {
            window.history.back();
        }
    }
</script>

</body>
</html>
