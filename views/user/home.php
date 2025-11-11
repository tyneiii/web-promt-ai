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
    if(!text) return alert("Prompt trống!");
    try {
        const resp = await fetch("../../api/run_api.php", {
            method:"POST",
            headers:{"Content-Type":"application/json"},
            body: JSON.stringify({prompt:text})
        });

        if(!resp.ok) {
            const err = await resp.text();
            alert("Lỗi server: " + resp.status + "\n" + err.substring(0,200));
            return;
        }

        const data = await resp.json();
        let result = data.result || data.choices?.[0]?.message?.content || "Không có dữ liệu trả về.";
        alert("✅ Kết quả:\n\n" + result);
        closePromptModal();
    } catch(err) {
        alert("❌ Lỗi: " + err.message);
        console.error(err);
    }
}
</script>


<?php include_once __DIR__ . '/layout/footer.php'; ?>
