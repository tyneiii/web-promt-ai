<?php include_once __DIR__ . '/layout/header.php'; ?>
<?php
$cards = [
  [
    'user' => 'Nguyễn Mai',
    'avatar' => 'https://i.pravatar.cc/40?img=1',
    'title' => 'Viết nội dung quảng cáo',
    'description' => 'Tạo quảng cáo ngắn hấp dẫn cho sản phẩm thời trang nữ cao cấp.'
  ],
  [
    'user' => 'Trần Long',
    'avatar' => 'https://i.pravatar.cc/40?img=2',
    'title' => 'Sinh ý tưởng video TikTok',
    'description' => 'Tạo 5 ý tưởng video ngắn về review công nghệ cho người mới bắt đầu.'
  ],
  [
    'user' => 'Lê Anh',
    'avatar' => 'https://i.pravatar.cc/40?img=3',
    'title' => 'Viết caption mạng xã hội',
    'description' => 'Tạo caption hấp dẫn cho bức ảnh du lịch tại Bali.'
  ],
  [
    'user' => 'Phạm Huy',
    'avatar' => 'https://i.pravatar.cc/40?img=4',
    'title' => 'Lên kế hoạch học tập',
    'description' => 'Tạo lịch học 7 ngày cho người muốn học lập trình web từ cơ bản đến nâng cao.'
  ],
];
?>
<div class="left-sidebar">
  <i class="fa-regular fa-heart"></i>
  <a href="create_post.php" class="sidebar-btn" title="Tạo bài viết mới">
    <i class="fa-solid fa-plus"></i>
  </a>
  <i class="fa-regular fa-comment"></i>
</div>

<div class="right-sidebar">
  <div class="border-top"></div>
  <div class="border-bottom"></div>
  <h3>Bảng tin hot 🔥</h3>
  <div class="item">Prompt tạo ảnh phong cách anime</div>
  <div class="item">Prompt phân tích văn bản bằng GPT</div>
  <div class="item">Prompt viết bài SEO tự động</div>
  <div class="item">Prompt vẽ concept nhân vật fantasy</div>
  <div class="item">Prompt tạo website bằng HTML</div>
</div>

<div class="main-content">
  <?php foreach ($cards as $card): ?>
    <div class="card">
      <div class="card-header">
        <div class="user-info">
          <img src="<?= $card['avatar'] ?>" alt="<?= $card['user'] ?>" style="width:35px; height:35px; border-radius:50%;">
          <strong><?= $card['user'] ?></strong>
        </div>
        <button class="report-btn"><i class="fa-solid fa-flag"></i> Báo cáo</button>
      </div>
      <h4><?= $card['title'] ?></h4>
      <p><?= $card['description'] ?></p>
      <div class="card-buttons">
        <button><i class="fa-regular fa-heart"></i> Thích</button>
        <button><i class="fa-regular fa-comment"></i> Bình luận</button>
        <button><i class="fa-regular fa-bookmark"></i> Lưu</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include_once __DIR__ . '/layout/footer.php'; ?>