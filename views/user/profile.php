<?php
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================
        XỬ LÝ FOLLOW/UNFOLLOW AJAX (TRẢ JSON)
    ========================== */
if (isset($_POST['action']) && $_POST['action'] === "follow_toggle") {
    header("Content-Type: application/json");

    $follower = intval($_SESSION['id_user']);
    $following = intval($_POST['following_id']);

    if ($follower == $following) {
        echo json_encode(["status" => "error"]);
        exit;
    }

    // Kiểm tra đã follow
    $sql_check = "SELECT * FROM follow WHERE follower_id = $follower AND following_id = $following";
    $check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM follow WHERE follower_id = $follower AND following_id = $following");
        $action = "unfollow";
    } else {
        mysqli_query($conn, "INSERT INTO follow (follower_id, following_id) VALUES ($follower, $following)");
        $action = "follow";
    }

    // Lấy số follower mới
    $followerCount = mysqli_fetch_row(mysqli_query(
        $conn,
        "SELECT COUNT(*) FROM follow WHERE following_id = $following"
    ))[0];

    // Lấy số đang follow mới
    $followingCount = mysqli_fetch_row(mysqli_query(
        $conn,
        "SELECT COUNT(*) FROM follow WHERE follower_id = $following"
    ))[0];

    echo json_encode([
        "status" => $action,
        "followerCount" => $followerCount,
        "followingCount" => $followingCount
    ]);
    exit;
}



/* ==========================
        LẤY DỮ LIỆU PROFILE
    ========================== */

$acc_id = intval($_SESSION['id_user']);
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : $acc_id;

// Lấy thông tin user
$sql_user = "SELECT * FROM account WHERE account_id = $profile_id";
$user_result = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($user_result);

$avatar = $user['avatar'];
if (!$avatar || strtolower($avatar) === "null" || !file_exists(__DIR__ . "/../../public/img/$avatar")) {
    $avatar = "default_avatar.png";
}

// Kiểm tra đã follow?
$sql_check_follow = "SELECT * FROM follow WHERE follower_id = $acc_id AND following_id = $profile_id";
$is_following = mysqli_num_rows(mysqli_query($conn, $sql_check_follow)) > 0;

// Lấy số follower
$followerCountQuery = $conn->prepare("SELECT COUNT(*) FROM follow WHERE following_id = ?");
$followerCountQuery->bind_param("i", $profile_id);
$followerCountQuery->execute();
$followerCountQuery->bind_result($followerCount);
$followerCountQuery->fetch();
$followerCountQuery->close();

// Lấy số đang follow
$followingCountQuery = $conn->prepare("SELECT COUNT(*) FROM follow WHERE follower_id = ?");
$followingCountQuery->bind_param("i", $profile_id);
$followingCountQuery->execute();
$followingCountQuery->bind_result($followingCount);
$followingCountQuery->fetch();
$followingCountQuery->close();

// Tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'posts';

// Lấy bài viết
if ($tab === 'favorites') {
    $sql = "SELECT p.* 
                FROM love l 
                JOIN prompt p ON l.prompt_id = p.prompt_id 
                WHERE l.account_id = $profile_id AND l.status = 'OPEN'
                ORDER BY l.love_at DESC ";
} else if ($tab === 'posts') {
    $sql = "SELECT * FROM prompt WHERE account_id = $profile_id ORDER BY prompt_id DESC";
} else {
    $sql = "SELECT p.* 
                FROM save s 
                JOIN prompt p ON s.prompt_id = p.prompt_id 
                WHERE s.account_id = $profile_id 
                ORDER BY s.save_id DESC ";
}

$result = mysqli_query($conn, $sql);
?>

<link rel="stylesheet" href="../../public/css/user/profile.css">

<button id="back-btn" class="back-btn" onclick="window.history.back()">
    <i class="fa-solid fa-arrow-left"></i>
</button>

<div class="profile-container">
    <div class="header" style="background-image: url('../../public/img/<?= $user['bg_avatar'] ?? 'bg.png' ?>');">
        <img src="../../public/img/<?= $avatar ?>" class="avatar">
    </div>

    <div class="profile-info">
        <h2><?= $user['username'] ?></h2>
        <h3><?= $user['fullname'] ?></h3>

        <div class="buttons">
            <?php if ($acc_id !== $profile_id): ?>
                <button id="follow-btn"

                    data-following="<?= $is_following ? 1 : 0 ?>">
                    <?= $is_following ? '<i class="fa-solid fa-user-check"></i> Đã follow' : 'Theo dõi' ?>
                </button>

            <?php else: ?>
                <form action="edit_profile.php">
                    <input type="submit" value="Sửa hồ sơ" class="edit-btn">
                </form>
                <form action="create_post.php">
                    <input type="submit" value="📝 Viết bài" class="add-btn">
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="stats">
        <span><strong id="following-count"><?php echo $followingCount; ?></strong> Đang follow</span>
        <span><strong id="follower-count"><?php echo $followerCount; ?></strong> Follower</span>
    </div>

    <p class="bio"><?= $user['description'] ?? 'Chưa có tiểu sử.' ?></p>

    <div class="tabs">
        <a href="?id=<?= $profile_id ?>&tab=posts" class="tab <?= $tab === 'posts' ? 'active' : '' ?>">🔁 Bài viết</a>
        <a href="?id=<?= $profile_id ?>&tab=favorites" class="tab <?= $tab === 'favorites' ? 'active' : '' ?>">❤️ Yêu thích</a>
        <a href="?id=<?= $profile_id ?>&tab=saves" class="tab <?= $tab === 'saves' ? 'active' : '' ?>">🔖 Đã lưu</a>
    </div>
</div>

<div class="write-container">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <a href="detail_post.php?id=<?= $row['prompt_id'] ?>" class="write-item" style="text-decoration:none;">
                <h2><?= $row['title'] ?></h2>
                <h3><?= $row['short_description'] ?></h3>
                <span><?= $row['love_count'] ?> ❤️ • <?= number_format($row['comment_count']) ?> bình luận</span>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; color:gray;">Không có bài viết.</p>
    <?php endif; ?>
</div>

</body>

</html>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const followBtn = document.getElementById("follow-btn");
        if (!followBtn) return;

        // Lấy trạng thái ban đầu
        let isFollowing = followBtn.dataset.following === "1";
        const profileId = <?= $profile_id ?>;

        // Hàm cập nhật nút ngay lập tức
        function updateButton() {
            if (isFollowing == "1") {
                followBtn.innerHTML = '<i class="fa-solid fa-user-check"></i> Đã follow';
                followBtn.classList.add("following");
                followBtn.dataset.following = "1";
            } else {
                followBtn.innerHTML = 'Theo dõi';
                followBtn.classList.remove("following");
                followBtn.dataset.following = "0";
            }
        }

        // Hiển thị đúng trạng thái ban đầu
        updateButton();

        followBtn.addEventListener("click", function(e) {
            e.preventDefault();

            // 🔥 Đổi nút NGAY LẬP TỨC (không chờ server)
            isFollowing = !isFollowing;
            updateButton();

            // Gửi AJAX lên đúng file profile.php
            const formData = new FormData();
            formData.append("action", "follow_toggle");
            formData.append("following_id", profileId);

            fetch("profile.php?id=<?= $profile_id ?>", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(result => {
                    // Nếu server trả về lỗi thì rollback lại
                    if (result !== "follow" && result !== "unfollow") {
                        console.error("Server error:", result);
                        isFollowing = !isFollowing;
                        updateButton();
                    }
                })
                .catch(err => {
                    console.error(err);
                    // rollback nếu fetch lỗi
                    isFollowing = !isFollowing;
                    updateButton();
                });
        });
    });
</script>