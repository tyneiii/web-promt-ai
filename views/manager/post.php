<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bài đăng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/manager/post.css">
    <link rel="stylesheet" href="../../public/css/manager/sidebar.css">

</head>

<body>
    <div class="container">
        <?php
        include_once __DIR__ . '/layout/sidebar.php';
        ?>
        <div class="main">
            <?php
            include_once __DIR__ . '/../../helpers/helper.php';
            include_once __DIR__ . '/../../config.php';
            include_once __DIR__ . '/../../Controller/user/report.php';
            include_once __DIR__ . '/../../helpers/manager_prompt_logic.php';

            // Đếm bài viết đang chờ duyệt
            $waiting_count = $conn->query(" SELECT COUNT(*) AS total FROM prompt WHERE status = 'waiting' ")->fetch_assoc()['total'];

            // Đếm bài bị báo cáo
            $report_count = $conn->query(" SELECT COUNT(*) AS total FROM prompt WHERE status = 'report' ")->fetch_assoc()['total'];

            function getReportCount($conn, $prompt_id, $status)
            {
                if (strtolower($status) === 'report') {
                    return getReportOfPrompt($conn, $prompt_id);
                }
                return "";
            }
            ?>
            <fieldset class="account-fieldset">
                <legend>Quản lý bài đăng</legend>
                <div class="top-bar">
                    <div class="stats">
                        Tổng số bài đăng: <strong><?= (($current_page - 1) * $rows_per_page + $posts->num_rows) . '/' . $total_rows ?></strong>
                    </div>
                    <div class="search-box">
                        <form method="get" id="search-form">
                            <?php foreach ($search_columns as $col_name): ?>
                                <input type="hidden" name="search_columns[]" value="<?= htmlspecialchars($col_name) ?>">
                            <?php endforeach; ?>

                            <div class="search-group-styled">
                                <input type="text" name="search" class="search-bar-styled"
                                    title="Tìm kiếm theo tiêu đề, mô tả hoặc ID" placeholder="Tìm kiếm bài đăng..."
                                    value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="search-btn-styled">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                            <select name="status" onchange="document.getElementById('search-form').submit()">
                                <option value="">Tất cả bài viết</option>
                                <option value="public" <?= ($selectedStatus === 'public') ? 'selected' : '' ?>>Public</option>
                                <option value="waiting" <?= ($selectedStatus === 'waiting') ? 'selected' : '' ?>>Waiting</option>
                                <option value="report" <?= ($selectedStatus === 'report') ? 'selected' : '' ?>>Report</option>
                                <option value="reject" <?= ($selectedStatus === 'reject') ? 'selected' : '' ?>>Reject</option>
                            </select>
                            <select name="rows_per_page" onchange="document.getElementById('search-form').submit()">
                                <option value="">Số hàng 1 trang</option>
                                <option value="25" <?= (string)$rows_per_page === '25' ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= (string)$rows_per_page === '50' ? 'selected' : '' ?>>50</option>
                                <option value="75" <?= (string)$rows_per_page === '75' ? 'selected' : '' ?>>75</option>
                                <option value="100" <?= (string)$rows_per_page === '100' ? 'selected' : '' ?>>100</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="table-wrapper">
                    <div class="table-container">
                        <form method="get" id="column-search-form">
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus) ?>">
                            <table>
                                <thead>
                                    <tr>
                                        <th>
                                            <label>
                                                <input type="checkbox" name="search_columns[]" value="prompt_id"
                                                    <?= in_array('prompt_id', $search_columns) ? 'checked' : '' ?>
                                                    onchange="document.getElementById('column-search-form').submit()"> ID
                                            </label>
                                        </th>
                                        <th>
                                            <label>
                                                <input type="checkbox" name="search_columns[]" value="title"
                                                    <?= in_array('title', $search_columns) ? 'checked' : '' ?>
                                                    onchange="document.getElementById('column-search-form').submit()"> Title
                                            </label>
                                        </th>
                                        <th>
                                            <label>
                                                <input type="checkbox" name="search_columns[]" value="short_description"
                                                    <?= in_array('short_description', $search_columns) ? 'checked' : '' ?>
                                                    onchange="document.getElementById('column-search-form').submit()"> Short Description
                                            </label>
                                        </th>
                                        <th>Status</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                        </form>
                        <tbody>
                            <?php while ($post = $posts->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $post['prompt_id'] ?></td>
                                    <td><?= $post['title'] ?></td>
                                    <td><?= $post['short_description'] ?></td>
                                    <td>
                                        <span class="status-<?= strtolower($post['status']) ?>"><?= ucfirst($post['status']) ?></span>
                                        <sup style="font-weight: bold; color:red;"><?= getReportCount($conn, $post['prompt_id'], $post['status']); ?></sup>
                                    </td>
                                    <td class="actions">
                                        <input type="hidden" name="prompt_id" value="<?= $post['prompt_id'] ?>">
                                        <a class="btn-edit" href="prompt_detail.php?id=<?= $post['prompt_id'] ?>">
                                            <i class="fa-solid fa-magnifying-glass"></i> Xem chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        </table>
                    </div>
                </div>
                <?php echo renderPagination($current_page, $total_pages, $rows_per_page, $pagination_params); ?>
            </fieldset>
        </div>
    </div>
</body>

</html>