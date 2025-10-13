<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Form Nâng Cao</title>
  <link rel="stylesheet" href="../../public/css/create_post copy.css">
</head>

<body>

  <button type="button" class="close-btn" title="Hủy bài viết mới" onclick="confirmCancel()">×</button>

  <div class="form-card">
    <div class="user-info">
      <img class="avatar" src="https://i.pravatar.cc/40?img=12" alt="avatar">
      <div class="name">John Doe</div>

      <!-- Multi-select chủ đề -->
      <div class="topic-container">
        <input type="text" class="topic-input" placeholder="Chọn chủ đề...">
        <div class="topic-dropdown"></div>
        <div class="selected-topics"></div>
      </div>
    </div>

    <div class="input-group">
      <button class="btn-label" onclick="toggleInput('titleInput')">Title</button>
      <input type="text" id="titleInput" class="dynamic-input" placeholder="Type something...">
    </div>

    <div class="input-group">
      <button class="btn-label" onclick="toggleInput('descInput')">Description</button>
      <input type="text" id="descInput" class="dynamic-input" placeholder="Type something...">
    </div>

    <div class="input-group">
      <button class="btn-label" onclick="toggleInput('contentSection')">Content</button>
      <div id="contentSection" class="dynamic-input content-section">
        <label>1. Instruction</label>
        <input type="text" placeholder="Type something..." class="content-input">
        <label>2. Requirement</label>
        <input type="text" placeholder="Type something..." class="content-input">
      </div>
    </div>

    <button class="upload-btn" onclick="document.getElementById('fileInput').click()">+ Upload image</button>
    <input type="file" id="fileInput" accept="image/*" hidden>

    <div class="form-footer">
    
      <div class="right-buttons">
        <button class="cancel-btn" onclick="confirmCancel()">Cancel</button>
        <button class="submit-btn" onclick="handleSubmit()">Upload</button>
      </div>
    </div>
  </div>

  <script>
    /* ===== Toggle Input ===== */
    let currentOpen = null;

    function toggleInput(id) {
      const el = document.getElementById(id);
      const group = el.closest(".input-group");

      // Nếu đang mở -> đóng lại
      if (currentOpen === el) {
        el.style.display = "none";
        group.classList.remove("active");
        currentOpen = null;
        return;
      }

      // Đóng phần đang mở trước đó
      if (currentOpen) {
        currentOpen.style.display = "none";
        currentOpen.closest(".input-group").classList.remove("active");
      }

      // Mở phần mới
      el.style.display = el.tagName === "DIV" ? "flex" : "block";
      group.classList.add("active");
      currentOpen = el;
    }


    /* ===== Xác nhận hủy ===== */
    function confirmCancel() {
      if (confirm("Bạn có chắc chắn muốn hủy bài viết này không?")) {
        history.back();
      }
    }

    /* ===== Multi-select chủ đề ===== */
    const topicInput = document.querySelector(".topic-input");
    const topicDropdown = document.querySelector(".topic-dropdown");
    const selectedTopics = document.querySelector(".selected-topics");
    const topics = ["Công nghệ", "AI", "Học tập", "Thiết kế", "Phần mềm", "Kinh doanh", "Marketing", "Thủ thuật", "Đời sống", "Sáng tạo"];
    let chosen = [];

    function renderDropdown(filter = "") {
      topicDropdown.innerHTML = "";
      topics
        .filter(t => t.toLowerCase().includes(filter.toLowerCase()) && !chosen.includes(t))
        .forEach(t => {
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
    function handleSubmit() {
      if (chosen.length === 0) {
        alert("Vui lòng chọn ít nhất 1 chủ đề trước khi đăng bài!");
        return;
      }
      alert("Bài viết của bạn đã sẵn sàng để đăng!");
    }
  </script>
</body>

</html>