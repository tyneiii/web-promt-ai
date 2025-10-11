<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Tạo bài đăng</title>
  <link rel="stylesheet" href="../../public/css/create_post.css">
</head>
<body> 
  <main class="composer-wrap">
    <button type="button" class="close-btn" title="Hủy bài viết mới" onclick="confirmCancel()">x</button>
    <div class="composer card">
      <div class="composer-top">
        <div class="top-left">
          <a href="user_main_page.php">
            <img class="avatar" src="https://i.pravatar.cc/40?img=12" alt="avatar">
          </a>
          <div class="composer-inputs">
            <a href="user_main_page.php" style="text-decoration: none;">
              <div class="who">Phạm Trung Kiên</div>
            </a>
          </div>
        </div>
        <h2 style="margin: 0;color:red;-webkit-text-stroke: 0.8px black;">Chia sẻ Prompt</h2>
        <div class="topic-container">
          <input type="text" class="topic-input" placeholder="Chọn chủ đề...">
          <div class="topic-dropdown"></div>
          <div class="selected-topics"></div>
        </div>
      </div>

      <input type="text" class="title-input" placeholder="Tiêu đề bài đăng" required>
      <textarea class="composer-textarea" placeholder="Mô tả ngắn gọn về prompt" required oninput="autoGrow(this)"></textarea>

            <div class="preview">
        <label for="upload" class="upload-placeholder">+ Thêm mô tả bằng ảnh tại đây</label>
        <input type="file" id="upload" accept="image/*" hidden>
      </div>

      <!-- 🔽 BỐ CỤC 2 CỘT CHO 4 PHẦN -->
      <div class="prompt-sections">
        <div class="section">
          <h3>1. Mục tiêu</h3>
          <textarea class="composer-textarea small" placeholder="Mục tiêu" required oninput="autoGrow(this)"></textarea>
        </div>
        <div class="section">
          <h3>2. Vai trò</h3>
          <textarea class="composer-textarea small" placeholder="Vai trò của AI" required oninput="autoGrow(this)"></textarea>
        </div>
        <div class="section">
          <h3>3. Ngữ cảnh</h3>
          <textarea class="composer-textarea small" placeholder="Ngữ cảnh" required oninput="autoGrow(this)"></textarea>
        </div>
        <div class="section">
          <h3>4. Nhiệm vụ</h3>
          <textarea class="composer-textarea small" placeholder="Nhiệm vụ của AI" required oninput="autoGrow(this)"></textarea>
        </div>
      </div>
      <div class="composer-actions">
        <label for="upload" class="action-btn">📷 Ảnh/Video</label>
        <div class="right">
          <button class="btn ghost" onclick="confirmCancel()" title="Hủy bài viết">Hủy</button>
          <button class="btn primary" title="Đăng bài">Đăng</button>
        </div>
      </div>
    </div>
  </main>

  <script>
function autoGrow(el) {
  el.style.height = 'auto';
  el.style.height = el.scrollHeight + 'px';
}

/* ===== MULTI SELECT CHỦ ĐỀ ===== */
const topicInput = document.querySelector(".topic-input");
const topicDropdown = document.querySelector(".topic-dropdown");
const selectedTopics = document.querySelector(".selected-topics");

const topics = [
  "Công nghệ", "AI", "Học tập", "Thiết kế", "Phần mềm", 
  "Kinh doanh", "Marketing", "Thủ thuật", "Đời sống", "Sáng tạo"
];
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

/* ===== XÁC NHẬN HỦY ===== */
function confirmCancel() {
  if (confirm("Bạn có chắc chắn muốn hủy bài viết này không?")) {
    history.back();
  }
}
  </script>
</body>
</html>
  