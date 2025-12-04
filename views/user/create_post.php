<?php
// Include controller
include_once __DIR__ . '/../../Controller/user/create_post_controller.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head> 
  <link rel="icon" href="../../public/img/T1.png" type="image/png" sizes="180x180">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tạo bài viết - Giao diện mới</title>
  <link rel="stylesheet" href="../../public/css/user/create_post.css">
  <link rel="stylesheet" href="../../public/css/user/modal_success.css">
</head>

<body>
  <button type="button" class="close-btn" title="Hủy bài viết mới" onclick="confirmCancel()">×</button>
  
  <?php if (!empty($_SESSION['create_post_error'])): ?>
    <div class="error-message">
      <?= $_SESSION['create_post_error'];
      unset($_SESSION['create_post_error']); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['show_success_modal']) && isset($_SESSION['create_post_success'])): ?>
    <div id="successModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-icon">✓</div>
        <h2>Đăng bài thành công!</h2>
        <p>
          <?= $_SESSION['success_message'] ?? 'Bài viết của bạn đã được gửi và đang chờ duyệt.' ?>
        </p>
        <div class="modal-buttons">
          <button type="button" class="btn-primary" onclick="closeModalAndRedirect()">
            Xem danh sách bài viết
          </button>
          <button type="button" class="btn-secondary" onclick="closeModalAndStay()">
            Tạo bài viết mới
          </button>
        </div>
      </div>
    </div>

    <script>
      // Fix overflow khi modal mở
      document.body.style.overflow = 'hidden';
    </script>

    <?php
    unset($_SESSION['create_post_success']);
    unset($_SESSION['success_message']);
    ?>
  <?php endif; ?>

  <form class="form-card" action="create_post.php" method="POST" enctype="multipart/form-data">
    <div class="user-info">
      <img src="<?= $user['avatar'] ?? 'avatar.png' ?>" class="avatar">
      <div class="name"><?= $user['username'] ?></div>
      <div class="topic-container">
        <input type="text" class="topic-input" placeholder="Chọn chủ đề...">
        <div class="topic-dropdown"></div>
        <div class="selected-topics"></div>
        <input type="hidden" name="tags" id="tags-hidden">
      </div>
    </div>
    <div class="form-content">
      <div class="collapsible-group">
        <div class="collapsible-header">
          <span>Title</span>
          <span class="indicator">+</span>
        </div>
        <div class="collapsible-content">
          <input type="text" name="title" placeholder="Nhập tiêu đề cho bài viết của bạn..." required>
        </div>
      </div>

      <div class="collapsible-group">
        <div class="collapsible-header">
          <span>Description</span>
          <span class="indicator">+</span>
        </div>
        <div class="collapsible-content">
          <textarea name="short_description" placeholder="Thêm mô tả ngắn gọn..." required></textarea>
        </div>
      </div>

      <div class="collapsible-group">
        <div class="collapsible-header">
          <span>Content</span>
          <span class="indicator">+</span>
        </div>
        <div class="collapsible-content content-fields">
          <textarea name="content[]" placeholder="Nhập nội dung chính của bài viết..." required></textarea>
        </div>
      </div>

      <div class="collapsible-group" id="upload-section">
        <div class="collapsible-header">
          <span>+ Upload Image</span>
          <span class="indicator">+</span>
        </div>
        <div class="collapsible-content">
          <button type="button" class="upload-btn-main" onclick="document.getElementById('fileInput').click()">
            Chọn ảnh từ thiết bị
          </button>
          <input type="file" name="image" id="fileInput" accept="image/*" hidden>
          <div id="image-preview"></div>
        </div>
      </div>
    </div>

    <div class="form-footer">
      <div class="right-buttons">
        <button class="cancel-btn" onclick="confirmCancel()">Cancel</button>
        <button class="submit-btn" type="submit" onclick="return handleSubmit()">Upload</button>
      </div>
    </div>
</head>

</html>

<script src="../../public/js/create_post.js"></script>