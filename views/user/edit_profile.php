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
$sql_user = "SELECT * FROM account WHERE account_id = $acc_id";
$user_result = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($user_result);
if (isset($_GET['check_username'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $acc_id = $_SESSION['account_id'];
    $username = mysqli_real_escape_string($conn, $_GET['check_username']);

    $sql = "SELECT * FROM account 
            WHERE username = '$username'
           AND account_id != $acc_id";
    $res = mysqli_query($conn, $sql);

    echo (mysqli_num_rows($res) > 0) ? "exists" : "ok";
    exit;  // rất quan trọng! kết thúc AJAX, không chạy code phía dưới
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['bio']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $avatarPath = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['avatar']['name']);
        $targetDir = '../../public/img/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $avatarPath = $targetDir . $fileName; // chỉ lưu tên file
        }
        $_SESSION['avatar'] = $targetFile;
    }
    $backgroundPath = $user['bg_avatar'] ?? null;
    if (isset($_FILES['background']) && $_FILES['background']['error'] === 0) {
        $bgName = '../../public/img/'.time() . '_bg_' . basename($_FILES['background']['name']);
        $targetDir = __DIR__ . '/../../public/img/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $bgFile = $targetDir . $bgName;
        if (move_uploaded_file($_FILES['background']['tmp_name'], $bgFile)) {
            $backgroundPath = $bgName;
            $_SESSION['background'] = $bgFile;
        }
    }

    $updateSql = "UPDATE account 
        SET username = '$username',
            fullname = '$fullname',
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
                <button class="close-btn" onclick="smartGoBack()">✕</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <label>Ảnh hồ sơ</label>
                    <div class="avatar-section">
                        <div class="avatar-container">
                            <img
                                id="avatarPreview"
                                src="<?= htmlspecialchars($user['avatar']) ?>"
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
                                src="<?= htmlspecialchars($user['bg_avatar']) ?>"
                                alt="Background" class="background-img">

                            <label for="background" class="edit-bg-icon">✎</label>
                            <input type="file" name="background" id="background" accept="image/*">
                        </div>
                    </div>

                    <label for="username">Tên User</label>
                    <input type="text" id="username" name="username"
                        value="<?= htmlspecialchars($user['username'] ?? '') ?>">

                    <p id="usernameError" style="color:red; margin:4px 0;"></p>
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
                    <button type="button" class="cancel" onclick="smartGoBack()">Hủy</button>
                    <button type="submit" class="save" id="saveBtn">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const redirectUrl = "<?php echo htmlspecialchars($redirect_url); ?>";
        function smartGoBack() {
            window.location.href = redirectUrl;
        }
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

        const saveBtn = document.getElementById("saveBtn");

        function setButtonState(enabled) {
            saveBtn.disabled = !enabled; // disabled khi false
            saveBtn.style.opacity = enabled ? 1 : 0.5; // mờ khi disabled
        }

        document.getElementById("username").addEventListener("input", function() {
            const username = this.value.trim();
            const errorBox = document.getElementById("usernameError");

            if (username === "") {
                errorBox.textContent = "";
                setButtonState(true);
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open("GET", "edit_profile.php?check_username=" + encodeURIComponent(username), true);

            xhr.onload = function() {
                if (this.responseText === "exists") {
                    errorBox.textContent = "⚠️ Username đã tồn tại!";
                    errorBox.style.color = "red";
                    setButtonState(false); // mờ + khóa nút
                } else {
                    errorBox.textContent = "✔ Username hợp lệ";
                    errorBox.style.color = "green";
                    setButtonState(true); // bật nút
                }
            };

            xhr.send();
        });
    </script>

</body>

</html>