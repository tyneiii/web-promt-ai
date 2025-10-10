<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prompt AI Share</title>
  <link rel="stylesheet" href="/web-promt-ai/public/css/home.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <nav class="navbar">
    <div class="navbar-left">
      <a href="home.php" class="logo" style="text-decoration: none;" title="Trang chủ">Prompt AI</a>
    </div>
    <div class="navbar-center">
      <input type="text" class="search-bar" title="Tìm kiếm" placeholder="Tìm kiếm prompt...">
    </div>
    <div class="navbar-right">
      <select>
        <option>Chủ đề</option>
        <option>AI Chat</option>
        <option>Image</option>
        <option>Code</option>
      </select>
      <i class="fa-regular fa-bell icon"></i>
      <div class="dropdown">
        <button class="dropbtn"></button>
        <div class="dropdown-content">
          <form action="../user/profile.php" method="post">
            <input type="submit" value="Xem trang cá nhân">
          </form>
          <form action="../user/logout.php" method="post">
            <input type="submit" value="Thoát">
          </form>
        </div>
    </div>
   <div>
      <form action="../manager/QlyTaiKhoan.php" method="post">
        <button type="submit" title="Đến trang quản lý" class="gear-btn">
          <i class="fa-solid fa-gears"></i>
        </button>
      </form>
    </div>
  </nav>
</body>
</html>
