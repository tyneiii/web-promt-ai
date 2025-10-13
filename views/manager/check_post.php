<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Duyệt Prompt</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/sidebar.css">
  <link rel="stylesheet" href="../../public/css/check_report.css">
</head>

<body>
  <div class="container">
    <?php
    $posts = [
      [
        'prompt_id' => 101,
        'account_id' => 1,
        'title' => 'Giải thích API đơn giản',
        'short_description' => 'API là giao diện cho phép các ứng dụng giao tiếp với nhau.',
        'status' => 'waiting',
        'created_' => '2025-10-01',
        'component_name' => 'Mục tiêu',
        'order' => 1,
        'content' => 'API là giao diện cho phép các ứng dụng giao tiếp với nhau. Bài viết này giải thích chi tiết từng phần của API.'
      ],
      [
        'prompt_id' => 101,
        'account_id' => 1,
        'title' => 'Giải thích API nâng cao',
        'short_description' => 'Chi tiết cách hoạt động của các endpoint và phương thức.',
        'status' => 'waiting',
        'created_' => '2025-10-02',
        'component_name' => 'Vai trò',
        'order' => 2,
        'content' => 'Bài viết này mô tả các thành phần của API, cách sử dụng endpoint và phương thức HTTP, cùng ví dụ minh họa.'
      ],
      [
        'prompt_id' => 104,
        'account_id' => 3,
        'title' => 'Poster game hành động nhân vật áo giáp',
        'short_description' => 'Thiết kế poster với nhân vật chính và bối cảnh game.',
        'status' => 'waiting',
        'created_' => '2025-10-03',
        'component_name' => 'Nhân vật',
        'order' => 1,
        'content' => 'Poster mô tả game hành động, tập trung vào nhân vật chính mặc áo giáp, bối cảnh chiến trường và nhiệm vụ của nhân vật.'
      ],
      [
        'prompt_id' => 104,
        'account_id' => 3,
        'title' => 'Poster game nâng cao',
        'short_description' => 'Chi tiết về ánh sáng, tông màu và hiệu ứng đồ họa.',
        'status' => 'waiting',
        'created_' => '2025-10-04',
        'component_name' => 'Bối cảnh',
        'order' => 2,
        'content' => 'Bài viết phân tích chi tiết poster game: ánh sáng, tông màu tối, hiệu ứng đồ họa, nhấn mạnh nhân vật chính.'
      ],
    ];
    $id = $_GET['id'] ?? null;
    $relatedPosts = array_filter($posts, fn($p) => $p['prompt_id'] == $id);
    if (!$relatedPosts) {
      echo "<p>Không tìm thấy bài viết với id: " . htmlspecialchars($id) . "</p>";
      exit;
    }
    $componentList = [];
    foreach ($relatedPosts as $p) {
      $componentList[] = [
        'name' => $p['component_name'],
        'content' => $p['content'],
        'order' => $p['order']
      ];
    }
    usort($componentList, fn($a, $b) => $a['order'] <=> $b['order']);
    $post = reset($relatedPosts);
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
                <input type="text" class="title-input" value="<?= htmlspecialchars($post['title']) ?>" readonly>
              </div>
              <textarea class="short-desc" readonly><?= htmlspecialchars($post['short_description']) ?></textarea>
            </div>
            <div class="button-group">
              <button class="action-btn back-btn" onclick="window.history.back()">Back</button>
              <button class="action-btn run-btn">Run</button>
            </div>
          </div>

          <div class="right-side">
            <?php foreach ($componentList as $comp): ?>
              <div class="info-block">
                <label><?= htmlspecialchars($comp['order'] . '. ' . $comp['name']) ?></label>
                <textarea readonly><?= htmlspecialchars($comp['content']) ?></textarea>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="bottom-side">
          <?php if (!empty($post['image'])): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="Ảnh bài viết" style="max-height:100%; max-width:100%; border-radius:10px;">
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