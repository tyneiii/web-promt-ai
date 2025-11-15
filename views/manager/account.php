<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý tài khoản</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/manager/sidebar.css">
  <?php include_once __DIR__ . '/../../Controller/account.php'; ?>
  <style>
    .table-container table td:nth-child(4) {
      vertical-align: middle;
    }

    .role-tag {
      font-weight: bold;
    }

    .admin-tag {
      color: blue;
    }

    .user-tag {
      color:black ;
    }
  </style>
</head>

<body>
  <div class="container">
    <?php include_once __DIR__ . '/layout/sidebar.php'; ?>
    <div class="main">
      <?php
      $search = $_GET['search'] ?? '';
      $role = $_GET["type"] ?? '';
      $accounts = getAccounts($conn, $search, $role);
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
          <div class="search-box" style="display: flex; gap: 10px; align-items: center;">
            <form method="get" style="display: flex; gap: 10px; align-items: center;">

              <input type="text" name="search" title="Tìm kiếm theo tên tài khoản hoặc email" placeholder="Tìm kiếm tài khoản..."
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

              <select name="type">
                <option value="">Tất cả</option>
                <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Admin</option>
                <option value="user" <?= ($role === 'user') ? 'selected' : '' ?>>User</option>
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
                  <th>ID</th>
                  <th>Tên tài khoản</th>
                  <th>Email</th>
                  <th>Loại tài khoản</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php $index = 0;
                while ($acc = $accounts->fetch_assoc()): ?>
                  <tr style="background-color: <?= $index % 2 === 0 ? '#ffffffff' : '#dcdbdbff' ?>;">
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