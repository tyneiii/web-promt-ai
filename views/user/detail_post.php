<?php
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../config.php';

if (!isset($_GET['id'])) {
  echo "<p>Bài viết không tồn tại.</p>";
  exit;
}

$id = (int)$_GET['id'];

// Lấy thông tin bài viết
$sql = "SELECT p.*, a.username, a.avatar 
        FROM prompt p
        JOIN account a ON p.account_id = a.account_id
        WHERE p.prompt_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$prompt = $result->fetch_assoc();

if (!$prompt) {
  echo "<p>Bài viết không tồn tại.</p>";
  exit;
}

// Lấy nội dung chi tiết bài viết
$sql_details = "SELECT content 
                FROM promptdetail 
                WHERE prompt_id = ?";
$stmt2 = $conn->prepare($sql_details);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$details_result = $stmt2->get_result();
$details = $details_result->fetch_all(MYSQLI_ASSOC);

// Xây dựng nội dung prompt đầy đủ cho data-prompt
$full_prompt = $prompt['description'];
foreach ($details as $d) {
    $full_prompt .= "\n" . $d['content'];
}
?>

<link rel="stylesheet" href="../../public/css/detail_post.css">

<div class="detail-container">
  <button class="close-detail" onclick="closeDetailPage()">×</button>
  
  <div class="detail-header">
    <div class="user-info">
      <img src="<?= htmlspecialchars($prompt['avatar'] ?? '../../public/img/user5.png') ?>" 
           alt="<?= htmlspecialchars($prompt['username']) ?>">
      <div>
        <strong><?= htmlspecialchars($prompt['username']) ?></strong>
        <div class="date"><?= date('d/m/Y H:i', strtotime($prompt['create_at'])) ?></div>
      </div>
    </div>
  </div>

  <h1 class="detail-title"><?= htmlspecialchars($prompt['short_description']) ?></h1>

  <?php if ($prompt['image']): ?>
    <img class="post-image" src="../../<?= htmlspecialchars($prompt['image']) ?>" alt="Ảnh bài viết" style="max-width: 100%; border-radius: 8px; margin-bottom: 20px;">
  <?php endif; ?>

  <div class="detail-content">
    <?php foreach ($details as $d): ?>
      <p><?= nl2br(htmlspecialchars($d['content'])) ?></p>
    <?php endforeach; ?>
  </div>

  <div class="detail-actions">
    <button class="love"><i class="fa-regular fa-heart"></i> <?= $prompt['love_count'] ?></button>
    <button><i class="fa-regular fa-comment"></i> <?= $prompt['comment_count'] ?></button>
    <button class="save"><i class="fa-regular fa-bookmark"></i> <?= $prompt['save_count'] ?></button>
    <button type="button" class="run-btn" title="Xem kết quả" 
            data-prompt="<?= htmlspecialchars($full_prompt, ENT_QUOTES) ?>">
      ⚡ Run Prompt
    </button>
  </div>

  <div class="comment-section">
    <h4>Bình luận</h4>
    <!-- Thêm form bình luận nếu cần -->
    <div class="comment-form">
      <textarea placeholder="Viết bình luận..."></textarea>
      <button>Gửi</button>
    </div>
    <!-- Danh sách bình luận -->
    <div class="comments-list">
      <!-- Bình luận sẽ được load bằng JS -->
    </div>
  </div>
</div>

<!-- Modal Xác nhận chạy Prompt -->
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

<script>
function closeDetailPage() {
  window.history.back();
}
</script>

<script src="/web-promt-ai/public/js/run_api.js"></script>
