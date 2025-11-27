<link rel="stylesheet" href="../../public/css/manager/sidebar.css">
<?php include_once __DIR__ . '/../../../config.php' ?>
<?php include_once __DIR__ . '/../../../Controller/user/prompt.php'; ?>
<?php include_once __DIR__ . '/../../../Controller/account.php'; ?>

<div class="sidebar">
  <h2>
    <form action="../user/home.php">
      <input type="submit" value="Về trang chủ">
    </form>
  </h2>
<?php
  $currentPage = basename($_SERVER['PHP_SELF']); 
  function isActive($page) {
      global $currentPage;
      return $currentPage === $page ? 'active' : '';
  }
  function isParentActive($subPages) {
      global $currentPage;
      return in_array($currentPage, $subPages) ? 'active' : '';
  }
?>
<ul>
    <li>
        <a href="../manager/account.php" class="menu-link <?= isActive('account.php') ?>">
            <i class="fa-solid fa-users"></i>Quản lý tài khoản
        </a>
    </li>
    <li>
        <a href="../manager/post.php" class="menu-link <?= isActive('post.php') ?>">
            <i class="fa-solid fa-file-lines"></i>Quản lý bài đăng
        </a>
    </li>
    <li>
        <a href="../manager/revenue.php" class="menu-link <?= isActive('revenue.php') ?>">
            <i class="fa-solid fa-file-lines"></i>Doanh thu
        </a>
    </li>
</ul>
</div>
