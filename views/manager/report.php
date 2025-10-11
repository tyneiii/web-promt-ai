<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý bài đăng</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/sidebar.css">
</head>

<body>
  <div class="container">
    <?php include_once __DIR__ . '/layout/sidebar.php'; ?>
    <div class="main">
      <?php
      $reports = [
        ['report_id' => 201, 'prompt_id' => 101, 'account_id' => 2, 'reason' => 'Nội dung không phù hợp', 'created_at' => '2025-10-01'],
        ['report_id' => 202, 'prompt_id' => 104, 'account_id' => 3, 'reason' => 'Spam hoặc quảng cáo', 'created_at' => '2025-10-02'],
        ['report_id' => 203, 'prompt_id' => 102, 'account_id' => 2, 'reason' => 'Vi phạm bản quyền', 'created_at' => '2025-10-03'],
        ['report_id' => 204, 'prompt_id' => 103, 'account_id' => 1, 'reason' => 'Ngôn từ không lịch sự', 'created_at' => '2025-10-04'],
      ];

      $search = $_GET['search'] ?? '';
      $selectedStatus = $_GET['status'] ?? '';

      $filteredReports = array_filter($reports, function ($r) use ($search) {
        if (!$search) return true;
        return stripos($r['prompt_id'], $search) !== false || stripos($r['reason'], $search) !== false;
      });
      ?>
      <fieldset class="account-fieldset">
        <legend>Bài đăng bị báo cáo</legend>
        <div class="top-bar">
          <div class="stats">
            Tổng số bài bị báo: <strong><?= count($filteredReports) ?></strong>
          </div>
          <div class="search-box" style="display: flex; gap: 10px; align-items: center;">
            <form method="get" style="display: flex; gap: 10px; align-items: center;">
              <input type="text" name="search" title="Tìm kiếm theo tiêu đề" placeholder="Tìm kiếm bài đăng..."
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

              <select name="status">
                <option value="">Tất cả</option>
                <option value="approved" <?= ($selectedStatus === 'approved') ? 'selected' : '' ?>>Approved</option>
                <option value="pending" <?= ($selectedStatus === 'pending') ? 'selected' : '' ?>>Pending</option>
                <option value="reported" <?= ($selectedStatus === 'reported') ? 'selected' : '' ?>>Reported</option>
              </select>

              <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Prompt ID</th>
                  <th>Account ID</th>
                  <th>Reason</th>
                  <th>Created At</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($filteredReports as $index => $report): ?>
                  <tr style="background-color: <?= $index % 2 === 0 ? '#ffffffff' : '#dcdbdbff' ?>;">
                    <td><?= $report['prompt_id'] ?></td>
                    <td><?= $report['account_id'] ?></td>
                    <td><?= htmlspecialchars($report['reason']) ?></td>
                    <td><?= (new DateTime($report['created_at']))->format('d/m/Y') ?></td>
                    <td class="actions">
                      <a href="check_report.php" class="btn-edit"><i class="fa-solid fa-magnifying-glass"></i> Kiểm tra</a>
                      <button class="btn-delete"><i class="fa-solid fa-trash"></i> Xóa</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </fieldset>
    </div>
  </div>
</body>

</html>