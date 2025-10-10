
<?php include_once __DIR__ . '/../layout/header.php'; ?>
<!-- Sidebar trái -->
<div class="left-sidebar">
  <i class="fa-regular fa-heart"></i>
  <a href="create_post.php" class="sidebar-btn" title="Tạo bài viết mới">
    <i class="fa-solid fa-plus"></i>
  </a>
  <i class="fa-regular fa-comment"></i>
</div>


<!-- Sidebar phải -->
<div class="right-sidebar">
  <h3>Bảng tin hot 🔥</h3>
  <div class="item">Prompt tạo ảnh phong cách anime</div>
  <div class="item">Prompt phân tích văn bản bằng GPT</div>
  <div class="item">Prompt viết bài SEO tự động</div>
  <div class="item">Prompt vẽ concept nhân vật fantasy</div>
  <div class="item">Prompt tạo website bằng HTML</div>
</div>

<!-- Nội dung chính -->
<div class="main-content">
  <div class="card">
    <div class="card-header">
      <h4>Prompt: Viết nội dung quảng cáo</h4>
      <button class="report-btn"><i class="fa-solid fa-flag"></i> Báo cáo</button>
    </div>
    <p>Tạo quảng cáo ngắn hấp dẫn cho sản phẩm thời trang nữ cao cấp.</p>
    <div class="card-buttons">
      <button><i class="fa-regular fa-heart"></i> Thích</button>
      <button><i class="fa-regular fa-comment"></i> Bình luận</button>
      <button><i class="fa-regular fa-bookmark"></i> Lưu</button>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h4>Prompt: Sinh ý tưởng video TikTok</h4>
      <button class="report-btn"><i class="fa-solid fa-flag"></i> Báo cáo</button>
    </div>
    <p>Tạo 5 ý tưởng video ngắn về review công nghệ cho người mới bắt đầu.</p>
    <div class="card-buttons">
      <button><i class="fa-regular fa-heart"></i> Thích</button>
      <button><i class="fa-regular fa-comment"></i> Bình luận</button>
      <button><i class="fa-regular fa-bookmark"></i> Lưu</button>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
