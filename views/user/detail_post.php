<?php

session_start();
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../Controller/user/prompt.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$account_id = $_SESSION['account_id'] ?? 0;
$redirect_url = $_SERVER['REQUEST_URI'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($account_id <= 0) {
    $_SESSION['redirect_after_login'] = $redirect_url;
    header("Location: ../../views/login/login.php");
    exit;
  }

  if (isset($_POST['loveBtn'])) {
    lovePrompt($account_id, $id, $conn);
    header("Location: " . $redirect_url);
    exit;
  }

  if (isset($_POST['saveBtn'])) {
    savePrompt($account_id, $id, $conn);
    header("Location: " . $redirect_url);
    exit;
  }

  if (isset($_POST['out-btn'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: home.php");
    exit;
  }
}

if ($id <= 0) {
  echo "<h3 style='text-align:center;padding:50px;color:#ff4d4d;'>Bài viết không tồn tại!</h3>";
  exit;
}

include_once __DIR__ . '/layout/header.php';
?>

<link rel="stylesheet" href="../../public/css/detail_post.css">
<link rel="stylesheet" href="../../public/css/comment.css">

<?php
// Lấy dữ liệu bài viết
$sql = "SELECT p.*, a.username, a.avatar FROM prompt p JOIN account a ON p.account_id = a.account_id WHERE p.prompt_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$prompt = $stmt->get_result()->fetch_assoc();

if (!$prompt) {
  echo "<h3 style='text-align:center;padding:50px;color:#ff4d4d;'>Bài viết không tồn tại!</h3>";
  exit;
}

// Kiểm tra đã love/save chưa
$is_loved = $is_saved = false;
if ($account_id > 0) {
  $is_loved = $conn->query("SELECT 1 FROM love WHERE prompt_id = $id AND account_id = $account_id AND status = 'OPEN'")->num_rows > 0;
  $is_saved = $conn->query("SELECT 1 FROM save WHERE prompt_id = $id AND account_id = $account_id")->num_rows > 0;
}

// Chi tiết + tag + bình luận + full_prompt
$sql_details = "SELECT content FROM promptdetail WHERE prompt_id = ? ORDER BY component_order ASC";
$stmt2 = $conn->prepare($sql_details);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$details = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

$tag_sql = "SELECT t.tag_id, t.tag_name FROM prompttag pt JOIN tag t ON pt.tag_id = t.tag_id WHERE pt.prompt_id = ?";
$tag_stmt = $conn->prepare($tag_sql);
$tag_stmt->bind_param("i", $id);
$tag_stmt->execute();
$tags = $tag_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$full_prompt = ($prompt['short_description'] ?? '');
foreach ($details as $d) $full_prompt .= "\n" . $d['content'];

$sql_cmt = "SELECT c.*, a.username, a.avatar FROM comment c JOIN account a ON c.account_id = a.account_id WHERE c.prompt_id = ? ORDER BY c.created_at DESC";
$stmt_cmt = $conn->prepare($sql_cmt);
$stmt_cmt->bind_param("i", $id);
$stmt_cmt->execute();
$comments = $stmt_cmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="detail-container">
  <button class="close-detail" onclick="smartGoBack()">×</button>

  <div class="detail-header">
    <div class="user-info">
      <img src="<?= htmlspecialchars($prompt['avatar'] ?? 'default_avatar.png') ?>" alt="<?= htmlspecialchars($prompt['username']) ?>">
      <div>
        <strong><?= htmlspecialchars($prompt['username']) ?></strong>
        <div class="date"><?= date('d/m/Y H:i', strtotime($prompt['create_at'])) ?></div>
      </div>
    </div>
  </div>

  <!-- Tiêu đề chính của bài viết -->
  <h1 class="detail-title"><?= htmlspecialchars($prompt['title'] ?: 'Không có tiêu đề') ?></h1>

  <!-- Mô tả ngắn (giữ nguyên như cũ, làm phần giới thiệu) -->
  <p class="detail-short-desc"><?= htmlspecialchars($prompt['short_description'] ?: '') ?></p>

  <?php if (!empty($tags)): ?>
    <div class="detail-tags">
      <?php foreach ($tags as $t): ?>
        <a class="tag-item" href="../user/home.php?tag=<?= $t['tag_id'] ?>">#<?= htmlspecialchars($t['tag_name']) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($prompt['image'])): ?>
    <img class="post-image" src="<?htmlspecialchars($prompt['image']) ?>" alt="Ảnh bài viết" style="max-width:100%;border-radius:8px;margin:20px 0;">
  <?php endif; ?>

  <div class="detail-content">
    <?php foreach ($details as $d): ?>
      <p><?= nl2br(htmlspecialchars($d['content'])) ?></p>
    <?php endforeach; ?>
  </div>

<div class="detail-actions">
  <?php if ($id_user > 0): ?>
    <button type="submit" name="loveBtn" class="love" title="Thích bài viết" value="<?= $id ?>">
      <i class="fa-heart <?= $is_loved ? 'fa-solid text-red' : 'fa-regular' ?>"></i> <?= (int)$prompt['love_count'] ?>
    </button>

    <button><i class="fa-regular fa-comment"></i> <?= (int)$prompt['comment_count'] ?></button>
    
    <button type="submit" name="saveBtn" class="save" title="Lưu bài viết" value="<?= $id ?>">
      <i class="fa-bookmark <?= $is_saved ? 'fa-solid text-blue' : 'fa-regular' ?>"></i> <?= (int)$prompt['save_count'] ?>
    </button>

    <!-- ĐÃ ĐĂNG NHẬP → cho phép mở modal chạy prompt -->
    <button type="button" class="run-btn" title="Xem kết quả" onclick="openRunModal()" 
            data-prompt="<?= htmlspecialchars($full_prompt, ENT_QUOTES) ?>">
      ⚡ Run Prompt
    </button>

  <?php else: ?>
    <!-- CHƯA ĐĂNG NHẬP → các nút bị disable + Run Prompt chuyển hướng login -->
    <button class="love disabled" title="Đăng nhập để thích"><i class="fa-regular fa-heart"></i> <?= (int)$prompt['love_count'] ?></button>
    <button class="save disabled" title="Đăng nhập để lưu"><i class="fa-regular fa-bookmark"></i> <?= (int)$prompt['save_count'] ?></button>

    <!-- Nút Run Prompt khi chưa đăng nhập → chuyển hướng login -->
    <a href="../../views/login/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
       class="run-btn" 
       title="Đăng nhập để chạy prompt"
       style="text-decoration:none;color:inherit;">
       Run Prompt
    </a>
  <?php endif; ?>
</div>

<?php if ($account_id > 0): ?>
    <div class="comment-form-new">
        <form method="post" action="../../Controller/user/process_comment.php" class="comment-input-form">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="prompt_id" value="<?= $id ?>">
            
            <div class="input-wrapper">
                <textarea name="comment_content" rows="1" placeholder="Viết bình luận..." required></textarea>
                <button type="submit" class="send-btn">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
<?php else: ?>
    <p style="text-align:center;padding:20px;background:#222;border-radius:12px;">
        Bạn cần <a href="../../views/login/login.php?redirect=<?= urlencode($redirect_url) ?>">đăng nhập</a> để bình luận.
    </p>
<?php endif; ?>

    <div class="comments-list">
      <?php if (empty($comments)): ?>
        <p style="text-align:center;color:#888;padding:30px;">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
        <?php else: foreach ($comments as $c): ?>
          <div class="comment-item">
            <div class="comment-avatar">
              <img src="<?= htmlspecialchars($c['avatar'] ?? 'default_avatar.png') ?>" alt="<?= htmlspecialchars($c['username']) ?>">
            </div>
            <div class="comment-body">
              <div class="comment-header">
                <strong><?= htmlspecialchars($c['username']) ?></strong>
                <span class="comment-date"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
              </div>
              <div class="comment-content"><?= nl2br(htmlspecialchars($c['content'])) ?></div>

              <?php if ($account_id == $c['account_id']): ?>
                <div class="comment-actions">
                  <details>
                    <summary>Sửa</summary>
                    <form method="post" action="../../Controller/user/process_comment.php" class="edit-comment-form">
                      <input type="hidden" name="action" value="edit">
                      <input type="hidden" name="prompt_id" value="<?= $id ?>">
                      <input type="hidden" name="comment_id" value="<?= $c['comment_id'] ?>">
                      <textarea name="comment_content" required><?= htmlspecialchars($c['content']) ?></textarea>
                      <button type="submit">Lưu</button>
                    </form>
                  </details>
                  <form method="post" action="../../Controller/user/process_comment.php" onsubmit="return confirm('Xóa bình luận này?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="prompt_id" value="<?= $id ?>">
                    <input type="hidden" name="comment_id" value="<?= $c['comment_id'] ?>">
                    <button type="submit" class="btn-delete-comment">Xoá</button>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          </div>
      <?php endforeach;
      endif; ?>
    </div>
  </div>
</div>

<!-- Modal Run Prompt -->
<div id="prompt-modal" style="display:none;">
  <div class="modal-overlay" onclick="closeRunModal()"></div>
  <div class="modal-content">
    <h3>Xác nhận chạy Prompt</h3>
    <textarea id="promptInput" rows="10"><?= htmlspecialchars($full_prompt) ?></textarea>
    <div class="modal-actions">
      <button class="cancel" onclick="closeRunModal()">Hủy</button>
      <button class="confirm" onclick="confirmRunPrompt()">Chạy ngay</button>
    </div>
  </div>
</div>

<div id="resultBox" style="display:none;"></div>

<script>

    const redirectUrl = "<?php echo htmlspecialchars($redirect_url); ?>";
    function smartGoBack() {
        window.location.href = redirectUrl;
    }
  function requireLogin() {
    if (confirm('Bạn cần đăng nhập để thực hiện hành động này!')) {
      location.href = '../../views/login/login.php?redirect=<?= urlencode($redirect_url) ?>';
    }
  }

  function openRunModal() {
    document.getElementById('prompt-modal').style.display = 'flex';
    document.getElementById('promptInput').focus();
  }

  function closeRunModal() {
    document.getElementById('prompt-modal').style.display = 'none';
  }

  function confirmRunPrompt() {
    const prompt = document.getElementById('promptInput').value;
    runPrompt(prompt); // Hàm từ run_api.js
    closeRunModal();
  }
</script>

<script src="../../public/js/run_api.js"></script>

<?php include_once __DIR__ . '/layout/footer.php'; ?>