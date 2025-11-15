<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bài đăng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include_once __DIR__ . '/../../Controller/user/prompt.php'; ?>
    <style>
        .status-tag{
            text-transform: capitalize;
            font-weight: bold;
        }
        .status-public .status-tag {
            color: green; 
        }
        .status-waiting .status-tag {
            color: blue; 
        }
        .status-reported .status-tag {
            color: red; 
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include_once __DIR__ . '/layout/sidebar.php'; ?>
        <div class="main">
            <?php
            $selectedStatus=$_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';
            $posts = getAlldPrompts($conn,$search, $selectedStatus);
            function getStatusClass($status) {
                switch ($status) {
                    case 'public':
                        return 'status-public';
                    case 'waiting':
                        return 'status-waiting';
                    case 'report':
                        return 'status-reported';
                    default:
                        return '';
                }
            }
            ?>
            <fieldset class="account-fieldset">
                <legend>Quản lý bài đăng</legend>
                <div class="top-bar">
                    <div class="stats">
                        Tổng số bài đăng: <strong><?= $posts->num_rows ?></strong>
                    </div>
                    <div class="search-box" style="display: flex; gap: 10px; align-items: center;">
                        <form method="get" style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" name="search" title="Tìm kiếm theo tiêu đề" placeholder="Tìm kiếm bài đăng..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

                            <select name="status">
                                <option value="">Tất cả</option>
                                <option value="public" <?= ($selectedStatus === 'public') ? 'selected' : '' ?>>Public</option>
                                <option value="waiting" <?= ($selectedStatus === 'waiting') ? 'selected' : '' ?>>Waiting</option>
                                <option value="report" <?= ($selectedStatus === 'reported') ? 'selected' : '' ?>>Report</option>
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
                                    <th>Title</th>
                                    <th>Short Description</th>
                                    <th>Status</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $index=0; while ($post = $posts->fetch_assoc()): ?>
                                    <tr style="background-color: <?= $index % 2 === 0 ? '#ffffffff' : '#dcdbdbff' ?>;">
                                        <td><?= $post['prompt_id'] ?></td>
                                        <td><?= $post['title'] ?></td>
                                        <td><?= $post['short_description'] ?></td>
                                        <td class="<?= getStatusClass($post['status']) ?>">
                                            <span class="status-tag"><?= $post['status'] ?></span>
                                        </td>
                                        <td class="actions">
                                            <button class="btn-edit"><i class="fa-solid fa-magnifying-glass"></i> Kiểm tra</button>
                                            <button class="btn-delete"><i class="fa-solid fa-trash"></i> Xóa</button>
                                        </td>
                                    </tr>
                                <?php $index++; endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</body>

</html>