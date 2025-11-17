<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý bài đăng</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <?php include_once __DIR__ . '/layout/sidebar.php'; ?>
    <div class="main">
      <?php
      $search = $_GET['search'] ?? '';
      $reports = getReportedPrompts($conn, $search);
      ?>
      <fieldset class="account-fieldset">
        <legend>Bài đăng bị báo cáo</legend>
        <div class="top-bar">
          <div class="stats">
            Tổng số bài đăng: <strong><?= $reports->num_rows ?></strong>
          </div>
          <div class="search-box">
            <form method="get">
              <div class="search-group-styled">
                <input type="text" name="search" class="search-bar-styled"
                  title="Tìm kiếm theo tiêu đề" placeholder="Tìm kiếm bài đăng..."
                  value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="search-btn-styled">
                  <i class="fa-solid fa-magnifying-glass"></i>
                </button>
              </div>
            </form>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Prompt ID</th>
                  <th>Title</th>
                  <th>Status</th>
                  <th>Reason</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php $index = 0;
                while ($report = $reports->fetch_assoc()): ?>
                  <tr>
                    <td><?= $report['prompt_id'] ?></td>
                    <td><?= $report['title'] ?></td>
                    <td style="text-transform: capitalize; color:red; font-weight: bold;"><?= htmlspecialchars($report['status']) ?></td>
                    <td><?= $report['reason'] ?></td>
                    <td class="actions">
                      <a href="check_report.php?prompt_id=<?= $report['prompt_id'] ?>" class="btn-edit">
                        <i class="fa-solid fa-magnifying-glass"></i> Kiểm tra
                      </a>
                      <button class="btn-delete" data-prompt-id="<?= $report['prompt_id'] ?>">
                        <i class="fa-solid fa-trash"></i> Xóa
                      </button>
                    </td>
                  </tr>
                <?php $index++;
                endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </fieldset>
    </div>
  </div>
</body>

</html>