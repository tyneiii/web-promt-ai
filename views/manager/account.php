<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý tài khoản</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/slidebar.css">
</head>
<body>
  <div class="container">
    <?php include_once __DIR__ . '/layout/slidebar.php'; ?>
    <div class="main">
        <?php
          $accounts = [
              ['id' => 1, 'name' => 'Nguyễn Văn A', 'email' => 'a@gmail.com', 'type' => 'admin', 'password' => '123456'],
              ['id' => 2, 'name' => 'Trần Thị B', 'email' => 'b@gmail.com', 'type' => 'user', 'password' => '123456'],
              ['id' => 3, 'name' => 'Lê Văn C', 'email' => 'c@gmail.com', 'type' => 'admin', 'password' => '123456'],
              ['id' => 4, 'name' => 'Phạm Thị D', 'email' => 'd@gmail.com', 'type' => 'user', 'password' => '123456'],
          ];

          $search = $_GET['search'] ?? '';
          $selectedType= $_GET["type"]??'';
          $filteredAccounts = array_filter($accounts, function($a) use ($selectedType, $search) {
              $matchType = $selectedType ? $a['type']=== $selectedType : true;
              $matchSearch = $search ? (stripos($a['name'], $search)!==false || stripos($a['email'], $search)!==false) : true;
              return $matchType && $matchSearch;
          });
        ?>
      <fieldset class="account-fieldset">
          <legend>Quản lý tài khoản</legend>
        <div class="top-bar">
          <div class="stats">
            Tổng số tài khoản: <strong><?= count($filteredAccounts) ?></strong>
          </div>
          <div class="search-box" style="display: flex; gap: 10px; align-items: center;">
            <form method="get" style="display: flex; gap: 10px; align-items: center;">
              
              <input type="text" name="search" title="Tìm kiếm theo tên tài khoản hoặc email" placeholder="Tìm kiếm tài khoản..." 
                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

              <select name="type">
                <option value="">Tất cả</option>
                <option value="admin" <?= ($selectedType==='admin')?'selected':'' ?>>Admin</option>
                <option value="user" <?= ($selectedType==='user')?'selected':'' ?>>User</option>
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
                  <th>Mật khẩu</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($filteredAccounts as $index => $acc): ?>
                  <tr style="background-color: <?= $index % 2 === 0 ? '#ffffffff' : '#dcdbdbff' ?>;">
                    <td><?= $acc["id"] ?></td>
                    <td><?= htmlspecialchars($acc["name"]) ?></td>
                    <td><?= htmlspecialchars($acc["email"]) ?></td>
                    <td style="text-transform: capitalize;"><?= $acc["type"] ?></td>
                    <td><?= htmlspecialchars($acc["password"]) ?></td>
                    <td class="actions">
                      <button class="btn-edit"><i class="fa-solid fa-magnifying-glass"></i> Kiểm tra</button>
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
