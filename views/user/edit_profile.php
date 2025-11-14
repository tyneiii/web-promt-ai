<?php
include_once __DIR__ . '/../../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

$acc_id = $_SESSION['id_user'];

// Lấy thông tin người dùng
$sql_user = "SELECT * FROM account WHERE account_id = $acc_id";
$user_result = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($user_result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['bio']);

    // Giữ ảnh cũ nếu không tải ảnh mới
    $avatarPath = $user['avatar'];

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['avatar']['name']);
        $targetDir = '../../public/img/';

        // Tạo thư mục nếu chưa có
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $avatarPath = $fileName; // chỉ lưu tên file
        }
        $_SESSION['avatar'] = $targetFile ;
    }

    // Cập nhật thông tin vào CSDL
    $updateSql = "UPDATE account 
        SET fullname = '$fullname',
            description = '$description',
            avatar = '$avatarPath'
        WHERE account_id = $acc_id
    ";
    
    if (mysqli_query($conn, $updateSql)) {
        $_SESSION['success'] = "Cập nhật hồ sơ thành công!";
        header("Location: profile.php");
        exit;
    } else {
        $error = "Cập nhật thất bại: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa hồ sơ</title>
    <link rel="stylesheet" href="../../public/css/user/edit_profile.css">
</head>
<body>
<div class="overlay" id="overlay">
    <div class="modal">
        <div class="modal-header">
            <h2>Sửa hồ sơ</h2>
            <button class="close-btn" onclick="confirmCancel()">✕</button>
        </div>

        <?php if (isset($error)): ?>
            <p style="color:red;text-align:center;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <label>Ảnh hồ sơ</label>
                <div class="avatar-section">
                    <div class="avatar-container">
                        
                        <img 
                            id="avatarPreview"
                            src="../../public/img/<?= htmlspecialchars($user['avatar']) ?>" 
                            alt="Avatar" class="avatar">
                        <label for="avatar" class="edit-icon">✎</label>
                        <input type="file" name="avatar" id="avatar" accept="image/*">
                    </div>
                </div>

                <label for="tiktok_id">User ID</label>
                <input type="text" id="tiktok_id" name="tiktok_id" 
                       value="<?= $user['account_id'] ?>" readonly>

                <label for="name">Tên</label>
                <input type="text" id="name" name="name" 
                       value="<?= htmlspecialchars($user['fullname']) ?>">

                <label for="bio">Tiểu sử</label>
                <textarea id="bio" name="bio" maxlength="80" 
                          placeholder="Giới thiệu ngắn..."><?= htmlspecialchars($user['description']) ?></textarea>
                <small style="color:#777;">
                    <?= strlen($user['description'] ?? '') ?>/80
                </small>
            </div>

            <div class="modal-footer">
                <button type="button" class="cancel" onclick="confirmCancel()">Hủy</button>
                <button type="submit" class="save">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ✅ Preview ảnh khi chọn
    document.getElementById('avatar').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const preview = document.getElementById('avatarPreview');
            preview.src = URL.createObjectURL(file);
        }
    });

    // Cập nhật bộ đếm ký tự bio
    const bio = document.getElementById('bio');
    const small = document.querySelector('small');
    bio.addEventListener('input', () => {
        small.textContent = `${bio.value.length}/80`;
    });

    // Xác nhận khi hủy
    function confirmCancel() {
        const confirmExit = confirm("Bạn có chắc muốn hủy chỉnh sửa và quay lại trang hồ sơ?");
        if (confirmExit) {
            window.location.href = "profile.php";
        }
    }
</script>
</body>
</html>
