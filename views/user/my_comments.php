<?php
session_start();
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../controller/user/prompt.php';

if (!isset($_SESSION['account_id'])) {
    header("Location: ../login/login.php");
    exit;
}

$account_id = $_SESSION['account_id'];
$comments = getUserComments($account_id, $conn);  // Lấy list comments (có author info)
?>

<link rel="stylesheet" href="../../public/css/my_comment.css">

<div class="my-comments-container">
    <div class="page-header">
        <h2><i class="fa-solid fa-comments"></i> Danh sách bình luận của bạn</h2>
        <a href="home.php" class="back-btn" title="Quay về trang chủ">
            <i class="fa-solid fa-arrow-left"></i> Trang chủ
        </a>
    </div>

    <?php if (empty($comments)): ?>
        <div class="empty-state">
            <i class="fa-regular fa-comment-slash" style="font-size: 48px; color: #666; margin-bottom: 10px;"></i>
            <p>Bạn chưa có bình luận nào. Hãy bắt đầu thảo luận trên các bài viết nhé!</p>
            <a href="home.php" class="back-btn">Quay về trang chủ</a>
        </div>
    <?php else: ?>
        <div class="comments-grid">
            <?php foreach ($comments as $cmt): ?>
                <div class="comment-item-full">
                    <div class="comment-header">
                        <!-- Avatar của người bình luận -->
                        <img src="../../public/img/<?= htmlspecialchars($cmt['avatar']) ?>"
                            alt="<?= htmlspecialchars($cmt['username']) ?>" class="comment-avatar">
                        <div class="user-info">
                            <strong>Bình luận của bạn: <?= htmlspecialchars($cmt['username']) ?></strong>
                            <span class="comment-date"><?= date('d/m/Y H:i', strtotime($cmt['created_at'])) ?></span>
                            <br>
                            <em>Dưới bài viết của: <?= htmlspecialchars($cmt['author_username']) ?></em> <!-- THÊM TÊN AUTHOR -->
                        </div>
                    </div>

                    <div class="comment-title">
                        <i class="fa-solid fa-quote-left"></i>
                        <a href="detail_post.php?id=<?= $cmt['prompt_id'] ?>">
                            <?= htmlspecialchars($cmt['title'] ?: 'Bài viết không có tiêu đề') ?>
                        </a>
                    </div>

                    <div class="comment-content">
                        <?= nl2br(htmlspecialchars($cmt['content'])) ?>
                    </div>

                    <details class="comment-menu">
                        <summary title="Tùy chọn">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </summary>
                        <ul>
                            <li class="view-option" onclick="viewDetail(<?= $cmt['prompt_id'] ?>)">
                                <i class="fa-solid fa-eye"></i> Xem chi tiết bài đăng
                            </li>
                            <li class="delete-option" onclick="deleteComment(<?= $cmt['comment_id'] ?>, <?= $cmt['prompt_id'] ?>)">
                                <i class="fa-solid fa-trash"></i> Xóa bình luận
                            </li>
                        </ul>
                    </details>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="page-footer">
            <p>Tổng số bình luận: <?= count($comments) ?></p>
        </div>
    <?php endif; ?>
</div>

<script src="../../public/js/user_comments.js"></script>

<?php include_once __DIR__ . '/layout/footer.php'; ?>