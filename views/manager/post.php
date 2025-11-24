<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bài đăng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .status-tag {
            text-transform: capitalize;
            font-weight: bold;
        }

        .status-public .status-tag {
            color: #4CAF50;
        }

        .status-waiting .status-tag {
            color: #ffc107;
        }

        .status-reported .status-tag {
            color: #f44336;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include_once __DIR__ . '/layout/sidebar.php';
              include_once __DIR__ . '/../../helpers/helper.php'?>
        <div class="main">
            <?php
            $mess = "";
            if (isset($_POST["btnUpdateStatus"])) {
                $prompt_id = (int)$_POST["prompt_id"];
                $new_status = $_POST["new_status"];
                $mess_result = changestatus($conn, $prompt_id, $new_status);
                $query_params = array_filter([
                    'status' => $_GET['status'] ?? '',
                    'search' => $_GET['search'] ?? '',
                    'search_columns' => $_GET['search_columns'] ?? [],
                ]);
                handlePrgRedirect($mess_result, $query_params);
            }
            $mess = getMess();
            $selectedStatus = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';
            $search_columns = $_GET['search_columns'] ?? [];
            $posts = getAlldPrompts($conn, $search, $selectedStatus, $search_columns);
            function getStatusClass($status)
            {
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
                    <?php printMess($mess); ?>
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
                                <option value="report" <?= ($selectedStatus === 'report') ? 'selected' : '' ?>>Reported</option>
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
                                    <form method="POST" action="" class="status-update-form">
                                        <td class="<?= getStatusClass($post['status']) ?>">
                                            <select class="status-select" name="new_status">
                                                <option value="public" <?= ($post['status'] === 'public') ? 'selected' : '' ?>>Public</option>
                                                <option value="waiting" <?= ($post['status'] === 'waiting') ? 'selected' : '' ?>>Waiting</option>
                                                <option value="report" <?= ($post['status'] === 'report') ? 'selected' : '' ?>>Reported</option>
                                            </select>
                                        </td>
                                        <td class="actions">
                                            <input type="hidden" name="prompt_id" value="<?= $post['prompt_id'] ?>">
                                            <input type="hidden" name="btnUpdateStatus" value="<?= $post['prompt_id'] ?>"> <button class="btn-edit" type="button"><i class="fa-solid fa-magnifying-glass"></i> Xem chi tiết</button>
                                            <button class="btn-delete" type="button" value="<?= $post['prompt_id'] ?>"><i class="fa-solid fa-trash"></i> Xóa</button>
                                            <button type="submit" name="btnSave" class="btn-save-role">
                                                <i class="fa-solid fa-floppy-disk"></i> Lưu
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        </table>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</body>

</html>