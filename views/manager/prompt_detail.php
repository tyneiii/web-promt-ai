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
  $details = $conn->query("SELECT content FROM promptdetail WHERE prompt_id = $prompt_id")->fetch_all(MYSQLI_ASSOC);
  $full_prompt = $prompt['short_description'] ?? '';
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
                    <img src="<?= htmlspecialchars($prompt['avatar'] ?? 'default_avatar.png') ?>" alt="Avatar" class="user-avatar-img">
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

            <label>Ảnh tham khảo</label>
            <div class="bottom-side">
              <?php if (!empty($prompt['image'])): ?>
                <img class="post-image" src="<?= htmlspecialchars($prompt['image']) ?>" alt="Ảnh bài viết" style="cursor:pointer; max-width:100%; border-radius:8px;">
              <?php else: ?>
                <span style="color:#888;">Bài viết không có ảnh</span>
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

      <!-- KẾT QUẢ CHẠY THỬ + NÚT ADMIN -->
      <div class="section section-2">
        <h2 class="section-title">Kết quả chạy thử Prompt</h2>
        <div id="run-result" style="margin:20px 0; min-height:100px;">
          <div style="text-align:center; padding:60px 20px; color:#888;">
            <i class="fa-solid fa-circle-info" style="font-size:48px; opacity:0.4;"></i><br><br>
            <strong>Chưa chạy thử</strong><br>
            <small>Nhấn "Run Prompt với AI" để xem kết quả</small>
          </div>
        </div>

        <div id="actionButtons" style="text-align:center; margin:30px 0;">
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

        <!-- NÚT BÁO CÁO (chỉ hiện khi có báo cáo) -->
        <?php if ($reports->num_rows > 0): ?>
          <div class="report-floating-btn" id="reportBtn" title="Xem chi tiết báo cáo">
            <i class="fas fa-flag"></i>
            <span class="report-count"><?= $reports->num_rows ?></span>
          </div>
        <?php endif; ?>

        <!-- POPUP BÁO CÁO -->
        <div id="reportModal" style="display:none;">
          <div class="report-modal-overlay"></div>
          <div class="report-modal-content">
            <div class="report-modal-header">
              <h3>Báo cáo bài viết #<?= $prompt_id ?> (<?= $reports->num_rows ?> lượt)</h3>
              <span class="report-modal-close">x</span>
            </div>
            <div class="report-modal-body">
              <?php
              $reports->data_seek(0);
              while ($report = $reports->fetch_assoc()):

                // ĐOẠN CODE AN TOÀN — BẮT BUỘC DÁN VÀO ĐÂY
                $reporter_id = $report['account_id'] ?? 0;
                $reporter = ['username' => 'Người dùng đã xóa', 'avatar' => 'default_avatar.png'];

                if ($reporter_id > 0) {
                  $stmt = $conn->prepare("SELECT username, avatar FROM account WHERE account_id = ?");
                  $stmt->bind_param("i", $reporter_id);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  if ($result && $result->num_rows > 0) {
                    $reporter = $result->fetch_assoc();
                  }
                  $stmt->close();
                }
                // Kết thúc đoạn code an toàn
              ?>
                <div class="report-item">
                  <div class="reporter">
                    <img src="../../public/img/<?= htmlspecialchars($reporter['avatar']) ?>" alt="avatar">
                    <div>
                      <strong><?= htmlspecialchars($reporter['username']) ?></strong>
                      <small><?= date('d/m/Y H:i', strtotime($report['created_at'])) ?></small>
                    </div>
                  </div>
                  <div class="reason">
                    <strong>Lý do:</strong>
                    <?= nl2br(htmlspecialchars($report['reason'] ?? 'Không có lý do')) ?>
                  </div>
                </div>
                <hr>
              <?php endwhile; ?>

              <?php if ($reports->num_rows == 0): ?>
                <p style="text-align:center; color:#999; padding:50px 0;">Chưa có báo cáo nào</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Modal ảnh -->
        <div id="imageModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.9); justify-content:center; align-items:center;">
          <img id="img01" style="max-width:90%; max-height:90%; border-radius:8px;">
        </div>

        <script>
          // Dữ liệu từ PHP
          window.fullPrompt = <?= json_encode($full_prompt, JSON_HEX_QUOT | JSON_HEX_APOS) ?>;
          window.promptStatus = "<?= $prompt['status'] ?>";
          window.currentPromptId = <?= $prompt_id ?>;

          // Xử lý hành động admin
          function handleAction(action, promptId, comment = null) {
            if (!confirm('Xác nhận thực hiện hành động này?')) return;
            const payload = {
              action,
              prompt_id: promptId
            };
            if (comment !== null) payload.comment = comment;

            fetch('../../public/ajax/prompt_detail.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
              })
              .then(r => r.json())
              .then(d => {
                alert(d.success ? 'Thành công! ' + (d.message || '') : 'Lỗi: ' + (d.message || ''));
                if (d.success) location.reload();
              })
              .catch(() => alert('Lỗi kết nối server'));
          }

          function rejectWithReason(promptId) {
            const reason = prompt('Nhập lý do từ chối (bắt buộc):');
            if (!reason || reason.trim() === '') {
              alert('Bạn phải nhập lý do!');
              return;
            }
            handleAction('reject', promptId, reason.trim());
          }

          // Chạy thử prompt
          document.getElementById('runBtn')?.addEventListener('click', async () => {
            const box = document.getElementById('run-result');
            box.innerHTML = `<div style="text-align:center; padding:60px; color:#666;"><i class="fa-solid fa-spinner fa-spin fa-3x"></i><br><br>Đang chạy thử...</div>`;

            try {
              const res = await fetch("../../api/run_api.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json"
                },
                body: JSON.stringify({
                  prompt: window.fullPrompt
                })
              });
              const data = await res.json();
              box.innerHTML = data.result ?
                `<div style="background:#f8fff8; border-left:5px solid #4CAF50; padding:20px; border-radius:8px;"><strong>Thành công!</strong><pre style="white-space: pre-wrap; background:#fff; padding:15px; border-radius:6px; margin-top:10px;">${data.result.replace(/\n/g, '<br>')}</pre></div>` :
                `<div style="background:#ffebee; border-left:5px solid #f44336; padding:20px; border-radius:8px; color:#c62828;"><strong>Lỗi:</strong> ${data.error || 'Không thể chạy'}</div>`;
            } catch (err) {
              box.innerHTML = `<div style="background:#ffebee; padding:20px; border-radius:8px; color:#c62828;"><strong>Lỗi kết nối API</strong></div>`;
            }
          });

          // Modal ảnh
          document.querySelector('.post-image')?.addEventListener('click', function() {
            const modal = document.getElementById('imageModal');
            document.getElementById('img01').src = this.src;
            modal.style.display = 'flex';
          });
          document.getElementById('imageModal')?.addEventListener('click', function() {
            this.style.display = 'none';
          });

          // === POPUP BÁO CÁO - HOẠT ĐỘNG 100% ===
          document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('reportModal');
            const btn = document.getElementById('reportBtn');
            const closeBtn = document.querySelector('.report-modal-close');
            const overlay = document.querySelector('.report-modal-overlay');

            if (!modal || !btn) return;

            btn.onclick = () => {
              modal.style.display = 'block';
              setTimeout(() => modal.classList.add('show'), 10);
            };

            const closeModal = () => {
              modal.classList.remove('show');
              setTimeout(() => modal.style.display = 'none', 300);
            };

            closeBtn.onclick = closeModal;
            overlay.onclick = closeModal;
            document.addEventListener('keydown', e => {
              if (e.key === 'Escape' && modal.style.display === 'block') closeModal();
            });
          });
          document.querySelectorAll('.short-desc, .info-block textarea').forEach(textarea => {
            const autoGrow = () => {
              textarea.style.height = 'auto';
              textarea.style.height = textarea.scrollHeight + 'px';
            };
            textarea.addEventListener('input', autoGrow);
            autoGrow();
          });
        </script>

        <style>
          /* Nút báo cáo */
          .report-floating-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: #e74c3c;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(231, 76, 60, 0.6);
            z-index: 997;
            border: 4px solid white;
            transition: all 0.3s ease;
          }

          .report-floating-btn:hover {
            transform: scale(1.15);
            background: #c0392b;
          }

          .report-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: white;
            color: #e74c3c;
            font-weight: bold;
            font-size: 12px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e74c3c;
          }

          /* Modal báo cáo */
          #reportModal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
          }

          .report-modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
          }

          .report-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            background: white;
            width: 90%;
            max-width: 600px;
            max-height: 85vh;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            opacity: 0;
            transition: all 0.3s ease;
          }

          #reportModal.show .report-modal-content {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
          }

          .report-modal-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
          }

          .report-modal-close {
            font-size: 32px;
            cursor: pointer;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
          }

          .report-modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
          }

          .report-modal-body {
            padding: 20px;
            max-height: 65vh;
            overflow-y: auto;
          }

          .report-item {
            margin-bottom: 18px;
          }

          .reporter {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
          }

          .reporter img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
          }

          .reason {
            background: #ea0808ff;
            padding: 14px;
            border-radius: 10px;
            border-left: 5px solid #e74c3c;
            font-size: 15px;
            line-height: 1.6;
          }

          .reason strong {
            color: #080808ff;
          }

          hr {
            border: none;
            border-top: 1px dashed #ddd;
            margin: 15px 0;
          }
        </style>

</body>

</html>