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

    <li class="menu-parent <?= isParentActive(['post.php','awaiting_approval.php','report.php']) ?>">
        <span class="menu-parent-title">
            <i class="fa-solid fa-file-lines"></i>
            <span>Quản lý bài đăng</span>
        </span>
        <ul class="submenu">
            <li>
                <a href="../manager/post.php" class="menu-link <?= isActive('QlyBaiDang.php') ?>">
                    <i class="fa-solid fa-pager"></i>Bài đăng
                </a>
            </li>
            <li>
                <a href="../manager/awaiting_approval.php" class="menu-link <?= isActive('baidang_choduyet.php') ?>">
                    <i class="fa-solid fa-clock"></i>Bài đăng chờ duyệt
                </a>
            </li>
            <li>
                <a href="../manager/report.php" class="menu-link <?= isActive('baidang_baocao.php') ?>">
                    <i class="fa-solid fa-triangle-exclamation"></i>Bài đăng bị báo cáo
                </a>
            </li>
        </ul>
    </li>
</ul>
</div>
