<?php
include_once __DIR__ . '/../../config.php';
if (!isset($_SESSION['account_id'])) {
  header("Location: ../login/login.php");
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $short = $_POST['short_description'];
  $contents = $_POST['content'];

  $rawTags = $_POST['tags'] ?? '[]';
  $tags = json_decode($rawTags, true);
  if (!is_array($tags)) {
    $tags = [];
  }

  // ✅ BẮT BUỘC PHẢI CÓ ÍT NHẤT 1 CHỦ ĐỀ
  if (empty($tags)) {
    $_SESSION['create_post_error'] = "Vui lòng chọn ít nhất 1 chủ đề cho bài viết.";
    header("Location: create_post.php");
    exit;
  }

  $acc_id = $_SESSION['account_id'];
  $imageName = "";

  // Upload image
  if (!empty($_FILES['image']['name'])) {
    $imageName =  "../../public/img/" . time() . "_" . $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], $imageName);
  }

  // INSERT prompt
  $stmt = $conn->prepare("
        INSERT INTO prompt (account_id, title, short_description, image, status, love_count, comment_count, save_count, create_at)
        VALUES (?, ?, ?, ?, 'waiting', 0, 0, 0, NOW())
    ");
  $stmt->bind_param("isss", $acc_id, $title, $short, $imageName);
  $stmt->execute();

  $prompt_id = $stmt->insert_id;
  $conn->query("
      INSERT INTO admin_notifications (type, prompt_id, message)
      VALUES ('waiting', $prompt_id, 'Có bài viết mới chờ duyệt (#$prompt_id)')
    ");


  // INSERT promptdetail
  $order = 1;
  $detailStmt = $conn->prepare("INSERT INTO promptdetail (prompt_id, content) VALUES (?, ?)");
  foreach ($contents as $ct) {
    $detailStmt->bind_param("is", $prompt_id, $ct);
    $detailStmt->execute();
    $order++;
  }

  // INSERT prompttag
  if (!empty($tags)) {
    foreach ($tags as $tag_id) {
      $tag_id = (int)$tag_id;
      $conn->query("INSERT INTO prompttag (prompt_id, tag_id) VALUES ($prompt_id, $tag_id)");
    }
  }

  // === SAU KHI INSERT XONG TẤT CẢ ===
  $_SESSION['create_post_success'] = true;
  $_SESSION['success_message'] = "Bài viết của bạn đã được gửi thành công và đang chờ duyệt bởi quản trị viên!";

  // ĐÚNG: phải dùng show_success_modal=1
  header("Location: create_post.php?show_success_modal=1");
  exit;
}

$acc_id = $_SESSION['account_id'];
$sql_user = "SELECT * FROM account WHERE account_id = $acc_id ";
// Lấy danh sách tag từ DB
// $tag_query = "SELECT tag_id, tag_name FROM tag ORDER BY tag_name ASC";
// $tag_result = mysqli_query($conn, $tag_query);

// $tags_list = [];
// while ($row = mysqli_fetch_assoc($tag_result)) {
//   $tags_list[] = $row;
// }
$tags_list = [
    ['tag_id' => 1, 'tag_name' => 'Công nghệ'],
    ['tag_id' => 2, 'tag_name' => 'Thiết kế'],
    ['tag_id' => 3, 'tag_name' => 'Lập trình'],
    ['tag_id' => 4, 'tag_name' => 'Kinh doanh'],
    ['tag_id' => 5, 'tag_name' => 'Sức khỏe & Sắc đẹp'],
    ['tag_id' => 6, 'tag_name' => 'Ẩm thực & Du lịch'],
    ['tag_id' => 7, 'tag_name' => 'Tài chính & Đầu tư'],
];

$user_result = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($user_result);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tạo bài viết - Giao diện mới</title>
  <link rel="stylesheet" href="../../public/css/user/create_post.css">
</head>

<body>
  <button type="button" class="close-btn" title="Hủy bài viết mới" onclick="confirmCancel()">×</button>
  <?php if (!empty($_SESSION['create_post_error'])): ?>
    <div style="margin: 10px; padding: 8px 12px; border-radius: 6px; background:#ffdddd; color:#b30000;">
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

    <style>
      /* Fix cứng giữa màn hình - hoạt động mọi trường hợp */
      .modal-overlay {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.75);
        display: flex !important;
        align-items: center;
        justify-content: center;
        z-index: 999999 !important;
        padding: 20px;
        box-sizing: border-box;
      }

      .modal-content {
        background: white;
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        max-width: 480px;
        width: 100%;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.35);
        animation: modalPop 0.4s ease-out;
        transform: translateY(0);
      }

      @keyframes modalPop {
        from {
          transform: scale(0.8) translateY(-50px);
          opacity: 0;
        }

        to {
          transform: scale(1) translateY(0);
          opacity: 1;
        }
      }

      .modal-icon {
        font-size: 70px;
        color: #ea1710ff;
        margin-bottom: 10px;
      }

      .modal-content h2 {
        margin: 10px 0 16px;
        color: #f60404ff;
        font-size: 26px;
      }

      .modal-content p {
        color: #555;
        font-size: 16px;
        line-height: 1.6;
        margin-bottom: 28px;
      }

      .modal-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
      }

      .btn-primary,
      .btn-secondary {
        padding: 14px 28px;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        min-width: 180px;
        transition: all 0.2s;
      }

      .btn-primary {
        background: #f00a0aff;
        color: white;
      }

      .btn-primary:hover {
        background: #f40808ff;
        transform: translateY(-2px);
      }

      .btn-secondary {
        background: #f3f4f6;
        color: #374151;
      }

      .btn-secondary:hover {
        background: #e5e7eb;
        transform: translateY(-2px);
      }
    </style>

    <script>
      function closeModalAndRedirect() {
        document.body.style.overflow = 'auto';
        window.location.href = 'home.php';
      }

      function closeModalAndStay() {
        document.getElementById('successModal').remove();
        document.body.style.overflow = 'auto';

        // Reset form sạch sẽ
        document.querySelector('.form-card').reset();
        document.getElementById('image-preview').innerHTML = '';
        chosen = [];
        renderSelected();
        updateTagsHidden();

        // Đóng hết collapsible
        document.querySelectorAll('.collapsible-group').forEach(g => g.classList.remove('active'));
      }

      // Fix lỗi cuộn trang khi modal mở
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
  </form>

  <!-- MODAL THÀNH CÔNG  -->
  <?php if (isset($_GET['show_success_modal']) && $_GET['show_success_modal'] == '1' && isset($_SESSION['create_post_success'])): ?>
    <div id="successModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-icon">✓</div>
        <h2>Đăng bài thành công!</h2>
        <p id="modal-message">
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

    <style>
      .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease-out;
      }

      .modal-content {
        background: white;
        border-radius: 16px;
        padding: 30px 40px;
        text-align: center;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: zoomIn 0.4s ease-out;
      }

      .modal-icon {
        font-size: 64px;
        color: #22c55e;
        margin-bottom: 16px;
      }

      .modal-content h2 {
        margin: 0 0 16px;
        color: #22c55e;
        font-size: 28px;
      }

      .modal-content p {
        color: #444;
        font-size: 16px;
        line-height: 1.5;
        margin-bottom: 24px;
      }

      .modal-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
      }

      .btn-primary,
      .btn-secondary {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        min-width: 160px;
      }

      .btn-primary {
        background: #22c55e;
        color: white;
      }

      .btn-primary:hover {
        background: #16a34a;
      }

      .btn-secondary {
        background: #e5e7eb;
        color: #374151;
      }

      .btn-secondary:hover {
        background: #d1d5db;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
        }

        to {
          opacity: 1;
        }
      }

      @keyframes zoomIn {
        from {
          transform: scale(0.7);
          opacity: 0;
        }

        to {
          transform: scale(1);
          opacity: 1;
        }
      }
    </style>

    <script>
      // Đóng modal và chuyển về trang chủ / danh sách bài viết
      function closeModalAndRedirect() {
        document.getElementById('successModal').remove();
        window.location.href = 'home.php'; // hoặc trang danh sách bài viết của user
      }

      // Đóng modal và ở lại để tạo bài mới (xóa form)
      function closeModalAndStay() {
        document.getElementById('successModal').remove();
        // Reset form để tạo bài mới
        document.querySelector('.form-card').reset();
        document.querySelectorAll('.collapsible-content').forEach(el => el.innerHTML = el.innerHTML.split('<textarea')[0] + '<textarea name="content[]" placeholder="Nhập nội dung chính của bài viết..." required></textarea>');
        document.getElementById('image-preview').innerHTML = '';
        chosen = [];
        renderSelected();
        updateTagsHidden();
      }

      // Tự động focus vào modal (cho đẹp)
      document.getElementById('successModal')?.focus();
    </script>

    <?php
    // Xóa session để không hiện lại khi bấm F5
    unset($_SESSION['create_post_success']);
    unset($_SESSION['success_message']);
    ?>
  <?php endif; ?>
</body>

</html>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const collapsibleGroups = document.querySelectorAll('.collapsible-group');

    collapsibleGroups.forEach(group => {
      const header = group.querySelector('.collapsible-header');
      header.addEventListener('click', () => {
        const wasActive = group.classList.contains('active');

        collapsibleGroups.forEach(otherGroup => {
          otherGroup.classList.remove('active');
        });

        if (!wasActive) {
          group.classList.add('active');
        }
      });
    });

    // Xử lý xem trước ảnh
    const fileInput = document.getElementById('fileInput');
    const imagePreview = document.getElementById('image-preview');
    const uploadSection = document.getElementById('upload-section');

    fileInput.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          imagePreview.innerHTML = `<img src="${e.target.result}" alt="Image preview"/>`;
          imagePreview.style.display = 'block';
          // Tự động mở khu vực upload để người dùng thấy ảnh
          if (!uploadSection.classList.contains('active')) {
            uploadSection.querySelector('.collapsible-header').click();
          }
        }
        reader.readAsDataURL(file);
      }
    });
  });

  /*  Các hàm cũ giữ nguyên  */
  function confirmCancel() {
    if (confirm("Bạn có chắc chắn muốn hủy bài viết này không?")) {
      history.back();
    }
  }

  function handleSubmit() {
    if (chosen.length === 0) {
      alert("Vui lòng chọn ít nhất 1 chủ đề trước khi đăng bài!");
      return false; // chặn submit
    }
    // cập nhật hidden input trước khi gửi form
    updateTagsHidden();
    return true; // cho submit
  }

  /* Multi-select chủ đề (giữ nguyên)  */
  const topicInput = document.querySelector(".topic-input");
  const topicDropdown = document.querySelector(".topic-dropdown");
  const selectedTopics = document.querySelector(".selected-topics");
  const topics = [
    {"tag_id":1,"tag_name":"Công việc"},
    {"tag_id":2,"tag_name":"Công nghệ"},
    {"tag_id":3,"tag_name":"Học tập"},
    {"tag_id":4,"tag_name":"Sáng tạo nội dung"},
    {"tag_id":5,"tag_name":"Giải trí"},
    {"tag_id":6,"tag_name":"Phát triển bản thân"},
    {"tag_id":7,"tag_name":"Cuộc sống"},
    {"tag_id":8,"tag_name":"Kinh doanh"},
    {"tag_id":9,"tag_name":"Công cụ"},
    {"tag_id":10,"tag_name":"Khác"}
];

  let chosen = [];
  // (Toàn bộ các hàm renderDropdown, selectTopic, removeTopic, renderSelected và event listeners cho topicInput được giữ nguyên như file cũ của bạn)
  function renderDropdown(filter = "") {
    topicDropdown.innerHTML = "";
    topics
      .filter(t => t.tag_name.toLowerCase().includes(filter.toLowerCase()) &&
        !chosen.some(c => c.tag_id === t.tag_id))
      .forEach(t => {
        const div = document.createElement("div");
        div.textContent = t.tag_name;
        div.onclick = () => selectTopic(t);
        topicDropdown.appendChild(div);
      });
  }

  function selectTopic(topic) {
    if (chosen.length >= 3) {
      alert("Chỉ được chọn tối đa 3 chủ đề!");
      return;
    }
    chosen.push(topic);
    renderSelected();
    renderDropdown(topicInput.value);
    updateTagsHidden();
  }

  function removeTopic(tag_id) {
    tag_id = Number(tag_id); // ép kiểu để so sánh chính xác

    chosen = chosen.filter(t => Number(t.tag_id) !== tag_id);

    renderSelected();
    renderDropdown(topicInput.value);
    updateTagsHidden();
  }

  function renderSelected() {
    selectedTopics.innerHTML = "";
    chosen.forEach(t => {
      const tag = document.createElement("div");
      tag.className = "tag";
      tag.innerHTML = `#${t.tag_name} 
    <button type="button" onclick="event.stopPropagation(); removeTopic(${t.tag_id});">×</button>`;
      selectedTopics.appendChild(tag);
    });
  }
  topicInput.addEventListener("focus", () => {
    renderDropdown();
    topicDropdown.classList.add("show");
  });
  topicInput.addEventListener("blur", () => {
    setTimeout(() => topicDropdown.classList.remove("show"), 150);
  });
  topicInput.addEventListener("input", () => renderDropdown(topicInput.value));

  function updateTagsHidden() {
    document.getElementById("tags-hidden").value = JSON.stringify(
      chosen.map(t => t.tag_id)
    );
  }
</script>