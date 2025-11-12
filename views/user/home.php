<?php 
include_once __DIR__ . '/layout/header.php'; 
include_once __DIR__ . '/../../config.php';
?>
<link rel="stylesheet" href="../../public/css/run_prompt.css">
<?php
$sql = 'SELECT prompt.prompt_id, account.username, account.avatar, prompt.short_description,
               promptdetail.content, prompt.love_count, prompt.save_count, prompt.comment_count
    FROM prompt
    JOIN account ON account.account_id = prompt.account_id
    JOIN promptdetail ON prompt.prompt_id = promptdetail.prompt_id
    ORDER BY prompt.prompt_id ASC';
$cards = $conn->query($sql);
$prompts = [];
while ($row = $cards->fetch_assoc()) {
    $id = $row['prompt_id'];
    if (!isset($prompts[$id])) {
        $prompts[$id] = [
            'username' => $row['username'],
            'avatar' => $row['avatar'],
            'description' => $row['short_description'],
            'details' => [],
            'love_count' => $row['love_count'],
            'comment_count' => $row['comment_count'],
            'save_count' => $row['save_count'],
        ];
    }
    $prompts[$id]['details'][] = $row['content'];
}
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
    <div class="card">
      <div class="card-header">
        <div class="user-info">
          <img src="../../public/img/avatar.png" alt="<?= $prompt['username'] ?>" style="width:35px; height:35px; border-radius:50%;">
          <strong><?= $prompt['username'] ?></strong>
        </div>
        <button class="report-btn"><i class="fa-solid fa-flag"></i> Báo cáo</button>
      </div>
      <h4><?= $prompt['description'] ?></h4>
      <p><?= implode('<br><br>', $prompt['details']) ?></p>
      <div class="card-buttons">
        <button><i class="fa-regular fa-heart"></i> <?= $prompt['love_count'] ?></button>
        <button><i class="fa-regular fa-comment"></i> <?= $prompt['comment_count'] ?></button>
        <button><i class="fa-regular fa-bookmark"></i> <?= $prompt['save_count'] ?></button>
        <button class="run-btn" onclick="openPromptModal(`<?= htmlspecialchars($prompt['description'] . "\n" . implode("\n", $prompt['details']), ENT_QUOTES) ?>`)">
          ⚡ Run Prompt
        </button>
      </div>
    </div>
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

        const resp = await fetch("/web-promt-ai/api/run_api.php", {  // Fix: Thêm / đầu
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ prompt: edited })
});

        console.log('Response status:', resp.status); // Debug log

        if (!resp.ok) {
            const errorText = await resp.text();
            console.error('Server error:', resp.status, errorText);
            alert(`❌ Lỗi server: ${resp.status} (${resp.statusText})\nChi tiết: ${errorText.substring(0, 200)}...`);
            return;
        }

        const data = await resp.json();
console.log('Raw data từ API:', data);  // Debug: Log JSON đầy đủ

let result = data.result || data.choices?.[0]?.message?.content || "Không có dữ liệu trả về.";

        alert("✅ Kết quả:\n\n" + result);
    } catch (error) {
        console.error('Lỗi JS:', error);
        alert("❌ Lỗi: " + error.message + "\nKiểm tra console để biết thêm.");
    }
}
</script>

<!-- Modal xác nhận -->
<div id="prompt-modal">
  <div class="modal-overlay" onclick="closePromptModal()"></div>
  <div class="modal-content">
    <h3>Xác nhận chạy prompt</h3>
    <p>Bạn có chắc chắn muốn chạy lệnh này không?</p>
    <div class="modal-actions">
      <button class="cancel" onclick="closePromptModal()">Hủy</button>
      <button class="confirm" onclick="confirmRunPrompt()">Chạy ngay</button>
    </div>
  </div>
</div>

<div id="resultBox"></div>

<script src="/web-promt-ai/public/js/run_api.js"></script>


<?php include_once __DIR__ . '/layout/footer.php'; ?>
