<?php
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../config.php';


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

/* ==========================
   LẤY THU NHẬP THÁNG HIỆN TẠI
========================== */
$sql_bank = "SELECT * FROM userpayoutinfo WHERE account_id = $acc_id";
$bank_res = mysqli_query($conn, $sql_bank);
$bankInfo = mysqli_fetch_assoc($bank_res);

$currentMonth = date('Y-m');

$payoutQuery = $conn->prepare("
    SELECT money_received 
    FROM user_payout 
    WHERE account_id = ? AND month_year = ?
");
$payoutQuery->bind_param("is", $profile_id, $currentMonth);
$payoutQuery->execute();
$payoutQuery->bind_result($earnedMoney);
$payoutQuery->fetch();
$payoutQuery->close();

if (!$earnedMoney) {
    $earnedMoney = 0;
}
// Tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'posts';

// Lấy bài viết
// if ($tab === 'favorites') {
//     $sql = "SELECT p.* 
//                 FROM love l 
//                 JOIN prompt p ON l.prompt_id = p.prompt_id 
//                 WHERE l.account_id = $profile_id AND l.status = 'OPEN'
//                 ORDER BY l.love_at DESC ";
// } else if ($tab === 'posts') {
//     $sql = "SELECT * FROM prompt WHERE account_id = $profile_id ORDER BY prompt_id DESC";
// } else {
//     $sql = "SELECT p.* 
//                 FROM save s 
//                 JOIN prompt p ON s.prompt_id = p.prompt_id 
//                 WHERE s.account_id = $profile_id 
//                 ORDER BY s.save_id DESC ";
// }
if ($tab === 'favorites') {
    $sql = "SELECT p.*, a.username, a.avatar 
                FROM love l 
                JOIN prompt p ON l.prompt_id = p.prompt_id 
                JOIN account a ON p.account_id = a.account_id
                WHERE l.account_id = $profile_id AND l.status = 'OPEN'
                ORDER BY l.love_at DESC";
} else if ($tab === 'posts') {
    $sql = "SELECT p.*, a.username, a.avatar
                FROM prompt p 
                JOIN account a ON p.account_id = a.account_id
                WHERE p.account_id = $profile_id 
                ORDER BY prompt_id DESC";
} else {
    $sql = "SELECT p.*, a.username, a.avatar 
                FROM save s 
                JOIN prompt p ON s.prompt_id = p.prompt_id 
                JOIN account a ON p.account_id = a.account_id
                WHERE s.account_id = $profile_id 
                ORDER BY s.save_id DESC";
}


$result = mysqli_query($conn, $sql);

?>

<link rel="stylesheet" href="../../public/css/user/profile.css">

<button id="back-btn" class="back-btn" onclick="confirmCancel()">
    <i class="fa-solid fa-arrow-left"></i>
</button>

<div class="profile-container">
    <div class="header" style="background-image: url('../../public/img/<?= $user['bg_avatar'] ?? 'bg.png' ?>');">
        <img src="<?= $avatar ?>" class="avatar">
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
        <span><strong id="following-count"><?php echo $followingCount; ?></strong> Đã follow</span>
        <span><strong id="follower-count"><?php echo $followerCount; ?></strong> Follower</span>
    </div>

    <?php if ($acc_id === $profile_id): ?>
    <div class="stats" style="margin-top: 10px; font-size: 18px;">

        <?php if ($bankInfo): ?>
            <!-- ĐÃ CÓ THÔNG TIN NGÂN HÀNG -->
            <span>💰 <strong><?= number_format($earnedMoney, 2) ?> USD</strong> / tháng này</span>
        <a href="edit_bank_info.php"
               style="padding:6px 12px; background:#007bff; color:white; 
                      border-radius:6px; text-decoration:none; font-size:12px;">
                ✎
            </a>
        <?php else: ?>
            <!-- CHƯA CÓ BANK INFO → ĐIỀN NGAY -->
            <a href="edit_bank_info.php" style="color: blue; text-decoration: underline;">
                🔔 Bạn chưa cập nhật thông tin ngân hàng – Nhấn để điền
            </a>
        <?php endif; ?>

    </div>
<?php endif; ?>

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
            <?php
            $post_avatar = $row['avatar'];
            // $post_avatar = $row['author_avatar'];
            if (empty($post_avatar) || strtolower($post_avatar) === "null") {
                $post_avatar = "default_avatar.png";
            }
            ?>
            <a href="detail_post.php?id=<?= $row['prompt_id'] ?>" class="write-item" style="text-decoration:none;">
                <div class="card-mini-header">
                    <img src="<?= $post_avatar ?>" alt="ava" style="width:35px; height:35px; border-radius:50%;">
                    <strong><?= $row['username'] ?></strong>
                </div>
                <div class="card-divider"></div>
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

        // Lấy giá trị ban đầu từ data-following (0/1)
        let isFollowing = followBtn.dataset.following === "1";
        const profileId = <?= $profile_id ?>;

        function updateButton() {
            if (isFollowing) {
                followBtn.innerHTML = '<i class="fa-solid fa-user-check"></i> Đã follow';
                followBtn.classList.add("following");
            } else {
                followBtn.innerHTML = 'Theo dõi';
                followBtn.classList.remove("following");
            }
        }

        updateButton();

        followBtn.addEventListener("click", function(e) {
            e.preventDefault();

            // 🔥 Đổi UI ngay lập tức
            isFollowing = !isFollowing;
            updateButton();

            const formData = new FormData();
            formData.append("action", "follow_toggle");
            formData.append("following_id", profileId);

            fetch("profile.php?id=<?= $profile_id ?>", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    console.log("Server:", data);

                    if (data.status !== "follow" && data.status !== "unfollow") {
                        // rollback nếu server báo lỗi
                        isFollowing = !isFollowing;
                        updateButton();
                        return;
                    }

                    // Cập nhật số follower / following
                    document.getElementById("follower-count").textContent = data.followerCount;
                    document.getElementById("following-count").textContent = data.followingCount;
                    
                })
                .catch(err => {
                    console.error(err);
                    // rollback nếu fetch lỗi
                    isFollowing = !isFollowing;
                    updateButton();
                });
                setTimeout(() => {
                        document.location.reload();
                    }, 0);
        });
    });
    function confirmCancel() {
            window.location.href = "home.php";
    }
</script>