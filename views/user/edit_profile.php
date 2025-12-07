<?php
$data = include_once __DIR__ . '/../../controller/user/edit_profileController.php';

$user = $data["user"];
$redirect_url = $data["redirect_url"];
$error = $data["error"];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa hồ sơ</title>
    <link rel="icon" href="../../public/img/T1.png" type="image/png" sizes="180x180">
    <link rel="stylesheet" href="../../public/css/user/edit_profile.css">
</head>

<body>

    <div class="overlay" id="overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>Sửa hồ sơ</h2>
                <button class="close-btn" onclick="confirmCancel()">✕</button>
            </div>

            <?php if ($error): ?>
                <p style="color:red; text-align:center;"><?= $error ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">

                    <label>Ảnh hồ sơ</label>
                    <div class="avatar-section">
                        <div class="avatar-container">
                            <img id="avatarPreview" src="<?= htmlspecialchars($user['avatar']) ?>" class="avatar">
                            <label for="avatar" class="edit-icon">✎</label>
                            <input type="file" name="avatar" id="avatar" accept="image/*">
                        </div>
                    </div>

                    <label>Ảnh nền</label>
                    <div class="background-section">
                        <div class="background-container">
                            <img id="backgroundPreview" src="<?= htmlspecialchars($user['bg_avatar']) ?>" class="background-img">
                            <label for="background" class="edit-bg-icon">✎</label>
                            <input type="file" name="background" id="background" accept="image/*">
                        </div>
                    </div>

                    <label for="username">Tên User</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                    <p id="usernameError"></p>

                    <label for="name">Tên</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['fullname']) ?>">

                    <label for="bio">Tiểu sử</label>
                    <textarea id="bio" name="bio" maxlength="80"><?= htmlspecialchars($user['description']) ?></textarea>
                    <small><?= strlen($user['description']) ?>/80</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="cancel" onclick="confirmCancel()">Hủy</button>
                    <button type="submit" class="save" id="saveBtn">Lưu</button>
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

    document.getElementById('avatar').addEventListener('change', e => {
        document.getElementById('avatarPreview').src = URL.createObjectURL(e.target.files[0]);
    });

    document.getElementById('background').addEventListener('change', e => {
        document.getElementById('backgroundPreview').src = URL.createObjectURL(e.target.files[0]);
    });

    // ================================
    // AJAX CHECK USERNAME
    // ================================
    document.getElementById("username").addEventListener("input", function () {

        const username = this.value.trim();
        const errorBox = document.getElementById("usernameError");
        const saveBtn = document.getElementById("saveBtn");

        function setButtonState(enabled) {
            saveBtn.disabled = !enabled;
            saveBtn.style.opacity = enabled ? 1 : 0.5;
        }

        if (username.length === 0) {
            errorBox.textContent = "Username không được để rỗng!";
            errorBox.style.color = "red";
            setButtonState(false);
            return;
        }


        const xhr = new XMLHttpRequest();

        xhr.open(
            "GET",
            "../../controller/user/edit_profileController.php?check_username=" + encodeURIComponent(username),
            true
        );

        xhr.onload = function () {

            const resp = this.responseText.trim();

            if (resp === "exists") {
                errorBox.textContent = "Username đã tồn tại!";
                errorBox.style.color = "red";
                setButtonState(false);

            } else if (resp === "ok") {
                errorBox.textContent = "Username hợp lệ";
                errorBox.style.color = "green";
                setButtonState(true);

            } else {
                // <-- xử lý lỗi server
                errorBox.textContent = "Lỗi kiểm tra username!";
                errorBox.style.color = "yellow";
                setButtonState(false);
            }
        };

        xhr.onerror = function () {
            errorBox.textContent = "⚠️ Không thể kết nối tới server!";
            errorBox.style.color = "red";
            setButtonState(false);
        };

        xhr.send();
    });
</script>


</body>

</html>