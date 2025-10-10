<<<<<<< HEAD:views/layout/user_other_page.php
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ người dùng khác</title>
    <link rel="stylesheet" href="../css/user_other_page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <button class="back-btn" onclick="window.history.back()" title="Trang chủ">
        <i class="fa-solid fa-arrow-left"></i>
    </button>
=======
<?php include_once __DIR__ . '/../layout/header.php'; ?>
    <link rel="stylesheet" href="../../public/css/profile.css">
>>>>>>> 06303720573d11281668a856d2bd54ac98c6f46a:views/user/profile.php
    <div class="profile-container">
        <div class="header" style="background-image: url('../../public/img/bg.jpg');">
            <img src="../../public/img/anh_user_1.jpeg" alt="Avatar" class="avatar">
        </div>
        <div class="profile-info">
                <h2>An Trương</h2>
                <div class="buttons">
<<<<<<< HEAD:views/layout/user_other_page.php
                    <button id="follow-btn" class="add-btn">Theo dõi</button>
                    
=======
                    <form action="">
                        <input type="submit" value="Sửa hồ sơ" class="edit-btn"> 
                    </form>
                    <form action="create_post.php">
                        <input type="submit" value="📝 Viết bài" class="add-btn"> 
                    </form>
>>>>>>> 06303720573d11281668a856d2bd54ac98c6f46a:views/user/profile.php
                </div>
            </div>
        <div class="stats">
                    <span><strong>116</strong> Đã follow</span>
                    <span><strong>8</strong> Follower</span>
                </div>
                <p class="bio">Chào mừng bạn đến với trang cá nhân của mình! Hãy theo dõi để xem những prompt thú vị nhé! 😊</p>
        <div class="tabs">
<<<<<<< HEAD:views/layout/user_other_page.php
            <div class="tab active">🔁 Bài viết</div>
            <div class="tab">❤️ Yêu thích</div>
        </div>


        <!-- Lưới video -->
=======
            <span class="active">🔁 Bài viết</span>
            <span>❤️ Yêu thích</span>
            <span>🔒 Đã Lưu</span>
        </div>

>>>>>>> 06303720573d11281668a856d2bd54ac98c6f46a:views/user/profile.php
        <div class="write-container">
            <div class="write-item">
                <h3>“Giải thích ngắn gọn cho tôi biết API là gì và cho ví dụ thực tế dễ hiểu.”</h3>
                <span>13,5K ❤️ • 810 comments</span>
            </div>
            <div class="write-item">
                <h3>“Viết caption TikTok ngắn, vui nhộn về việc học code khuya nhưng vẫn tỉnh táo, kèm 3 hashtag phù hợp.”</h3>
                <span>3,9K ❤️ • 714 comments</span>
            </div>
            <div class="write-item">
                <h3>“Viết bài blog 300 từ về ‘Cách duy trì động lực học lập trình’, giọng văn tích cực và gần gũi.”</h3>
                <span>9,8K ❤️ • 809 comments</span>
            </div>
            <div class="write-item">
                <h3>“Tạo hình ảnh poster game hành động với nhân vật chính mặc áo giáp tương lai, phông nền là thành phố đổ nát.”</h3>
                <span>20K ❤️ • 809 comments</span>
            </div>
            <div class="write-item">
                <h3>“Giải thích ngắn gọn cho tôi biết API là gì và cho ví dụ thực tế dễ hiểu.”</h3>
                <span>13,5K ❤️ • 810 comments</span>
            </div>
            <div class="write-item">
                <h3>“Viết caption TikTok ngắn, vui nhộn về việc học code khuya nhưng vẫn tỉnh táo, kèm 3 hashtag phù hợp.”</h3>
                <span>3,9K ❤️ • 714 comments</span>
            </div>
            <div class="write-item">
                <h3>“Viết bài blog 300 từ về ‘Cách duy trì động lực học lập trình’, giọng văn tích cực và gần gũi.”</h3>
                <span>9,8K ❤️ • 809 comments</span>
            </div>
            <div class="write-item">
                <h3>“Tạo hình ảnh poster game hành động với nhân vật chính mặc áo giáp tương lai, phông nền là thành phố đổ nát.”</h3>
                <span>20K ❤️ • 809 comments</span>
            </div>
            <div class="write-item">
                <h3>“Giải thích ngắn gọn cho tôi biết API là gì và cho ví dụ thực tế dễ hiểu.”</h3>
                <span>13,5K ❤️ • 810 comments</span>
            </div>
            <div class="write-item">
                <h3>“Viết caption TikTok ngắn, vui nhộn về việc học code khuya nhưng vẫn tỉnh táo, kèm 3 hashtag phù hợp.”</h3>
                <span>3,9K ❤️ • 714 comments</span>
            </div>
            <div class="write-item">
                <h3>“Viết bài blog 300 từ về ‘Cách duy trì động lực học lập trình’, giọng văn tích cực và gần gũi.”</h3>
                <span>9,8K ❤️ • 809 comments</span>
            </div>
            <div class="write-item">
                <h3>“Tạo hình ảnh poster game hành động với nhân vật chính mặc áo giáp tương lai, phông nền là thành phố đổ nát.”</h3>
                <span>20K ❤️ • 809 comments</span>
            </div>
        </div>
    </div>
<<<<<<< HEAD:views/layout/user_other_page.php
</body>
</html>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const followBtn = document.getElementById("follow-btn");
    let isFollowing = false;

    followBtn.addEventListener("click", function() {
        isFollowing = !isFollowing;

        if (isFollowing) {
            followBtn.innerHTML = '<i class="fa-solid fa-user-check"></i> Đã follow';
            followBtn.classList.add("followed");
        } else {
            followBtn.innerHTML = 'Theo dõi';
            followBtn.classList.remove("followed");
        }
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll(".tabs .tab");

    tabs.forEach(tab => {
        tab.addEventListener("click", function() {
            // Xóa class active của tất cả tab
            tabs.forEach(t => t.classList.remove("active"));
            // Thêm active cho tab đang bấm
            this.classList.add("active");
        });
    });
});
</script>
=======
>>>>>>> 06303720573d11281668a856d2bd54ac98c6f46a:views/user/profile.php
