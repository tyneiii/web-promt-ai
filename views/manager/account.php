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
      <?php include_once __DIR__ . '/layout/sidebar.php'; ?>
      <div class="main">
        <?php
        if (isset($_POST["btnSave"])) {
          $account_id = (int)$_POST["account_id"];
          $new_role = (int)$_POST["new_role"];
          $mess = changeRole($conn, $account_id, $new_role);
        }
        $search = $_GET['search'] ?? '';
        $role = $_GET["role"] ?? '';
        $search_columns = $_GET['search_columns'] ?? [];
        $accounts = getAccounts($conn, $search, $role, $search_columns);
        ?>
        <fieldset class="account-fieldset">
          <legend>Quản lý tài khoản</legend>
          <div class="top-bar">
            <div class="stats">
              Tổng số tài khoản: <strong><?= $accounts->num_rows ?></strong>
            </div>
            <?php if (!empty($mess)): ?>
              <div class="alert-success">
                <?= htmlspecialchars($mess) ?>
              </div>
            <?php endif; ?>
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
              </form>
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
                      <form method="POST" action="" class="role-update-form">
                        <td>
                          <select class="role-select" name="new_role" data-original-role="<?= $acc["role_name"] ?>">
                            <option value="1" <?= ($acc['role_name'] === 'Admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="2" <?= ($acc['role_name'] === 'User') ? 'selected' : '' ?>>User</option>
                          </select>
                        </td>
                        <td class="actions">
                          <input type="hidden" name="account_id" value="<?= $acc['account_id'] ?>">
                          <input type="hidden" class="hidden-role-input" name="role" value="<?= $acc['role_name'] ?>">

                          <button class="btn-delete" type="button"><i class="fa-solid fa-trash"></i> Xóa</button>

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
  </body>

  </html>