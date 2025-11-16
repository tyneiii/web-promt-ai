<?php
include_once __DIR__ . '/../../config.php';

// đảm bảo session đã được start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

$acc_id = intval($_SESSION['id_user']);

// Lấy thông tin người dùng
$sql_user = "SELECT * FROM account WHERE account_id = $acc_id";
$user_result = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($user_result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['bio']);

    // --- XỬ LÝ AVATAR (giữ nguyên logic cũ, chỉ thêm một vài bảo vệ) ---
    // Giữ ảnh cũ nếu không tải ảnh mới
    $avatarPath = $user['avatar'] ?? null;

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['avatar']['name']);
        // dùng đường dẫn tuyệt đối an toàn hơn
        $targetDir = __DIR__ . '/../../public/img/';

        // Tạo thư mục nếu chưa có
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $avatarPath = $fileName; // lưu vào DB chỉ tên file
            // lưu đường dẫn đầy đủ vào session (giống logic cũ)
            $_SESSION['avatar'] = $targetFile;
        }
    }

    // --- XỬ LÝ BACKGROUND: sửa lại để mirror avatar ---
    // Giữ ảnh nền cũ nếu không tải ảnh mới
    // (Chú ý: trong DB trường update đang là `bg_avatar`, nên lấy trường tương ứng)
    $backgroundPath = $user['bg_avatar'] ?? null;

    if (isset($_FILES['background']) && $_FILES['background']['error'] === 0) {
        $bgName = time() . '_bg_' . basename($_FILES['background']['name']);
        $targetDir = __DIR__ . '/../../public/img/';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $bgFile = $targetDir . $bgName;

        if (move_uploaded_file($_FILES['background']['tmp_name'], $bgFile)) {
            $backgroundPath = $bgName; // lưu DB chỉ tên file
            // lưu đường dẫn đầy đủ vào session (giống avatar)
            $_SESSION['background'] = $bgFile;
        }
    }

    // Cập nhật thông tin vào CSDL
    $updateSql = "UPDATE account 
        SET fullname = '$fullname',
            description = '$description',
            avatar = '$avatarPath',
            bg_avatar = '$backgroundPath'
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
                <p style="color:red;text-align:center;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <label>Ảnh hồ sơ</label>
                    <div class="avatar-section">
                        <div class="avatar-container">
                            <img
                                id="avatarPreview"
                                src="../../public/img/<?= htmlspecialchars($user['avatar'] ?? 'default_avatar.jpg') ?>"
                                alt="Avatar" class="avatar">
                            <label for="avatar" class="edit-icon">✎</label>
                            <input type="file" name="avatar" id="avatar" accept="image/*">
                        </div>
                    </div>

                    <label>Ảnh nền (Background)</label>
                    <div class="background-section">
                        <div class="background-container">
                            <img
                                id="backgroundPreview"
                                src="../../public/img/<?= htmlspecialchars($user['bg_avatar'] ?? 'default_bg.jpg') ?>"
                                alt="Background" class="background-img">

                            <label for="background" class="edit-bg-icon">✎</label>
                            <input type="file" name="background" id="background" accept="image/*">
                        </div>
                    </div>

                    <label for="name">Tên</label>
                    <input type="text" id="name" name="name"
                        value="<?= htmlspecialchars($user['fullname'] ?? '') ?>">

                    <label for="bio">Tiểu sử</label>
                    <textarea id="bio" name="bio" maxlength="80"
                        placeholder="Giới thiệu ngắn..."><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
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
        // ✅ Preview ảnh khi chọn (avatar)
        document.getElementById('avatar').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const preview = document.getElementById('avatarPreview');
                preview.src = URL.createObjectURL(file);
            }
        });
        // ✅ Preview ảnh nền khi chọn (background)
        document.getElementById('background').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const preview = document.getElementById('backgroundPreview');
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
