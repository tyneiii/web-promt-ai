<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý tài khoản</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/manager/sidebar.css">
</head>

<body>
  <div class="container">
    <?php include_once __DIR__ . '/layout/sidebar.php';
    include_once __DIR__ . '/../../helpers/helper.php' ?>
    <div class="main">
      <?php
      $action_result = handlePostActions($conn);
      if ($action_result) {
        $query_params = array_filter([
          'search' => $_GET['search'] ?? '',
          'role' => $_GET['role'] ?? '',
          'is_active' => $_GET['is_active'] ?? '',
          'search_columns' => $_GET['search_columns'] ?? [],
        ]);
        handlePrgRedirect($action_result, $query_params);
      }

      $mess = getMess();
      $search = $_GET['search'] ?? '';
      $role = $_GET["role"] ?? '';
      $is_active = $_GET["is_active"] ?? '';
      $search_columns = $_GET['search_columns'] ?? [];
      $accounts = getAccounts($conn, $search, $role, $search_columns, $is_active);
      ?>
      <fieldset class="account-fieldset">
        <legend>Quản lý tài khoản</legend>
        <div class="top-bar">
          <div class="stats">
            Tổng số tài khoản: <strong><?= $accounts->num_rows ?></strong>
          </div>
          <?php printMess($mess); ?>
          <div class="search-box">
            <form method="get" id="search-form">
              <?php foreach ($search_columns as $col_name): ?>
                <input type="hidden" name="search_columns[]" value="<?= htmlspecialchars($col_name) ?>">
              <?php endforeach; ?>
              <div class="search-group-styled">
                <input type="text" name="search" class="search-bar-styled"
                  title="Tìm kiếm theo tên tài khoản hoặc email" placeholder="Tìm kiếm tài khoản..."
                  value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="search-btn-styled">
                  <i class="fa-solid fa-magnifying-glass"></i>
                </button>
              </div>
              <select name="role" onchange="document.getElementById('search-form').submit()">
                <option value="">Tất cả tài khoản</option>
                <option value="Admin" <?= ($role === 'Admin') ? 'selected' : '' ?>>Admin</option>
                <option value="User" <?= ($role === 'User') ? 'selected' : '' ?>>User</option>
              </select>
              <select name="is_active" onchange="document.getElementById('search-form').submit()">
                <option value="">Tất cả trạng thái</option>
                <option value="1" <?= (string)$is_active === '1' ? 'selected' : '' ?>>Open</option>
                <option value="0" <?= (string)$is_active === '0' ? 'selected' : '' ?>>Block</option>
              </select>
            </form>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="table-container">
            <form method="get" id="column-search-form">
              <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
              <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
              <input type="hidden" name="is_active" value="<?= htmlspecialchars($is_active) ?>">
              <table>
                <thead>
                  <tr>
                    <th>
                      <label>
                        <input type="checkbox" name="search_columns[]" value="account_id"
                          <?= in_array('account_id', $search_columns) ? 'checked' : '' ?>
                          onchange="document.getElementById('column-search-form').submit()"> ID
                      </label>
                    </th>
                    <th>
                      <label>
                        <input type="checkbox" name="search_columns[]" value="username"
                          <?= in_array('username', $search_columns) ? 'checked' : '' ?>
                          onchange="document.getElementById('column-search-form').submit()"> Tên tài khoản
                      </label>
                    </th>
                    <th>
                      <label>
                        <input type="checkbox" name="search_columns[]" value="email"
                          <?= in_array('email', $search_columns) ? 'checked' : '' ?>
                          onchange="document.getElementById('column-search-form').submit()"> Email
                      </label>
                    </th>
                    <th>Loại tài khoản</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
            </form>
            <tbody>
              <?php while ($acc = $accounts->fetch_assoc()): ?>
                <tr>
                  <td><?= $acc["account_id"] ?></td>
                  <td><?= htmlspecialchars($acc["username"]) ?></td>
                  <td><?= htmlspecialchars($acc["email"]) ?></td>
                  <form method="POST" action="" class="role-update-form">
                    <td>
                      <select class="role-select" name="new_role" data-original-role="<?= $acc["role_name"] ?>">
                        <option value="1" <?= ($acc['role_name'] === 'Admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="2" <?= ($acc['role_name'] === 'User') ? 'selected' : '' ?>>User</option>
                      </select>
                    </td>
                    <td>
                      <?php if ($acc['is_active'] == 1): ?>
                        <span class="status-tag status-active">Open</span>
                      <?php else: ?>
                        <span class="status-tag status-inactive">Block</span>
                      <?php endif; ?>
                    </td>
                    <td class="actions">
                      <input type="hidden" name="account_id" value="<?= $acc['account_id'] ?>">

                      <?php if ($acc['is_active'] == 1): ?>
                        <button class="btn-action btn-lock" type="button"
                          data-account-id="<?= $acc['account_id'] ?>" data-action="lock">
                          <i class="fa-solid fa-lock"></i> Block
                        </button>
                      <?php else: ?>
                        <button class="btn-action btn-unlock" type="button"
                          data-account-id="<?= $acc['account_id'] ?>" data-action="unlock">
                          <i class="fa-solid fa-lock-open"></i> Open
                        </button>
                      <?php endif; ?>

                      <button type="submit" name="btnSave" class="btn-save-role">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu
                      </button>
                  </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
            </table>
          </div>
        </div>
      </fieldset>
    </div>
  </div>

  <form id="status-action-form" method="POST" style="display: none;">
    <input type="hidden" name="account_id" id="action-account-id">
    <input type="hidden" name="action_type" id="action-type">
    <button type="submit" name="btnStatus" id="submit-status-action"></button>
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const actionForm = document.getElementById('status-action-form');
      const accountIdInput = document.getElementById('action-account-id');
      const actionTypeInput = document.getElementById('action-type');
      const submitButton = document.getElementById('submit-status-action');

      document.querySelectorAll('.btn-lock, .btn-unlock').forEach(button => {
        button.addEventListener('click', function() {
          const accountId = this.getAttribute('data-account-id');
          const action = this.getAttribute('data-action');
          const actionText = (action === 'lock') ? 'khóa (Block)' : 'mở khóa (Open)';
          const confirmMessage = `Bạn có chắc chắn muốn ${actionText} tài khoản có ID = ${accountId} không?`;
          if (confirm(confirmMessage)) {
            accountIdInput.value = accountId;
            actionTypeInput.value = action;
            submitButton.click();
          }
        });
      });
    });
  </script>
</body>

</html>