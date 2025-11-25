<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Duyệt Prompt</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/manager/sidebar.css">
  <link rel="stylesheet" href="../../public/css/manager/check_report.css">
</head>

<body>
  <div class="container">
    <?php
    include_once __DIR__ . "/../../Controller/user/prompt.php";
    include_once __DIR__ . "/../../config.php";
    $id = $_GET['id'] ?? null;
    $data = getPrompt($conn, $id);
    $prompt = $data["prompt"];
    $details = $data["details"];
    ?>
    <div class="main">
      <div class="section section-1">
        <div class="top-row">
          <div class="left-side">
            <div class="post-preview">
              <div class="post-header">
                <div class="user-info">
                  <h3>Phạm Trung Kiên</h3>
                </div>
                <div>
                  <label for="">Tiêu đề</label>
                  <input type="text" class="title-input" value="<?= htmlspecialchars($prompt['title']) ?>" readonly>
                </div>
              </div>
              <textarea class="short-desc" readonly><?= htmlspecialchars($prompt['short_description']) ?></textarea>
            </div>
            <div class="button-group">
              <button class="action-btn back-btn" onclick="window.history.back()">Back</button>
              <button class="action-btn run-btn">Run</button>
            </div>
          </div>

          <div class="right-side">
            <div class="info-block">
              <label for="">Nội dung</label>
              <textarea readonly><?= htmlspecialchars($details['content']) ?></textarea>
            </div>
          </div>
        </div>
        <label for="" id="labelImg">Ảnh kết quả </label>
        <div class="bottom-side">
          <?php if (!empty($prompt['image'])): ?>
            <img class="post-image" src="../../public/<?= htmlspecialchars($prompt['image']) ?>" alt="Ảnh bài viết">
          <?php else: ?>
            <span>Chưa có ảnh</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="section section-2">
        <h2 class="section-title">Kết quả test Prompt</h2>
        <div id="run-result"></div>
      </div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.short-desc').forEach(textarea => {
      textarea.style.height = textarea.scrollHeight + 'px';
      textarea.addEventListener('input', () => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
      });
    });
    const runBtn = document.querySelector('.run-btn');
    const runResult = document.getElementById('run-result');

    runBtn.addEventListener('click', () => {
      runResult.innerHTML = ''; // reset trước khi hiển thị

      // Tạo textarea
      const textarea = document.createElement('textarea');
      textarea.placeholder = 'Nhập nhận xét/test prompt...';
      textarea.style.width = '100%';
      textarea.style.minHeight = '100px';
      textarea.style.marginBottom = '10px';

      // Tạo nút Duyệt bài đăng
      const approveBtn = document.createElement('button');
      approveBtn.textContent = 'Duyệt bài đăng';
      approveBtn.className = 'action-btn run-btn';
      approveBtn.style.marginRight = '10px';

      // Tạo nút Từ chối bài đăng
      const rejectBtn = document.createElement('button');
      rejectBtn.textContent = 'Từ chối bài đăng';
      rejectBtn.className = 'action-btn back-btn';

      // Thêm vào Section 2
      runResult.appendChild(textarea);
      runResult.appendChild(approveBtn);
      runResult.appendChild(rejectBtn);
    });
  </script>
</body>

</html>