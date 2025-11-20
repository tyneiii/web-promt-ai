<?php
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../Controller/user/prompt.php';
?>
<link rel="stylesheet" href="../../public/css/run_prompt.css">

<?php
$id_user = '';
$name_user = '';
if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
    $name_user = $_SESSION['name_user'];
}

$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Handle form submits
if (isset($_POST['loveBtn']) && $id_user) {
    $id_prompt = (int)$_POST['loveBtn'];
    $mess = lovePrompt($id_user, $id_prompt, $conn);
    header("Location: " . $_SERVER['PHP_SELF'] . "?search=" . urlencode($search));
    exit;
} elseif (isset($_POST['cmtBtn']) && $id_user) {
    $id_prompt = (int)$_POST['cmtBtn'];

    // Nếu có biến search thì thêm vào URL, không thì bỏ qua
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

// Guest mode: Optional message (display in main-content if needed)
// $guest_message = !$id_user ? '<p class="guest-notice">Đăng nhập để like, comment và save prompt!</p>' : '';

$tag = isset($_GET['tag']) ? (int)$_GET['tag'] : 0;
$prompts = getPrompt($id_user, $search, $tag, $conn);

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
    <div class="item">Prompt tạo ảnh phong cách anime</div>
    <div class="item">Prompt phân tích văn bản bằng GPT</div>
    <div class="item">Prompt viết bài SEO tự động</div>
    <div class="item">Prompt vẽ concept nhân vật fantasy</div>
    <div class="item">Prompt tạo website bằng HTML</div>
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
</div>

<script>
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Không mở khi bấm vào nút trong card
            if (e.target.closest('button') || e.target.closest('.run-btn')) return;
            const id = this.getAttribute('data-id');
            window.location.href = `detail_post.php?id=${id}`;
        });
    });
</script>
<script src="../../public/js/user_comments.js"></script>
<?php include_once __DIR__ . '/layout/footer.php'; ?>