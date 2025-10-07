<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trang chủ</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #f9f9f9;
      display: flex;
      flex-direction: column;
      height: 100vh;
    }

    /* Navbar */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background-color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      z-index: 1000;
    }

    .navbar-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo {
      font-size: 20px;
      font-weight: bold;
      color: #007bff;
    }

    .search-bar {
      width: 300px;
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 20px;
      outline: none;
    }

    .navbar-right {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    select {
      padding: 6px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      background-color: white;
    }

    .icon {
      font-size: 20px;
      cursor: pointer;
      color: #555;
      transition: 0.3s;
    }

    .icon:hover {
      color: #007bff;
    }

    .avatar {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      cursor: pointer;
      object-fit: cover;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 60px;
      left: 0;
      width: 60px;
      height: calc(100% - 60px);
      background-color: white;
      border-right: 1px solid #ddd;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 20px;
      gap: 30px;
    }

    .sidebar i {
      font-size: 22px;
      cursor: pointer;
      color: #555;
      transition: 0.3s;
    }

    .sidebar i:hover {
      color: #007bff;
    }

    /* Content */
    .content {
      margin-top: 60px;
      margin-left: 60px;
      padding: 20px;
    }

  </style>
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <div class="navbar-left">
      <div class="logo">MyLogo</div>
      <input type="text" class="search-bar" placeholder="Tìm kiếm...">
    </div>
    <div class="navbar-right">
      <select>
        <option>Chủ đề</option>
        <option>AI</option>
        <option>Lập trình</option>
        <option>Thiết kế</option>
      </select>
      <i class="fa-regular fa-bell icon"></i>
      <img src="https://via.placeholder.com/35" class="avatar" alt="Avatar" onclick="window.location='profile.html'">
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar">
    <i class="fa-regular fa-heart"></i>
    <i class="fa-solid fa-plus"></i>
    <i class="fa-regular fa-comment"></i>
  </div>

  <!-- Content -->
  <div class="content">
    <h1>Trang chủ</h1>
    <p>Đây là nội dung trang chính. Bạn có thể thêm bài viết, bài đăng hoặc nội dung động tại đây.</p>
  </div>
</body>
</html>
