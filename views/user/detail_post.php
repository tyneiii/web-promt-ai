<?php
session_start();
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../Controller/user/prompt.php';  // Include controller for lovePrompt/savePrompt

if (!isset($_GET['id'])) {
  echo "<p>Bài viết không tồn tại.</p>";
  exit;
}

$id = (int)$_GET['id'];
$id_user = $_SESSION['id_user'] ?? 0;  // Get logged user ID, 0 if guest

// Handle POST actions (like/save)
if ($id_user > 0) {
  if (isset($_POST['loveBtn'])) {
    $mess = lovePrompt($id_user, $id, $conn);
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
    exit;
  } elseif (isset($_POST['saveBtn'])) {
    $mess = savePrompt($id_user, $id, $conn);
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
    exit;
  }
}

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

$is_loved = false;
$is_saved = false;
if ($id_user > 0) {
  // is_loved
  $love_sql = "SELECT love_id FROM love WHERE prompt_id = ? AND account_id = ? AND status = 'OPEN'";
  $love_stmt = $conn->prepare($love_sql);
  $love_stmt->bind_param("ii", $id, $id_user);
  $love_stmt->execute();
  $is_loved = $love_stmt->get_result()->num_rows > 0;

  // is_saved
  $save_sql = "SELECT save_id FROM save WHERE prompt_id = ? AND account_id = ?";
  $save_stmt = $conn->prepare($save_sql);
  $save_stmt->bind_param("ii", $id, $id_user);
  $save_stmt->execute();
  $is_saved = $save_stmt->get_result()->num_rows > 0;
}

// Lấy nội dung chi tiết bài viết
$sql_details = "SELECT pd.content, p.short_description 
                FROM promptdetail pd
                JOIN prompt p ON pd.prompt_id = p.prompt_id
                WHERE pd.prompt_id = ?
                ORDER BY pd.component_order ASC";  // Ordered by component_order
$stmt2 = $conn->prepare($sql_details);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$details_result = $stmt2->get_result();
$details = $details_result->fetch_all(MYSQLI_ASSOC);

// Xây dựng nội dung prompt đầy đủ cho data-prompt
$full_prompt = $prompt['short_description'] ?? '';
foreach ($details as $d) {
  $full_prompt .= "\n" . $d['content'];
}

// Lấy danh sách bình luận
$sql_cmt = "SELECT c.comment_id, c.prompt_id, c.account_id, c.content, c.created_at,
                   a.username, a.avatar
            FROM comment c
            JOIN account a ON c.account_id = a.account_id
            WHERE c.prompt_id = ?
            ORDER BY c.created_at DESC";
$stmt_cmt = $conn->prepare($sql_cmt);
$stmt_cmt->bind_param("i", $id);
$stmt_cmt->execute();
$cmt_result = $stmt_cmt->get_result();
$comments = $cmt_result->fetch_all(MYSQLI_ASSOC);
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

  <h1 class="detail-title"><?= htmlspecialchars($prompt['short_description'] ?? $prompt['title'] ?? 'Untitled') ?></h1>

  <?php if (!empty($prompt['image'])): ?>
    <img class="post-image" src="../../<?= htmlspecialchars($prompt['image']) ?>" alt="Ảnh bài viết" style="max-width: 100%; border-radius: 8px; margin-bottom: 20px;">
  <?php endif; ?>

  <div class="detail-content">
    <?php foreach ($details as $d): ?>
      <p><?= nl2br(htmlspecialchars($d['content'])) ?></p>
    <?php endforeach; ?>
    <?php if (empty($details)): ?>
      <p>Không có chi tiết thêm.</p>
    <?php endif; ?>
  </div>

  <?php if ($id_user > 0): ?>
    <form action="" method="post" style="display: inline;">
    <?php endif; ?>
    <div class="detail-actions">
      <?php if ($id_user > 0): ?>
        <button type="submit" name="loveBtn" class="love" title="Thích bài viết" value="<?= $id ?>">
          <i class="fa-heart <?= $is_loved ? 'fa-solid text-red' : 'fa-regular' ?>"></i> <?= (int)$prompt['love_count'] ?>
        </button>

        <button><i class="fa-regular fa-comment"></i> <?= (int)$prompt['comment_count'] ?></button>
        <button type="submit" name="saveBtn" class="save" title="Lưu bài viết" value="<?= $id ?>">
          <i class="fa-bookmark <?= $is_saved ? 'fa-solid text-blue' : 'fa-regular' ?>"></i> <?= (int)$prompt['save_count'] ?>
        </button>
      <?php else: ?>
        <button class="love disabled" title="Đăng nhập để thích"><i class="fa-regular fa-heart"></i> <?= (int)$prompt['love_count'] ?></button>
        <button class="save disabled" title="Đăng nhập để lưu"><i class="fa-regular fa-bookmark"></i> <?= (int)$prompt['save_count'] ?></button>
      <?php endif; ?>
      <button type="button" class="run-btn" title="Xem kết quả"
        data-prompt="<?= htmlspecialchars($full_prompt, ENT_QUOTES) ?>">
        ⚡ Run Prompt
      </button>

    </div>
    <?php if ($id_user > 0): ?>
    </form>
  <?php endif; ?>

  <!-- PHẦN BÌNH LUẬN -->
  <div class="comment-section">
    <h4>Bình luận</h4>

    <!-- Form thêm bình luận -->
    <div class="comment-form">
      <?php if (isset($_SESSION['id_user'])): ?>
        <form method="post" action="../../Controller/user/process_comment.php">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="prompt_id" value="<?= $id ?>">
          <textarea name="comment_content" rows="3" placeholder="Viết bình luận..." required></textarea>
          <button type="submit">Gửi</button>
        </form>
      <?php else: ?>
        <p>Bạn cần <a href="../login/login.php">đăng nhập</a> để bình luận.</p>
      <?php endif; ?>
    </div>

    <!-- Danh sách bình luận -->
    <div class="comments-list">
      <?php if (empty($comments)): ?>
        <p>Chưa có bình luận nào.</p>
      <?php else: ?>
        <?php foreach ($comments as $c): ?>
          <div class="comment-item">
            <div class="comment-avatar">
              <img src="<?= htmlspecialchars($c['avatar'] ?? '../../public/img/user5.png') ?>"
                alt="<?= htmlspecialchars($c['username']) ?>">
            </div>
            <div class="comment-body">
              <div class="comment-header">
                <strong><?= htmlspecialchars($c['username']) ?></strong>
                <span class="comment-date">
                  <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?>
                </span>
              </div>

              <!-- Nội dung bình luận -->
              <div class="comment-content">
                <?= nl2br(htmlspecialchars($c['content'])) ?>
              </div>

              <!-- Nút sửa / xoá (chỉ hiện cho chủ comment) -->
              <?php if (isset($_SESSION['id_user']) && $_SESSION['id_user'] == $c['account_id']): ?>
                <div class="comment-actions">
                  <!-- Form sửa bình luận -->
                  <details>
                    <summary>Sửa</summary>
                    <form method="post" action="../../Controller/user/process_comment.php" class="edit-comment-form">
                      <input type="hidden" name="action" value="edit">
                      <input type="hidden" name="prompt_id" value="<?= $id ?>">
                      <input type="hidden" name="comment_id" value="<?= (int)$c['comment_id'] ?>">
                      <textarea name="comment_content" rows="3" required><?= htmlspecialchars($c['content']) ?></textarea>
                      <button type="submit">Lưu</button>
                    </form>
                  </details>

                  <!-- Form xoá bình luận -->
                  <form method="post" action="../../Controller/user/process_comment.php" onsubmit="return confirm('Bạn chắc chắn muốn xoá bình luận này?');" style="display:inline-block; margin-left:8px;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="prompt_id" value="<?= $id ?>">
                    <input type="hidden" name="comment_id" value="<?= (int)$c['comment_id'] ?>">
                    <button type="submit" class="btn-delete-comment">Xoá</button>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
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

<style>
  /* ===== COMMENT SECTION ===== */
  .comment-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #333;
    color: #fff;
    font-size: 14px;
  }

  .comment-section h4 {
    margin-bottom: 15px;
    font-size: 18px;
    font-weight: 600;
  }

  /* Form bình luận */
  .comment-form form {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin-bottom: 20px;
  }

  .comment-form textarea {
    flex: 1;
    border-radius: 8px;
    border: 1px solid #444;
    padding: 10px 12px;
    background: #111;
    color: #f1f1f1;
    resize: vertical;
    min-height: 60px;
    font-size: 14px;
  }

  .comment-form textarea::placeholder {
    color: #777;
  }

  .comment-form button {
    padding: 8px 16px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    background: linear-gradient(135deg, #ff6a3d, #ff944d);
    color: #fff;
    white-space: nowrap;
    margin-top: 4px;
  }

  .comment-form button:hover {
    opacity: 0.9;
  }

  /* Danh sách bình luận */
  .comments-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  /* Mỗi comment */
  .comment-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: #111;
    border-radius: 10px;
    border: 1px solid #222;
  }

  .comment-avatar img {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 50%;
  }

  /* Nội dung comment */
  .comment-body {
    flex: 1;
  }

  .comment-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
  }

  .comment-header strong {
    font-size: 14px;
  }

  .comment-date {
    font-size: 12px;
    color: #888;
  }

  .comment-content {
    margin-bottom: 6px;
    white-space: pre-wrap;
    line-height: 1.4;
  }

  /* Hành động Sửa / Xoá */
  .comment-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
  }

  /* Nút "Sửa" dùng <details> */
  .comment-actions details {
    display: inline-block;
  }

  .comment-actions summary {
    list-style: none;
    cursor: pointer;
    color: #ff8a4a;
  }

  .comment-actions summary::-webkit-details-marker {
    display: none;
  }

  .comment-actions summary::before {
    content: "";
  }

  /* Form sửa bên trong details */
  .edit-comment-form {
    margin-top: 6px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .edit-comment-form textarea {
    width: 100%;
    min-height: 60px;
    border-radius: 6px;
    border: 1px solid #444;
    background: #0c0c0c;
    color: #f1f1f1;
    padding: 6px 8px;
    font-size: 13px;
  }

  .edit-comment-form button {
    align-self: flex-end;
    padding: 5px 12px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-size: 12px;
    background: #3b82f6;
    color: #fff;
  }

  /* Nút xoá */
  .btn-delete-comment {
    background: transparent;
    border: none;
    color: #f97373;
    cursor: pointer;
    font-size: 12px;
    padding: 0;
  }

  .btn-delete-comment:hover {
    text-decoration: underline;
  }

  /* Disabled buttons for guests */
  button.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  /* Icon colors */
  .text-red {
    color: #ff0000;
  }

  .text-blue {
    color: #007bff;
  }
</style>

<?php include_once __DIR__ . '/layout/footer.php'; ?>