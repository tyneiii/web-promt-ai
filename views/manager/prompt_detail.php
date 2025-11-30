<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Duyệt Prompt #<?= $_GET['id'] ?? '' ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/manager/sidebar.css">
  <link rel="stylesheet" href="../../public/css/manager/prompt_detail.css">
</head>
<body>

<?php
include_once __DIR__ . "/../../config.php";
include_once __DIR__ . "/../../Controller/user/prompt.php";
include_once __DIR__ . "/../../Controller/user/report.php";

$prompt_id = (int)($_GET['id'] ?? 0);
if ($prompt_id <= 0) die("ID không hợp lệ");

$prompt = getPromptDetails($conn, $prompt_id);
if (!$prompt) die("Bài viết không tồn tại");

$reports = getReports($conn, $prompt_id);

// Tạo full prompt
$details = $conn->query("SELECT content FROM promptdetail WHERE prompt_id = $prompt_id ORDER BY component_order")->fetch_all(MYSQLI_ASSOC);
$full_prompt = ($prompt['short_description'] ?? '');
foreach ($details as $d) $full_prompt .= "\n" . $d['content'];
?>

<div class="container">
  <div class="main">
    <div class="section section-1">
      <div class="content-wrapper">
        <div class="left-column">
          <div class="left-side">
            <div class="post-preview">
              <div class="post-header">
                <div class="user-info">
                  <img src="../../public/img/<?= htmlspecialchars($prompt['avatar'] ?? 'default_avatar.png') ?>" alt="Avatar" class="user-avatar-img">
                  <div class="user-details">
                    <h3><?= htmlspecialchars($prompt['username']) ?></h3>
                  </div>
                </div>
                <div>
                  <label>Tiêu đề</label>
                  <input type="text" class="title-input" value="<?= htmlspecialchars($prompt['title'] ?? $prompt['short_description'] ?? '') ?>" readonly>
                </div>
              </div>
              <label>Mô tả ngắn</label>
              <textarea class="short-desc" readonly><?= htmlspecialchars($prompt['short_description'] ?? '') ?></textarea>
            </div>

            <div class="button-group">
              <button class="action-btn back-btn" onclick="window.history.back()">Back</button>
              <button class="action-btn run-ai-btn" id="runBtn">Run Prompt với AI</button>
            </div>
          </div>

          <label id="labelImg">Ảnh tham khảo</label>
          <div class="bottom-side">
            <?php if (!empty($prompt['image'])): ?>
              <img class="post-image" src="../../public/<?= htmlspecialchars($prompt['image']) ?>" alt="Ảnh bài viết" style="cursor:pointer;">
            <?php else: ?>
              <span>Bài viết không có ảnh</span>
            <?php endif; ?>
          </div>
        </div>

        <div class="right-column">
          <div class="right-side">
            <div class="info-block">
              <label>Nội dung Prompt đầy đủ</label>
              <textarea readonly class="content"><?= htmlspecialchars($full_prompt) ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KẾT QUẢ + NÚT DUYỆT -->
    <div class="section section-2">
      <h2 class="section-title">Kết quả chạy thử Prompt</h2>

      <div id="run-result">
        <div style="text-align:center; padding:80px 20px; color:#888;">
          <i class="fa-solid fa-circle-info" style="font-size:48px; opacity:0.4;"></i><br><br>
          <strong>Chưa chạy thử</strong><br>
          <small>Nhấn nút "Run Prompt với AI" để xem kết quả</small>
        </div>
      </div>

      <!-- NÚT DUYỆT / TỪ CHỐI / XÓA – CHỈ HIỆN SAU KHI CHẠY THÀNH CÔNG -->
      <div id="actionButtons" style="margin-top:20px; text-align:center; display:none;">
        <?php if ($prompt['status'] === 'waiting'): ?>
          <button class="action-btn approve-btn" onclick="handleAction('approve', <?= $prompt_id ?>)">Duyệt bài đăng</button>
          <button class="action-btn reject-btn" onclick="rejectWithReason(<?= $prompt_id ?>)">Từ chối bài đăng</button>
        <?php elseif ($prompt['status'] === 'report'): ?>
          <button class="action-btn approve-btn" onclick="handleAction('unreport', <?= $prompt_id ?>)">Bỏ báo cáo (Duyệt lại)</button>
          <button class="action-btn delete-btn" style="background:#e74c3c;" onclick="handleAction('delete', <?= $prompt_id ?>)">Xóa vĩnh viễn</button>
        <?php else: ?>
          <button class="action-btn back-btn" onclick="window.history.back()">Trở về</button>
        <?php endif; ?>
      </div>

      <div class="report-button" onclick="openReportPopup()" title="Xem báo cáo">
        <span class="report-badge"><?= $reports->num_rows ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Modal ảnh + báo cáo giữ nguyên -->
<div id="imageModal" class="modal">...</div>
<div id="reportPopup" class="report-popup">...</div>

<script>
// Truyền dữ liệu từ PHP
window.fullPrompt = <?= json_encode($full_prompt, JSON_HEX_QUOT | JSON_HEX_APOS) ?>;
window.promptStatus = "<?= $prompt['status'] ?>";
window.currentPromptId = <?= $prompt_id ?>;

// Xử lý tất cả hành động admin
function handleAction(action, promptId, comment = null) {
  if (!confirm('Xác nhận thực hiện hành động này?')) return;

  const payload = { action, prompt_id: promptId };
  if (comment !== null) payload.comment = comment;

  fetch('../../public/ajax/prompt_detail.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(d => {
    alert(d.success ? 'Thành công! ' + (d.message || '') : 'Lỗi: ' + (d.message || ''));

    if (d.success) {
      window.history.back();
    }
  })
  .catch(() => {
    alert('Lỗi kết nối server');
  });
}

// CHẠY PROMPT VỚI AI
document.getElementById('runBtn')?.addEventListener('click', async () => {
  const resultBox = document.getElementById('run-result');
  const actionButtons = document.getElementById('actionButtons');

  resultBox.innerHTML = `
    <div style="text-align:center; padding:60px; color:#888;">
      <i class="fa-solid fa-spinner fa-spin" style="font-size:48px; margin-bottom:20px;"></i><br><br>
      <strong>Đang chạy thử prompt...</strong><br><small>Đợi 5-15 giây...</small>
    </div>`;

  try {
    const res = await fetch("../../api/run_api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ prompt: window.fullPrompt })
    });
    const data = await res.json();

    if (data.error) throw new Error(data.error);

    resultBox.innerHTML = `
      <div style="background:#0a2e0a; border-left:5px solid #00ff88; padding:20px; border-radius:12px; color:#a0ffa0;">
        <strong>Thành công! Kết quả từ AI:</strong><br><br>
        <div style="background:#000; padding:16px; border-radius:8px; white-space:pre-wrap; max-height:500px; overflow-y:auto; font-family:'Courier New', monospace;">
          ${data.result.replace(/\n/g, '<br>')}
        </div>
      </div>`;

    // HIỆN NÚT DUYỆT SAU KHI CHẠY THÀNH CÔNG
    actionButtons.style.display = 'block';

  } catch (err) {
    resultBox.innerHTML = `
      <div style="background:#2e0a0a; border-left:5px solid #ff3366; padding:20px; border-radius:12px; color:#ffa0a0;">
        <strong>Lỗi:</strong> ${err.message}<br><br>
        <small>Thử lại sau hoặc kiểm tra token HF</small>
      </div>`;
  }
});

// Modal ảnh (giữ nguyên)
document.querySelector('.post-image')?.addEventListener('click', function() {
  document.getElementById('imageModal').style.display = 'flex';
  document.getElementById('img01').src = this.src;
});
document.querySelectorAll('.modal, .close').forEach(el => {
  el.addEventListener('click', () => document.getElementById('imageModal').style.display = 'none');
});
</script>

</body>
</html>