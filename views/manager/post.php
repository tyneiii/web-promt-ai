<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bài đăng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/sidebar.css">
</head>

<body>
    <div class="container">
        <?php include_once __DIR__ . '/layout/sidebar.php'; ?>
        <div class="main">
            <?php
            $posts = [
                ['prompt_id' => 101, 'account_id' => 1, 'title' => 'Giải thích API đơn giản', 'status' => 'approved', 'created_' => '2025-10-01'],
                ['prompt_id' => 102, 'account_id' => 2, 'title' => 'Caption TikTok vui về học code', 'status' => 'pending', 'created_' => '2025-10-02'],
                ['prompt_id' => 103, 'account_id' => 1, 'title' => 'Blog 300 từ về động lực học lập trình', 'status' => 'approved', 'created_' => '2025-10-03'],
                ['prompt_id' => 104, 'account_id' => 3, 'title' => 'Poster game hành động nhân vật áo giáp', 'status' => 'reported', 'created_' => '2025-10-04'],
            ];

            $search = $_GET['search'] ?? '';
            $selectedStatus = $_GET['status'] ?? '';

            $filteredPosts = array_filter($posts, function ($p) use ($search, $selectedStatus) {
                $matchStatus = $selectedStatus ? $p['status'] === $selectedStatus : true;
                $matchSearch = $search ? (stripos($p['prompt_id'], $search) !== false) : true;
                return $matchStatus && $matchSearch;
            });
            ?>
            <fieldset class="account-fieldset">
                <legend>Quản lý bài đăng</legend>
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
                                <option value="approved" <?= ($selectedStatus === 'approved') ? 'selected' : '' ?>>Approved</option>
                                <option value="pending" <?= ($selectedStatus === 'pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="reported" <?= ($selectedStatus === 'reported') ? 'selected' : '' ?>>Reported</option>
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
                                        <td style="text-transform: capitalize;"><?= $post['status'] ?></td>
                                        <td><?= (new DateTime($post['created_']))->format('d/m/Y') ?></td>
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