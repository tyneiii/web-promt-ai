<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prompt AI Share</title>
  <link rel="stylesheet" href="../../public/css/home.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      padding-top: 80px !important; 
    }

    @media (max-width: 768px) {
      body {
        padding-top: 160px !important; 
      }
    }
    #sticky-ad-banner {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;

      background-color: transparent;

      text-align: center;
      z-index: 1000;
    }

    #ad-wrapper,
    .ad-wrapper {
      position: relative;
      display: inline-block;
    }

    #sticky-ad-banner img {
      height: 100px;
      width: 850px;
      max-width: 200vw;
      border-radius: 6px;
    }

    #close-ad-btn {
      position: absolute;
      top: 5px;
      right: 5px;
      font-size: 22px;
      font-weight: bold;
      background: rgba(0, 0, 0, 0.4);
      color: white;
      padding: 3px 7px;
      border-radius: 50%;
      cursor: pointer;
      z-index: 10;
    }

    #close-ad-btn:hover {
      color: #fff;
    }
  </style>
</head>

<body>
  <?php
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
  if (isset($_POST['out-btn'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }
    session_destroy();
    header("Location: home.php");
    exit();
  }
  include_once __DIR__ . '/../../../config.php';
  ?>
  <nav class="navbar">
    <div class="navbar-left">
      <a href="home.php" class="logo" style="text-decoration: none;" title="Trang chủ">Prompt AI</a>
    </div>
    <div class="navbar-center">
      <form action="home.php" method="get" class="navbar-center search-group">
        <input type="text" name="search" class="search-bar"
          placeholder="Tìm kiếm prompt..."
          value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit" class="search-btn">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>
    </div>
    <div class="navbar-right">
      <select>
        <option>Chủ đề</option>
        <option>AI Chat</option>
        <option>Image</option>
        <option>Code</option>
      </select>
      <?php if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])): ?>
        <i class="fa-regular fa-bell icon"></i>
        <div class="dropdown">
          <button class="dropbtn">
            <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>"
              alt="Ảnh đại diện"
              class="avatar-image">
          </button>
          <div class="dropdown-content">
            <form action="../user/profile.php" method="post">
              <input type="submit" value="Xem trang cá nhân">
            </form>
            <form action="" method="post">
              <input type="submit" name="out-btn" value="Thoát">
            </form>
          </div>
        </div>
    </div>
  <?php else: ?>
    <a href="../../views/login/login.php" class="login-btn"><i class="fa-solid fa-right-to-bracket"></i> Đăng nhập</a>
  <?php endif; ?>
  <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1):?>
    <div>
      <form action="../manager/account.php" method="post">
        <button type="submit" title="Đến trang quản lý" class="gear-btn">
          <i class="fa-solid fa-gears"></i>
        </button>
      </form>
    </div>
  <?php endif; ?>
  </nav>
</body>

</html>