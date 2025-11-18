<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài chờ duyệt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    
    <div class="container">
        <?php 
            include_once __DIR__ . '/layout/sidebar.php'; 
            // ================= XỬ LÝ DUYỆT BÀI =================
            if (isset($_POST['approve_id'])) {
                $id = (int)$_POST['approve_id'];
                $conn->query("UPDATE prompt SET status = 'public' WHERE prompt_id = $id");
                header("Location: ".$_SERVER['PHP_SELF']);
                exit;
            }

            // ================= XỬ LÝ XÓA BÀI =====================
            if (isset($_POST['delete_id'])) {
                $id = (int)$_POST['delete_id'];

                // Xóa prompt → nên xóa các bảng liên quan trước
                $conn->query("DELETE FROM promptdetail WHERE prompt_id = $id");
                $conn->query("DELETE FROM prompttag WHERE prompt_id = $id");
                $conn->query("DELETE FROM love WHERE prompt_id = $id");
                $conn->query("DELETE FROM save WHERE prompt_id = $id");
                $conn->query("DELETE FROM report WHERE prompt_id = $id");

                // Cuối cùng xóa prompt
                $conn->query("DELETE FROM prompt WHERE prompt_id = $id");

                header("Location: ".$_SERVER['PHP_SELF']);
                exit;
            }

            $search = $_GET['search'] ?? '';
            $posts = getAwaitingPrompts($conn,$search);
        ?>
        <div class="main">
            <fieldset class="account-fieldset">
                <legend>Bài chờ duyệt</legend>
                <div class="top-bar">
                    <div class="stats">
                        Tổng số bài đăng: <strong><?= $posts->num_rows ?></strong>
                    </div>
                    <div class="search-box">
                        <form method="get">
                            <div class="search-group-styled">
                                <input type="text" name="search" class="search-bar-styled"
                                    title="Tìm kiếm theo tiêu đề" placeholder="Tìm kiếm bài đăng..."
                                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                <button type="submit" class="search-btn-styled">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-wrapper">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Prompt ID</th>
                                    <th>Tiêu đề</th>
                                    <th>Mô tả</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($post=$posts->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $post['prompt_id'] ?></td>
                                        <td><?= $post['title'] ?></td>
                                        <td><?= $post['short_description'] ?></td>
                                        <td style="text-transform: capitalize; color:#ffc107; font-weight: bold;"><?= $post['status'] ?></td>
                                        <td class="actions">
                                            <!-- NÚT DUYỆT -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="approve_id" value="<?= $post['prompt_id'] ?>">
                                                <button class="btn-edit" onclick="return confirm('Duyệt bài này?');">
                                                    <i class="fa-solid fa-circle-check"></i> Duyệt
                                                </button>
                                            </form>

                                            <!-- NÚT XÓA -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="delete_id" value="<?= $post['prompt_id'] ?>">
                                                <button class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa prompt này?');">
                                                    <i class="fa-solid fa-trash"></i> Xóa
                                                </button>
                                            </form>
                                        </td>
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