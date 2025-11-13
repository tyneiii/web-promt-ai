<?php
session_start();
if (!isset($_SESSION['account_id'])) {
  $_SESSION['account_id'] = 2; // Gán tạm để test
}
$account_id = $_SESSION['account_id'];
$search = $_GET['search'] ?? '';

include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../Controller/user/prompt.php';

if (isset($_POST['loveBtn'])) {
  $prompt_id = (int)$_POST['loveBtn'];
  $mess = lovePrompt($account_id, $prompt_id, $conn);
}

$prompts = getPrompt($account_id, $search, $conn);
?>


<?php 
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../Controller/user/prompt.php';
?>

<link rel="stylesheet" href="../../public/css/run_prompt.css"> 

<?php
$id_user = $_SESSION['id_user'];
$search = '';
if (isset($_GET['search'])) {
  $search = $_GET['search'];
}

if (isset($_POST['loveBtn'])) {
  $id_prompt = (int)$_POST['loveBtn'];
  $mess = lovePrompt($id_user, $id_prompt, $conn);
}

$prompts = getPrompt($id_user, $search, $conn);
?>

<div class="left-sidebar">
  <i class="fa-regular fa-heart"></i>
  <a href="create_post.php" class="sidebar-btn" title="Tạo bài viết mới">
    <i class="fa-solid fa-plus"></i>
  </a>
  <i class="fa-regular fa-comment"></i>
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
  <?php foreach ($prompts as $prompt): ?>
    <form action="" method="post">
      <div class="card" data-id="<?= $prompt['prompt_id'] ?>">
        <div class="card-header">
          <div class="user-info">
            <img src="../../public/img/user5.png" 
                 alt="<?= htmlspecialchars($prompt['username']) ?>" 
                 style="width:35px; height:35px; border-radius:50%;">
            <strong><?= htmlspecialchars($prompt['username']) ?></strong>
          </div>
          <button class="report-btn" type="button">
            <i class="fa-solid fa-flag"></i> Báo cáo
          </button>
        </div>

        <h4><?= htmlspecialchars($prompt['description']) ?></h4>
        <p><?= implode('<br><br>', $prompt['details']) ?></p>

        <div class="card-buttons">
          <button type="submit" name="loveBtn" id="loveBtn" title="Tim bài viết" value="<?= $prompt['prompt_id'] ?>">
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

<?php include_once __DIR__ . '/layout/footer.php'; ?>
