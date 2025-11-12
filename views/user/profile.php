<?php 
    include_once __DIR__ . '/layout/header.php'; 
    include_once __DIR__ . '/../../config.php';
    
    $acc_id = 5;
    // Lấy thông tin người dùng có account_id = 5
    $sql_user = "SELECT * FROM account WHERE account_id = $acc_id ";
    $user_result = mysqli_query($conn, $sql_user);
    $user = mysqli_fetch_assoc($user_result);

    // Xác định tab hiện tại (mặc định là 'posts')
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'posts';

    // Truy vấn dữ liệu theo tab
    if ($tab === 'favorites') {
        $sql = "SELECT p.* 
            FROM love l 
            JOIN prompt p ON l.prompt_id = p.prompt_id 
            WHERE l.account_id = $acc_id AND l.status = 'OPEN'
            ORDER BY l.love_at DESC ";
    } else {
        $sql = "SELECT * FROM prompt WHERE account_id = $acc_id ORDER BY prompt_id DESC";
    }

    $result = mysqli_query($conn, $sql);
    // // Lấy danh sách bài viết của user
    // $sql_prompt = "SELECT * FROM prompt WHERE account_id = $acc_id ORDER BY prompt_id DESC";
    // $posts = mysqli_query($conn, $sql_prompt);

    // // Lấy danh sách bài viết user đã yêu thích
    // $sql_love = "SELECT p.* FROM love l
    // JOIN prompt p ON l.prompt_id = p.prompt_id
    // WHERE l.account_id = $acc_id AND l.status = 'OPEN'
    // ORDER BY l.love_at DESC";

    // $favorites = mysqli_query($conn, $sql_love);

?>

<link rel="stylesheet" href="../../public/css/user/profile.css">

<button id="back-btn" class="back-btn" onclick="window.history.back()" title="Về trang trước">
  <i class="fa-solid fa-arrow-left"></i>
</button>
<div class="profile-container">
    <div class="header" style="background-image: url('../../public/img/bg.png');">
    <img src="../../public/img/<?= $user['avatar'] ?? 'avatar.png' ?>" class="avatar">
</div>
    <div class="profile-info">
        <h2><?= $user['username'] ?? 'Người dùng'?></h2>
        <div class="buttons">
            <form action="edit_profile.php">
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
    <p class="bio"><?= $user['description'] ?? 'Chưa có tiểu sử.' ?></p>
    <div class="tabs">
        <a href="?tab=posts" class="tab <?= $tab === 'posts' ? 'active' : '' ?>">🔁 Bài viết</a>
        <a href="?tab=favorites" class="tab <?= $tab === 'favorites' ? 'active' : '' ?>">❤️ Yêu thích</a>
    </div>
</div>

<!-- Nội dung -->
<div class="write-container">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="write-item">
                <h2><?= $row['title'] ?></h2>
                <h3><?= $row['short_description'] ?></h3>
                <span><?= $row['love_count'] ?> ❤️ • <?= number_format($row['comment_count']) ?> bình luận</span>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; color:gray;">
            <?= $tab === 'favorites' ? 'Chưa có bài viết yêu thích nào.' : 'Chưa có bài viết nào.' ?>
        </p>
    <?php endif; ?>
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