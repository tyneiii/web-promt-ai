<?php include_once __DIR__ . '/layout/header.php'; ?>
    <link rel="stylesheet" href="../../public/css/profile.css">
    <div class="profile-container">
        <div class="header" style="background-image: url('../../public/img/bg.jpg');">
            <img src="../../public/img/anh_user_1.jpeg" alt="Avatar" class="avatar">
        </div>
        <div class="profile-info">
                <h2>An Trương</h2>
                <div class="buttons">
                    <button id="follow-btn" class="add-btn">Theo dõi</button>
                    
                    <form action="">
                        <input type="submit" value="Sửa hồ sơ" class="edit-btn"> 
                    </form>
                    <form action="create_post.php">
                        <input type="submit" value="📝 Viết bài" class="add-btn"> 
                    </form>
                </div>
            </div>
        <div class="stats">
                    <span><strong>116</strong> Đã follow</span>
                    <span><strong>8</strong> Follower</span>
                </div>
                <p class="bio">Chào mừng bạn đến với trang cá nhân của mình! Hãy theo dõi để xem những prompt thú vị nhé! 😊</p>
        <div class="tabs">
            <div class="tab active">🔁 Bài viết</div>
            <div class="tab">❤️ Yêu thích</div>
        </div>


        <!-- Lưới video -->
            <!-- <span class="active">🔁 Bài viết</span>
            <span>❤️ Yêu thích</span>
            <span>🔒 Đã Lưu</span> -->
        </div>

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
