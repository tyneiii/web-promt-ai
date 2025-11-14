<?php
    include_once __DIR__ . '/../../config.php';
    $acc_id = $_SESSION['id_user'] ;
    $sql_user = "SELECT * FROM account WHERE account_id = $acc_id ";
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
  <div class="form-card">
    <div class="user-info">
      <img src="../../public/img/<?= $user['avatar'] ?? 'avatar.png' ?>" class="avatar">
    <div class="name"><?= $user['username'] ?></div>
      <div class="topic-container">
        <input type="text" class="topic-input" placeholder="Chọn chủ đề...">
        <div class="topic-dropdown"></div>
        <div class="selected-topics"></div>
      </div>
    </div>
    <div class="form-content">
      <div class="collapsible-group">
        <div class="collapsible-header">
          <span>Title</span>
          <span class="indicator">+</span>
        </div>
        <div class="collapsible-content">
          <input type="text" placeholder="Nhập tiêu đề cho bài viết của bạn...">
        </div>
      </div>

      <div class="collapsible-group">
        <div class="collapsible-header">
          <span>Description</span>
          <span class="indicator">+</span>
        </div>
        <div class="collapsible-content">
          <textarea placeholder="Thêm mô tả ngắn gọn..."></textarea>
        </div>
      </div>

      <div class="collapsible-group">
        <div class="collapsible-header">
          <span>Content</span>
          <span class="indicator">+</span>
        </div>
        <div class="collapsible-content content-fields">
        
          <textarea placeholder="Nhập nội dung chính của bài viết..."></textarea>
          
        </div>
      </div>

      <div class="collapsible-group" id="upload-section">
        <div class="collapsible-header">
          <span>+ Upload Image</span>
          <span class="indicator">+</span>
        </div>
        <div class="collapsible-content">
          <button class="upload-btn-main" onclick="document.getElementById('fileInput').click()">Chọn ảnh từ thiết bị</button>
          <input type="file" id="fileInput" accept="image/*" hidden>
          <div id="image-preview"></div>
        </div>
      </div>
    </div>

    <div class="form-footer">
      <div class="right-buttons">
        <button class="cancel-btn" onclick="confirmCancel()">Cancel</button>
        <button class="submit-btn" onclick="handleSubmit()">Upload</button>
      </div>
    </div>
  </div>
</body>

</html>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const collapsibleGroups = document.querySelectorAll('.collapsible-group');

    collapsibleGroups.forEach(group => {
      const header = group.querySelector('.collapsible-header');
      header.addEventListener('click', () => {
        // Kiểm tra xem nhóm này có đang mở không
        const wasActive = group.classList.contains('active');

        // Đóng tất cả các nhóm khác
        collapsibleGroups.forEach(otherGroup => {
          otherGroup.classList.remove('active');
        });

        // Nếu nhóm vừa click không phải là nhóm đang mở, thì mở nó ra
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

  /* ===== Các hàm cũ giữ nguyên ===== */
  function confirmCancel() {
    if (confirm("Bạn có chắc chắn muốn hủy bài viết này không?")) {
      history.back();
    }
  }

  function handleSubmit() {
    if (chosen.length === 0) {
      alert("Vui lòng chọn ít nhất 1 chủ đề trước khi đăng bài!");
      return;
    }
    alert("Bài viết của bạn đã sẵn sàng để đăng!");
  }

  /* ===== Multi-select chủ đề (giữ nguyên) ===== */
  const topicInput = document.querySelector(".topic-input");
  const topicDropdown = document.querySelector(".topic-dropdown");
  const selectedTopics = document.querySelector(".selected-topics");
  const topics = ["Công nghệ", "AI", "Học tập", "Thiết kế", "Phần mềm", "Kinh doanh", "Marketing", "Thủ thuật", "Đời sống", "Sáng tạo"];
  let chosen = [];
  // (Toàn bộ các hàm renderDropdown, selectTopic, removeTopic, renderSelected và event listeners cho topicInput được giữ nguyên như file cũ của bạn)
  function renderDropdown(filter = "") {
    topicDropdown.innerHTML = "";
    topics.filter(t => t.toLowerCase().includes(filter.toLowerCase()) && !chosen.includes(t)).forEach(t => {
      const div = document.createElement("div");
      div.textContent = t;
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
  }

  function removeTopic(topic) {
    chosen = chosen.filter(t => t !== topic);
    renderSelected();
    renderDropdown(topicInput.value);
  }

  function renderSelected() {
    selectedTopics.innerHTML = "";
    chosen.forEach(t => {
      const tag = document.createElement("div");
      tag.className = "tag";
      tag.innerHTML = `#${t} <button onclick="removeTopic('${t}')">×</button>`;
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
</script>