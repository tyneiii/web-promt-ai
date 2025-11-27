<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../config.php';  

$id_user = $_SESSION['id_user'] ?? '';
$name_user = $_SESSION['name_user'] ?? '';

$search = $_GET['search'] ?? '';

// Include prompt.php chỉ khi cần cho handle form (tránh load không cần)
include_once __DIR__ . '/../../Controller/user/prompt.php';

// Handle form submits NGAY ĐẦU (trước include header, tránh output)
if (isset($_POST['loveBtn']) && $id_user) {
    $id_prompt = (int)$_POST['loveBtn'];
    $mess = lovePrompt($id_user, $id_prompt, $conn);
    header("Location: " . $_SERVER['PHP_SELF'] . "?search=" . urlencode($search));
    exit;
} elseif (isset($_POST['cmtBtn']) && $id_user) {
    $id_prompt = (int)$_POST['cmtBtn'];
    $redirect = "detail_post.php?id=" . $id_prompt;
    if (!empty($search)) {
        $redirect .= "&search=" . urlencode($search);
    }
    header("Location: $redirect");
    exit;
} elseif (isset($_POST['saveBtn']) && $id_user) {
    $id_prompt = (int)$_POST['saveBtn'];
    $mess = savePrompt($id_user, $id_prompt, $conn);
    header("Location: " . $_SERVER['PHP_SELF'] . "?search=" . urlencode($search));
    exit;
}

// Bây giờ mới include header (sau handle, không redirect nữa)
include_once __DIR__ . '/layout/header.php';
?>

<link rel="stylesheet" href="../../public/css/run_prompt.css">

<?php
// Guest mode: Optional message (display in main-content if needed)
// $guest_message = !$id_user ? '<p class="guest-notice">Đăng nhập để like, comment và save prompt!</p>' : '';

$tag = isset($_GET['tag']) ? (int)$_GET['tag'] : 0;
$prompts = getPrompts($id_user, $search, $tag, $conn);
// Lấy top 5 prompt hot dựa trên lượt like
$hot_prompts = getHotPrompts($conn, 5);
unset($_POST);
?>

<div class="left-sidebar">
    <!-- <a href="profile.php?id=<?= $id_user ?>&tab=favorites" title="Danh sách yêu thích">
        <i class="fa-regular fa-heart"></i>
    </a> -->
    <?php if (isset($_SESSION['id_user'])): ?>
        <a href="profile.php?id=<?= $id_user ?>&tab=favorites" title="Danh sách yêu thích">
            <i class="fa-regular fa-heart"></i>
        </a>
    <?php else: ?>
        <a href="../login/login.php?require_login=favorites" title="Đăng nhập để xem yêu thích">
            <i class="fa-regular fa-heart"></i>
        </a>
    <?php endif; ?>

    <?php if (isset($_SESSION['id_user'])): ?>
        <a href="create_post.php" class="sidebar-btn" title="Tạo bài viết mới">
            <i class="fa-solid fa-plus"></i>
        </a>
    <?php else: ?>
        <a href="../login/login.php" class="sidebar-btn" title="Đăng nhập để tạo bài viết">
            <i class="fa-solid fa-plus"></i>
        </a>
    <?php endif; ?>
    <a href="my_comments.php" title="Danh sách bình luận của bạn" class="sidebar-btn">
        <i class="fa-regular fa-comment"></i>
    </a>
</div>

<div class="right-sidebar">
    <div class="border-top"></div>
    <div class="border-bottom"></div>
    <h3>Bảng tin hot 🔥</h3>
    <?php if (empty($hot_prompts)): ?>
        <div class="item">Chưa có bài viết hot nào.</div>
    <?php else: ?>
        <?php foreach ($hot_prompts as $hot): ?>
            <a href="detail_post.php?id=<?= $hot['prompt_id'] ?>" class="item-link">
                <div class="item"><?= htmlspecialchars($hot['description']) ?></div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="main-content">
    <?php if (empty($prompts)): ?>
        <p>Không có prompt nào phù hợp. Thử tìm kiếm khác!</p>
    <?php else: ?>
        <?php foreach ($prompts as $prompt): ?>
            <form action="" method="post">
                <div class="card" data-id="<?= $prompt['prompt_id'] ?>">
                    <div class="card-header">
                        <div class="user-info">
                             <img src="../../public/img/<?= htmlspecialchars($prompt['avatar'] ?? 'default-avatar.png') ?>" 
                                alt="<?= htmlspecialchars($prompt['username']) ?>"
                                style="width:35px; height:35px; border-radius:50%;">
                            <strong><?= htmlspecialchars($prompt['username']) ?></strong>
                        </div>
                        <button class="report-btn" type="button">
                            <i class="fa-solid fa-flag"></i> Báo cáo
                        </button>
                    </div>
                    <h4><?= htmlspecialchars($prompt['description']) ?></h4>
                    <?php if (!empty($prompt['tags'])): ?>
                        <div class="home-tags">
                            <?php foreach ($prompt['tags'] as $tag): ?>
                                <a class="tag-item" href="home.php?tag=<?= $tag['id'] ?>">
                                    #<?= htmlspecialchars($tag['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>


                    <p>
                        <?php
                        if (is_array($prompt['details']) && !empty($prompt['details'])) {
                            echo implode('<br><br>', array_map('htmlspecialchars', $prompt['details']));
                        }
                        ?>
                    </p>
                    <div class="card-buttons">
                        <button type="submit" name="loveBtn" id="loveBtn" title="Thích bài viết" value="<?= $prompt['prompt_id'] ?>">
                            <i class="fa-heart <?= $prompt['is_loved'] ? 'fa-solid text-red' : 'fa-regular' ?>"></i> <?= $prompt['love_count'] ?>
                        </button>
                        <button type="submit" name="cmtBtn" title="Bình luận bài viết" value="<?= $prompt['prompt_id'] ?>">
                            <i class="fa-regular fa-comment"></i> <?= $prompt['comment_count'] ?>
                        </button>
                        <button type="submit" name="saveBtn" title="Lưu bài viết" id="saveBtn" value="<?= $prompt['prompt_id'] ?>">
                            <i class="fa-regular fa-bookmark"></i> <?= $prompt['save_count'] ?>
                        </button>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
    <div id="report-modal" class="report-modal" style="display:none;">
        <div class="report-box">
            <h3>Chọn lý do báo cáo</h3>

            <select id="report-reason">
                <option value="Nội dung không phù hợp">Nội dung không phù hợp</option>
                <option value="Spam / Quảng cáo sai">Spam / Quảng cáo sai</option>
                <option value="Thông tin sai lệch">Thông tin sai lệch</option>
                <option value="Hình ảnh nhạy cảm">Hình ảnh nhạy cảm</option>
                <option value="Khác">Khác</option>
            </select>

            <textarea id="report-custom" placeholder="Nếu chọn 'Khác', hãy nhập lý do..." style="display:none; margin-top:10px;"></textarea>

            <div class="report-actions">
                <button id="cancelReport">Hủy</button>
                <button id="submitReport">Gửi báo cáo</button>
            </div>
        </div>
    </div>

</div>

<script>

let currentPromptId = 0;

/* CLICK CARD → MỞ CHI TIẾT */
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.closest('button') || e.target.closest('.run-btn')) return;
        const id = this.getAttribute('data-id');
        window.location.href = `detail_post.php?id=${id}`;
    });
});


/* MỞ POPUP BÁO CÁO */
document.querySelectorAll(".report-btn").forEach(btn => {
    btn.addEventListener("click", function (e) {
        e.stopPropagation();

        const card = this.closest(".card");
        currentPromptId = card.getAttribute("data-id");

        document.getElementById("report-modal").style.display = "flex";
    });
});


/* SHOW/HIDE COMMENT WHEN SELECT "Khác" */
document.getElementById("report-reason").addEventListener("change", function () {
    document.getElementById("report-custom").style.display =
        (this.value === "Khác") ? "block" : "none";
});


/* HỦY POPUP */
document.getElementById("cancelReport").onclick = () => {
    document.getElementById("report-modal").style.display = "none";
};


/* GỬI BÁO CÁO */
document.getElementById("submitReport").onclick = () => {
    let reason = document.getElementById("report-reason").value;

    if (reason === "Khác") {
        let custom = document.getElementById("report-custom").value.trim();
        if (!custom) {
            alert("Vui lòng nhập lý do báo cáo!");
            return;
        }
        reason = custom;
    }

    fetch("report.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + currentPromptId + "&reason=" + encodeURIComponent(reason)
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        document.getElementById("report-modal").style.display = "none";
    })
    .catch(err => {
        console.error(err);
        alert("Lỗi khi báo cáo!");
    });
};

</script>


<script src="../../public/js/user_comments.js"></script>
<?php include_once __DIR__ . '/layout/footer.php'; ?>