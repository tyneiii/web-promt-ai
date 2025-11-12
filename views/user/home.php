<?php
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../Controller/user/prompt.php'
?>
<link rel="stylesheet" href="../../public/css/run_prompt.css"> 
<?php
$id_user = $_SESSION['id_user'];
$search='';
if(isset($_GET['search'])){
  $search=$_GET['search'];
}
if (isset($_POST['loveBtn'])) {
  $id_prompt=(int)$_POST['loveBtn'];
  $mess = lovePrompt($id_user, $id_prompt, $conn);
}
$prompts = getPrompt($id_user,$search, $conn);

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
      <div class="card">
        <div class="card-header">
          <div class="user-info">
            <img src="../../public/img/avatar.png" alt="<?= $prompt['username'] ?>" style="width:35px; height:35px; border-radius:50%;">
            <strong><?= $prompt['username'] ?></strong>
          </div>
          <button class="report-btn" type="button"><i class="fa-solid fa-flag"></i> Báo cáo</button>
        </div>
        <h4><?= $prompt['description'] ?></h4>
        <p><?= implode('<br><br>', $prompt['details']) ?></p>
        <div class="card-buttons">
          <button type="submit" name="loveBtn" id='loveBtn' title="Tim bài viết" value="<?= $prompt['id'] ?> ">
            <i class="fa-heart <?= $prompt['is_loved'] ? 'fa-solid text-red' : 'fa-regular' ?>"></i>  <?= $prompt['love_count'] ?>
          </button>
          <button type="submit" name="cmtBtn" title="Bình luận bài viết" value="<?= $prompt['id'] ?>">
            <i class="fa-regular fa-comment"></i>  <?= $prompt['comment_count'] ?>
          </button>
          <button type="submit" name="saveBtn" title="Lưu bài viết" id='saveBtn' value="<?= $prompt['id'] ?>">
            <i class="fa-regular fa-bookmark"></i>  <?= $prompt['save_count'] ?>
          </button>
          <!-- SỬA: Xóa onclick, chỉ dùng class + data-prompt cho delegation JS -->
          <button type="button" class="run-btn" title="Xem kết quả" 
            data-prompt="<?= htmlspecialchars($prompt['description'] . "\n" . implode("\n", $prompt['details']), ENT_QUOTES) ?>">
            ⚡ Run Prompt
          </button>
        </div>
      </div>
    </form>
  <?php endforeach; ?>
</div>

<!-- Modal (giữ nguyên) -->
<div id="prompt-modal" style="display: none;">
  <div class="modal-overlay" onclick="closePromptModal(event)"></div>
  <div class="modal-content">
    <h3>Xác nhận chạy prompt</h3>
    <div id="prompt-display">
      <label for="promptInput">Nội dung prompt (có thể chỉnh sửa):</label>
      <textarea id="promptInput" rows="8" cols="50" placeholder="Prompt sẽ hiển thị ở đây..."></textarea>
      <small>Bấm "Chạy ngay" để lấy kết quả.</small>
    </div>
    <div class="modal-actions">
      <button class="cancel" type="button" onclick="closePromptModal()">Hủy</button>
      <button class="confirm" type="button" onclick="confirmRunPrompt()">Chạy ngay</button>
    </div>
  </div>
</div>

<div id="resultBox" style="display: none;"></div>

<script src="/web-promt-ai/public/js/run_api.js"></script>



<?php include_once __DIR__ . '/layout/footer.php'; ?>