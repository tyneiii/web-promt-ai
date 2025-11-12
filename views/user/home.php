<?php
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../Controller/user/prompt.php'
?>
<link rel="stylesheet" href="../../public/css/run_prompt.css">
<?php
$id_user = $_SESSION['id_user'];
$search='';
if(isset($_GET['search'])){
  $search=$_GET['search'];
}
if (isset($_POST['loveBtn'])) {
  $id_prompt=(int)$_POST['loveBtn'];
  $mess = lovePrompt($id_user, $id_prompt, $conn);
}
$prompts = getPrompt($id_user,$search, $conn);

?>
<div class="left-sidebar">
  <i class="fa-regular fa-heart"></i>
  <a href="create_post.php" class="sidebar-btn" title="Tạo bài viết mới">
    <i class="fa-solid fa-plus"></i>
  </a>
  <i class="fa-regular fa-comment"></i>
</div>

<div class="right-sidebar">
  <div class="border-top"></div>
  <div class="border-bottom"></div>
  <h3>Bảng tin hot 🔥</h3>
  <div class="item">Prompt tạo ảnh phong cách anime</div>
  <div class="item">Prompt phân tích văn bản bằng GPT</div>
  <div class="item">Prompt viết bài SEO tự động</div>
  <div class="item">Prompt vẽ concept nhân vật fantasy</div>
  <div class="item">Prompt tạo website bằng HTML</div>
</div>

<div class="main-content">
  <?php foreach ($prompts as $prompt): ?>
    <form action="" method="post">
      <div class="card">
        <div class="card-header">
          <div class="user-info">
            <img src="../../public/img/avatar.png" alt="<?= $prompt['username'] ?>" style="width:35px; height:35px; border-radius:50%;">
            <strong><?= $prompt['username'] ?></strong>
          </div>
          <button class="report-btn" type="button"><i class="fa-solid fa-flag"></i> Báo cáo</button>
        </div>
        <h4><?= $prompt['description'] ?></h4>
        <p><?= implode('<br><br>', $prompt['details']) ?></p>
        <div class="card-buttons">
          <button type="submit" name="loveBtn" id='loveBtn' title="Tim bài viết" value="<?= $prompt['id'] ?> ">
            <i class="fa-heart <?= $prompt['is_loved'] ? 'fa-solid text-red' : 'fa-regular' ?>"></i>  <?= $prompt['love_count'] ?>
          </button>
          <button type="submit" name="cmtBtn" title="Bình luận bài viết" value="<?= $prompt['id'] ?>">
            <i class="fa-regular fa-comment"></i>  <?= $prompt['comment_count'] ?>
          </button>
          <button type="submit" name="saveBtn" title="Lưu bài viết" id='saveBtn' value="<?= $prompt['id'] ?>">
            <i class="fa-regular fa-bookmark"></i>  <?= $prompt['save_count'] ?>
          </button>
          <button type="button" title="Xem kết quả" id="runBtn"
            onclick="openPromptModal(`<?= htmlspecialchars($prompt['description'] . "\n" . implode("\n", $prompt['details']), ENT_QUOTES) ?>`)">
            ⚡ Run Prompt
          </button>
        </div>
      </div>
    </form>
  <?php endforeach; ?>
</div>
<script>
  async function runPrompt(text) {
    let edited = window.prompt(
      "Chạy prompt sau:\n" + text + "\n\nBạn có muốn chỉnh sửa không?",
      text
    );
    if (!edited) return;

    try {
      console.log('Gửi prompt:', edited); // Debug log

      const resp = await fetch("/web-promt-ai/api/run_api.php", { // Fix: Thêm / đầu
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          prompt: edited
        })
      });

      console.log('Response status:', resp.status); // Debug log

      if (!resp.ok) {
        const errorText = await resp.text();
        console.error('Server error:', resp.status, errorText);
        alert(`❌ Lỗi server: ${resp.status} (${resp.statusText})\nChi tiết: ${errorText.substring(0, 200)}...`);
        return;
      }

      const data = await resp.json();
      console.log('Raw data từ API:', data); // Debug: Log JSON đầy đủ

      let result = data.result || data.choices?.[0]?.message?.content || "Không có dữ liệu trả về.";

      alert("✅ Kết quả:\n\n" + result);
    } catch (error) {
      console.error('Lỗi JS:', error);
      alert("❌ Lỗi: " + error.message + "\nKiểm tra console để biết thêm.");
    }
  }
</script>

<div id="prompt-modal" style="display:none;">
  <div class="modal-overlay"></div>
  <div class="modal-content">
    <h3>Chạy Prompt</h3>
    <textarea id="modal-prompt-text" rows="6" style="width:100%;"></textarea>
    <div class="modal-actions">
      <button onclick="runModalPrompt()">Chạy</button>
      <button onclick="closePromptModal()">Hủy</button>
    </div>
  </div>
</div>

<script>
  function openPromptModal(text) {
    document.getElementById('modal-prompt-text').value = text;
    document.getElementById('prompt-modal').style.display = 'flex';
  }

  function closePromptModal() {
    document.getElementById('prompt-modal').style.display = 'none';
  }

  async function runModalPrompt() {
    const text = document.getElementById('modal-prompt-text').value;
    if (!text) return alert("Prompt trống!");
    try {
      const resp = await fetch("../../api/run_api.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          prompt: text
        })
      });

      if (!resp.ok) {
        const err = await resp.text();
        alert("Lỗi server: " + resp.status + "\n" + err.substring(0, 200));
        return;
      }

      const data = await resp.json();
      let result = data.result || data.choices?.[0]?.message?.content || "Không có dữ liệu trả về.";
      alert("✅ Kết quả:\n\n" + result);
      closePromptModal();
    } catch (err) {
      alert("❌ Lỗi: " + err.message);
      console.error(err);
    }
  }
</script>
<?php include_once __DIR__ . '/layout/footer.php'; ?>