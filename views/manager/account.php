<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý tài khoản</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/manager/sidebar.css">
  <style>
    .role-tag {
      font-weight: bold;
    }

    .admin-tag {
      color: #ff5252;
    }

    .user-tag {
      color: #ccc;
    }

    /* CSS mới cho label của cột */
    .table-container th label {
      display: flex;
      align-items: center;
      cursor: pointer;
      font-weight: bold;
      /* Để tên cột không bị mờ */
    }

    .table-container th input[type="checkbox"] {
      margin-right: 5px;
    }
  </style>
</head>

<body>
  <div class="container">
    <?php include_once __DIR__ . '/layout/sidebar.php'; ?>
    <div class="main">
      <?php
      $search = $_GET['search'] ?? '';
      $role = $_GET["role"] ?? '';
      $search_columns = $_GET['search_columns'] ?? [];
      $accounts = getAccounts($conn, $search, $role, $search_columns);
      function roleCSS($roleName)
      {
        if ($roleName === 'Admin') {
          return 'role-tag admin-tag';
        }

        if ($roleName === 'User') {
          return 'role-tag user-tag';
        }
        return '';
      }
      ?>
      <fieldset class="account-fieldset">
        <legend>Quản lý tài khoản</legend>
        <div class="top-bar">
          <div class="stats">
            Tổng số tài khoản: <strong><?= $accounts->num_rows ?></strong>
          </div>
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

              <select name="role">
                <option value="">Loại tài khoản</option>
                <option value="Admin" <?= ($role === 'Admin') ? 'selected' : '' ?>>Admin</option>
                <option value="User" <?= ($role === 'User') ? 'selected' : '' ?>>User</option>
              </select>
            </form>
          </div>
        </div>
        <div class="table-wrapper">
          <div class="table-container">
            <form method="get" id="column-search-form">
              <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
              <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
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
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($acc = $accounts->fetch_assoc()): ?>
                    <tr>
                      <td><?= $acc["account_id"] ?></td>
                      <td><?= htmlspecialchars($acc["username"]) ?></td>
                      <td><?= htmlspecialchars($acc["email"]) ?></td>
                      <td>
                        <span class="<?= roleCSS($acc['role_name']) ?>">
                          <?= $acc["role_name"] ?>
                        </span>
                      </td>
                      <td class="actions">
                        <button class="btn-edit"><i class="fa-solid fa-magnifying-glass"></i> Kiểm tra</button>
                        <button class="btn-delete"><i class="fa-solid fa-trash"></i> Xóa</button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </form>
          </div>
        </div>
      </fieldset>
    </div>
  </div>
</body>

</html>