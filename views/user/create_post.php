<?php
    include_once __DIR__ . '/../../config.php';
    if (!isset($_SESSION['id_user'])) {
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

    $acc_id = $_SESSION['id_user'];

    $imageName = "";

    // Upload image
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../../public/img/" . $imageName);
    }

    // INSERT prompt
    $stmt = $conn->prepare("
        INSERT INTO prompt (account_id, title, short_description, image, status, love_count, comment_count, save_count, create_at)
        VALUES (?, ?, ?, ?, 'waiting', 0, 0, 0, NOW())
    ");
    $stmt->bind_param("isss", $acc_id, $title, $short, $imageName);
    $stmt->execute();

    $prompt_id = $stmt->insert_id;

    // INSERT promptdetail
    $order = 1;
    $detailStmt = $conn->prepare("INSERT INTO promptdetail (prompt_id, component_order, content) VALUES (?, ?, ?)");
    foreach ($contents as $ct) {
        $detailStmt->bind_param("iis", $prompt_id, $order, $ct);
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
    // Chuyển về trang chủ
    header("Location: home.php?msg=submitted");
    exit;
}

    $acc_id = $_SESSION['id_user'] ;
    $sql_user = "SELECT * FROM account WHERE account_id = $acc_id ";
    // Lấy danh sách tag từ DB
    $tag_query = "SELECT tag_id, tag_name FROM tag ORDER BY tag_name ASC";
    $tag_result = mysqli_query($conn, $tag_query);

    $tags_list = [];
    while ($row = mysqli_fetch_assoc($tag_result)) {
        $tags_list[] = $row;
    }
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
      <?= $_SESSION['create_post_error']; unset($_SESSION['create_post_error']); ?>
    </div>
  <?php endif; ?>

  <form class="form-card" action="create_post.php" method="POST" enctype="multipart/form-data">
    <div class="user-info">
      <img src="../../public/img/<?= $user['avatar'] ?? 'avatar.png' ?>" class="avatar">
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

  /* ===== Các hàm cũ giữ nguyên ===== */
  function confirmCancel() {
    if (confirm("Bạn có chắc chắn muốn hủy bài viết này không?")) {
      history.back();
    }
  }

  function handleSubmit() {
    if (chosen.length === 0) {
      alert("Vui lòng chọn ít nhất 1 chủ đề trước khi đăng bài!");
      return false; // ❌ chặn submit
    }
    // cập nhật hidden input trước khi gửi form
    updateTagsHidden();
    return true; // ✅ cho submit
  }


  /* ===== Multi-select chủ đề (giữ nguyên) ===== */
  const topicInput = document.querySelector(".topic-input");
  const topicDropdown = document.querySelector(".topic-dropdown");
  const selectedTopics = document.querySelector(".selected-topics");
  const topics = <?= json_encode($tags_list, JSON_UNESCAPED_UNICODE); ?>;
  let chosen = [];
  // (Toàn bộ các hàm renderDropdown, selectTopic, removeTopic, renderSelected và event listeners cho topicInput được giữ nguyên như file cũ của bạn)
  function renderDropdown(filter = "") {
    topicDropdown.innerHTML = "";
    topics
  .filter(t => t.tag_name.toLowerCase().includes(filter.toLowerCase()) 
           && !chosen.some(c => c.tag_id === t.tag_id))
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