<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa hồ sơ</title>
    <link rel="stylesheet" href="../css/edit_profile.css">
</head>

<body>
    <!-- Overlay -->
    <div class="overlay" id="overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>Sửa hồ sơ</h2>
                <!-- Nút X -->
                <button class="close-btn" onclick="confirmCancel()">✕</button>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <label>Ảnh hồ sơ</label>
                    <div class="avatar-section">
                        
                        <div class="avatar-container">
                            <img id="avatarPreview" src="../img/anh_user_1.jpeg" alt="Avatar">
                            <label for="avatar" class="edit-icon">
                                ✎
                            </label>
                            <input type="file" name="avatar" id="avatar" accept="image/*">
                        </div>
                    </div>





                    <label for="tiktok_id">User ID</label>
                    <input type="text" id="tiktok_id" name="tiktok_id" value="antruong_2709" required>

                    <label for="name">Tên</label>
                    <input type="text" id="name" name="name" value="An Trương">

                    <label for="bio">Tiểu sử</label>
                    <textarea id="bio" name="bio" maxlength="80" placeholder="Giới thiệu ngắn..."></textarea>
                    <small style="color:#777;">0/80</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="cancel" onclick="confirmCancel()">Hủy</button>
                    <button type="submit" class="save">Lưu</button>
                </div>
            </form>
        </div>
    </div>


</body>

</html>

<script>
    // Khi bấm X hoặc Hủy
    function confirmCancel() {
        const confirmExit = confirm("Bạn có chắc muốn hủy chỉnh sửa và quay lại trang hồ sơ?");
        if (confirmExit) {
            // Quay lại trang hồ sơ
            window.location.href = "user_main_page.php";
        }
    }

    // Cập nhật bộ đếm ký tự trong ô tiểu sử
    const bio = document.getElementById('bio');
    const small = document.querySelector('small');
    bio.addEventListener('input', () => {
        small.textContent = `${bio.value.length}/80`;
    });
</script>
<script>
    document.getElementById('avatar').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const preview = document.getElementById('avatarPreview');
            preview.src = URL.createObjectURL(file);
        }
    });
</script>