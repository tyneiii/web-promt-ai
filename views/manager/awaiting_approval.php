<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bài đăng chờ duyệt</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/sidebar.css">
</head>

<body>
  <div class="container">
    <?php include_once __DIR__ . '/layout/sidebar.php'; ?>
    <div class="main">
      <?php
          $posts = [
    [   'prompt_id' => 101,
        'account_id' => 1,
        'title' => 'Giải thích API đơn giản',
        'status' => 'waiting',
        'created_' => '2025-10-01',
        'component_name' => 'api_explainer',
        'content' => 'API là giao diện cho phép các ứng dụng giao tiếp với nhau.'],
    [ 'prompt_id' => 102,
        'account_id' => 2,
        'title' => 'Caption TikTok vui về học code',
        'status' => 'waiting',
        'created_' => '2025-10-02',
        'component_name' => 'tiktok_caption',
        'content' => 'Học code không khó, khó là không biết bug ở đâu 😂'],
    [   'prompt_id' => 103,
        'account_id' => 1,
        'title' => 'Blog 300 từ về động lực học lập trình',
        'status' => 'waiting',
        'created_' => '2025-10-03',
        'component_name' => 'blog_post',
        'content' => 'Lập trình là hành trình khám phá logic và sáng tạo không giới hạn.'],
    [   'prompt_id' => 104,
        'account_id' => 3,
        'title' => 'Poster game hành động nhân vật áo giáp',
        'status' => 'waiting',
        'created_' => '2025-10-04',
        'component_name' => 'game_poster',
        'content' => 'Thiết kế poster game với tông màu tối, nhân vật mặc áo giáp thép.'],];
        
        $search = $_GET['search'] ?? '';
        $selectedStatus = $_GET['status'] ?? '';

        $filteredPosts = array_filter($posts, function($p) use ($search, $selectedStatus) {
            $matchStatus = $selectedStatus ? $p['status'] === $selectedStatus : true;
            $matchSearch = $search ? (stripos($p['prompt_id'], $search)!==false) : true;
            return $matchStatus && $matchSearch;
        });
        ?>
      <fieldset class="account-fieldset">
    <legend>Bài đăng chờ duyệt</legend>
        <div class="top-bar">
            <div class="stats">
                Tổng số bài đăng: <strong><?= count($filteredPosts) ?></strong>
            </div>
            <div class="search-box" style="display: flex; gap: 10px; align-items: center;">
                <form method="get" style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="search" title="Tìm kiếm theo tiêu đề" placeholder="Tìm kiếm bài đăng..." 
                          value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <select name="status">
                        <option value="">Tất cả</option>
                        <option value="approved" <?= ($selectedStatus==='approved')?'selected':'' ?>>Approved</option>
                        <option value="pending" <?= ($selectedStatus==='pending')?'selected':'' ?>>Pending</option>
                        <option value="reported" <?= ($selectedStatus==='reported')?'selected':'' ?>>Reported</option>
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
                            <th>Prompt ID</th>
                            <th>Account ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($filteredPosts as $index => $post): ?>
                        <tr style="background-color: <?= $index % 2 === 0 ? '#ffffffff' : '#dcdbdbff' ?>;">
                            <td><?= $post['prompt_id'] ?></td>
                            <td><?= $post['account_id'] ?></td>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td style="text-transform: capitalize; color:red"><?= $post['status'] ?></td>
                            <td><?= (new DateTime($post['created_']))->format('d/m/Y') ?></td>
                            <td class="actions">
                                <a href="check_post.php?id=<?= $post['prompt_id'] ?>" class="btn-edit"><i class="fa-solid fa-magnifying-glass"></i> Kiểm tra</a>
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