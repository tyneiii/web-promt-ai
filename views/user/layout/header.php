<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prompt AI Share</title>
  <link rel="stylesheet" href="../../public/css/home.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    #sticky-ad-banner {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;

      background-color: transparent;

      text-align: center;
      padding: 5px 0;
      z-index: 1000;
    }

    #sticky-ad-banner img {
      height: 100px;
      width: 1500px;
      max-width: 50%;
      vertical-align: middle;
    }

    #close-ad-btn {
      position: absolute;
      top: 0;
      right: 15px;
      font-size: 24px;
      font-weight: bold;
      color: #888;
      cursor: pointer;
      line-height: 1;
      padding: 5px;
    }

    #close-ad-btn:hover {
      color: #fff;
    }
  </style>
</head>

<body>
  <?php
  session_start();
  ?>
  <nav class="navbar">
    <div class="navbar-left">
      <a href="home.php" class="logo" style="text-decoration: none;" title="Trang chủ">Prompt AI</a>
    </div>
    <div class="navbar-center">
      <form action="home.php" method="get" class="navbar-center">
        <input type="text" name="search" class="search-bar" title="Tìm kiếm" placeholder="Tìm kiếm prompt..."
          value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      </form>
    </div>
    <div class="navbar-right">
      <select>
        <option>Chủ đề</option>
        <option>AI Chat</option>
        <option>Image</option>
        <option>Code</option>
      </select>
      <i class="fa-regular fa-bell icon"></i>
      <?php if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])): ?>
        <div class="dropdown">
          <button class="dropbtn">
            <?php echo htmlspecialchars($_SESSION['name_user']); ?>
          </button>
          <div class="dropdown-content">
            <form action="../user/profile.php" method="post">
              <input type="submit" value="Xem trang cá nhân">
            </form>
            <form action="../../views/login/logout.php" method="post">
              <input type="submit" value="Thoát">
            </form>
          </div>
        </div>
      <?php else: ?>
        <a href="../../views/login/login.php" class="login-btn">Đăng nhập</a>
      <?php endif; ?>
      <div>
        <form action="../manager/account.php" method="post">
          <button type="submit" title="Đến trang quản lý" class="gear-btn">
            <i class="fa-solid fa-gears"></i>
          </button>
        </form>
      </div>
  </nav>
</body>

</html>