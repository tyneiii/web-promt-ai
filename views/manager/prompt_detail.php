<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Duyệt Prompt</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/manager/sidebar.css">
  <link rel="stylesheet" href="../../public/css/manager/prompt_detail.css">
  <style>
  </style>
</head>
<body>
  <div class="container">
    <?php
    include_once __DIR__ . "/../../Controller/user/prompt.php";
    include_once __DIR__ . "/../../config.php";
    $id = $_GET['id'] ?? null;
    $prompt = getPromptDetails($conn, $id);
    ?>
    <div class="main">
      <div class="section section-1">
        <div class="top-row">
          <div class="left-side">
            <div class="post-preview">
              <div class="post-header">
                <div class="user-info">
                  <img src="<?= htmlspecialchars($prompt['avatar'] ?? '../../public/img/default_avatar.png') ?>" alt="Avatar" class="user-avatar-img">
                  <div class="user-details">
                    <h3><?= htmlspecialchars($prompt['username']) ?></h3>
                  </div>
                </div>
                <div>
                  <label>Tiêu đề</label>
                  <input type="text" class="title-input" value="<?= htmlspecialchars($prompt['title']) ?>" readonly>
                </div>
              </div>
              <label>Mô tả ngắn</label>
              <textarea class="short-desc" readonly><?= htmlspecialchars($prompt['short_description']) ?></textarea>
            </div>
            <div class="button-group">
              <button class="action-btn back-btn" onclick="window.history.back()">Back</button>
              <button class="action-btn run-btn">Run Prompt với AI</button>
            </div>
          </div>
          <div class="right-side">
            <div class="info-block">
              <label>Nội dung Prompt</label>
              <textarea readonly class="content"><?= htmlspecialchars($prompt['content']) ?></textarea>
            </div>
          </div>
        </div>
        <label id="labelImg">Ảnh tham khảo</label>
        <div class="bottom-side">
          <?php if (!empty($prompt['image'])): ?>
            <img class="post-image" src="../../public/<?= htmlspecialchars($prompt['image']) ?>" alt="Ảnh bài viết" style="cursor: pointer;">
          <?php else: ?>
            <span>Bài viết không có ảnh</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Khu vực hiển thị kết quả chạy prompt -->
      <div class="section section-2">
        <h2 class="section-title">
          <i class="fas fa-robot"></i> Kết quả chạy thử Prompt bằng AI
        </h2>
        <div id="run-result"></div>
      </div>
    </div>
  </div>

  <!-- Modal xem ảnh -->
  <div id="imageModal" class="modal">
    <span class="close">×</span>
    <img class="modal-content" id="img01">
  </div>

  <script>
    const promptStatus = "<?= htmlspecialchars($prompt['status'] ?? 'waiting') ?>";
    const currentPromptId = "<?= htmlspecialchars($id) ?>";
    const fullPromptContent = <?= json_encode($prompt['content'] ?? '') ?>;
  </script>
  <script src="../../public/js/post_detail.js"></script>
</body>
</html>