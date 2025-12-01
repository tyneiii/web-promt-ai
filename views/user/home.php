<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../config.php';

$account_id = $_SESSION['account_id'] ?? '';
$name_user = $_SESSION['name_user'] ?? '';

$search = $_GET['search'] ?? '';

include_once __DIR__ . '/../../Controller/user/prompt.php';
if (isset($_POST['loveBtn']) && $account_id) {
    $id_prompt = (int)$_POST['loveBtn'];
    $mess = lovePrompt($account_id, $id_prompt, $conn);
    header("Location: " . $_SERVER['PHP_SELF'] . "?search=" . urlencode($search));
    exit;
} elseif (isset($_POST['cmtBtn']) && $account_id) {
    $id_prompt = (int)$_POST['cmtBtn'];
    $redirect = "detail_post.php?id=" . $id_prompt;
    if (!empty($search)) {
        $redirect .= "&search=" . urlencode($search);
    }
    header("Location: $redirect");
    exit;
} elseif (isset($_POST['saveBtn']) && $account_id) {
    $id_prompt = (int)$_POST['saveBtn'];
    $mess = savePrompt($account_id, $id_prompt, $conn);
    header("Location: " . $_SERVER['PHP_SELF'] . "?search=" . urlencode($search));
    exit;
}

include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../helpers/helper.php';
?>

<link rel="stylesheet" href="../../public/css/run_prompt.css">

<?php
$tag = isset($_GET['tag']) ? (int)$_GET['tag'] : 0;
$rows_per_page = 10;
$current_page = (int)($_GET['page'] ?? 1);
$offset = ($current_page - 1) * $rows_per_page;
$pagination_params = [
    'search' => $search,
    'tag' => $tag,
];
$total_rows = totalPrompts($search, $tag, $conn);
$total_pages = ceil($total_rows / $rows_per_page);
$hot_prompts = getHotPrompts($conn, 5);
$following_users = [];
if ($account_id) {
    $following_users = getFollowingUsers($account_id, $conn);
}
$prompts = getPrompts($account_id, $search, $tag, $conn, $rows_per_page, $offset);
unset($_POST);
?>

<div class="left-sidebar">
    <?php if (isset($_SESSION['account_id'])): ?>
        <a href="profile.php?id=<?= $account_id ?>&tab=favorites" title="Danh sách yêu thích" style="color:#FF4D4D">
            <i class="fa-solid fa-heart"></i>
        </a>
    <?php else: ?>
        <a href="../login/login.php?require_login=favorites" title="Đăng nhập để xem yêu thích" style="color:#FF4D4D">
            <i class="fa-solid fa-heart"></i>
        </a>
    <?php endif; ?>

    <?php if (isset($_SESSION['account_id'])): ?>
        <a href="create_post.php" class="sidebar-btn" title="Tạo bài viết mới" style="color:yellow">
            <i class="fa-solid fa-pen"></i>
        </a>
    <?php else: ?>
        <a href="../login/login.php" class="sidebar-btn" title="Đăng nhập để tạo bài viết" style="color:yellow">
            <i class="fa-solid fa-pen"></i>
        </a>
    <?php endif; ?>

    <?php if (isset($_SESSION['account_id'])): ?>
        <a href="my_comments.php" title="Danh sách bình luận của bạn" class="sidebar-btn" style="color:#4D88FF">
            <i class="fa-solid fa-comment-dots"></i>
        </a>
    <?php endif; ?>

    <?php if (isset($_SESSION['account_id']) && (($_SESSION['role'] == 2) || ($_SESSION['role'] == 3))): ?>
        <a href="chat_page.php" title="Nhắn tin với quản trị viên" class="sidebar-btn" style="color:#00FF85">
            <i class="fa-solid fa-comment-sms"></i>
        </a>
    <?php endif; ?>

    <a href="javascript:void(0)" id="btnOpenRules" title="Quy định & Hướng dẫn" class="sidebar-btn" style="color:white">
        <i class="fa-solid fa-circle-info"></i>
    </a>
</div>
<div class="box-section">
    <div class="right-sidebar">
        <h3>Bảng tin hot <i class="fa-solid fa-fire"></i></h3>
        <?php if (empty($hot_prompts)): ?>
            <div class="item">Chưa có bài viết hot nào.</div>
        <?php else: ?>
            <?php foreach ($hot_prompts as $hot): ?>
                <a href="detail_post.php?id=<?= $hot['prompt_id'] ?>" class="item-link">
                    <div class="item"><?= htmlspecialchars($hot['description']) ?></div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- BẢNG ĐANG THEO DÕI -->
    <div class="box-decor">
        <h3 class="follow-title">Đang theo dõi <i class="fa-solid fa-user-group"></i></h3>

        <div class="follow-list">

            <?php if (!isset($_SESSION['account_id'])): ?>

                <div class="item">Bạn cần đăng nhập để xem.</div>

            <?php elseif (empty($following_users)): ?>

                <div class="item">Bạn chưa theo dõi ai.</div>

            <?php else: ?>

                <?php foreach ($following_users as $user): ?>
                    <a href="profile.php?id=<?= $user['account_id'] ?>" class="item-link">
                        <div class="item">
                            <img src="<?= htmlspecialchars($user['avatar'] ?? 'default-avatar.png') ?>"
                                style="width:28px; height:28px; border-radius:50%; margin-right:8px;">
                            <?= htmlspecialchars($user['username']) ?>
                        </div>
                    </a>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</div>


<div class="main-content">
    <?php if (empty($prompts)): ?>
        <p>Không có prompt nào phù hợp. Thử tìm kiếm khác!</p>
    <?php else: ?>
        <?php foreach ($prompts as $prompt): ?>
            <form action="" method="post">
                <div class="card" data-id="<?= $prompt['prompt_id'] ?>">
                    <div class="card-header">
                        <div class="user-info">
                            <a href="profile.php?id=<?= $prompt['account_id'] ?>"
                                style="display:flex; align-items:center; gap:8px; text-decoration:none; color:inherit;">
                                <img src="<?= htmlspecialchars($prompt['avatar'] ?? 'default-avatar.png') ?>"
                                    alt="<?= htmlspecialchars($prompt['username']) ?>"
                                    style="width:35px; height:35px; border-radius:50%;">
                                <strong><?= htmlspecialchars($prompt['username']) ?></strong>
                            </a>
                        </div>
                        <button class="report-btn" type="button">
                            <i class="fa-solid fa-flag"></i> Báo cáo
                        </button>
                    </div>
                    <h4><?= htmlspecialchars($prompt['description']) ?></h4>
                    <?php if (!empty($prompt['tags'])): ?>
                        <div class="home-tags">
                            <?php foreach ($prompt['tags'] as $tag): ?>
                                <a class="tag-item" href="home.php?tag=<?= $tag['id'] ?>">
                                    #<?= htmlspecialchars($tag['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <p>
                        <?= htmlspecialchars($prompt['short_description']) ?>
                    </p>
                    <div class="card-buttons">
                        <button type="submit" name="loveBtn" id="loveBtn" title="Thích bài viết" value="<?= $prompt['prompt_id'] ?>">
                            <i class="fa-heart <?= $prompt['is_loved'] ? 'fa-solid text-red' : 'fa-regular' ?>"></i> <?= $prompt['love_count'] ?>
                        </button>
                        <button type="submit" name="cmtBtn" title="Bình luận bài viết" value="<?= $prompt['prompt_id'] ?>">
                            <i class="fa-regular fa-comment"></i> <?= $prompt['comment_count'] ?>
                        </button>
                        <button type="submit" name="saveBtn" title="Lưu bài viết" id="saveBtn" value="<?= $prompt['prompt_id'] ?>">
                            <i class="fa-regular fa-bookmark"></i> <?= $prompt['save_count'] ?>
                        </button>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
    <div id="report-modal" class="report-modal" style="display:none;">
        <div class="report-box">
            <h3>Chọn lý do báo cáo</h3>

            <select id="report-reason">
                <option value="Nội dung không phù hợp">Nội dung không phù hợp</option>
                <option value="Spam / Quảng cáo sai">Spam / Quảng cáo sai</option>
                <option value="Thông tin sai lệch">Thông tin sai lệch</option>
                <option value="Hình ảnh nhạy cảm">Hình ảnh nhạy cảm</option>
                <option value="Khác">Khác</option>
            </select>

            <textarea id="report-custom" placeholder="Nếu chọn 'Khác', hãy nhập lý do..." style="display:none; margin-top:10px;"></textarea>

            <div class="report-actions">
                <button id="cancelReport">Hủy</button>
                <button id="submitReport">Gửi báo cáo</button>
            </div>
        </div>
    </div>
</div>
<?php echo renderPagination($current_page, $total_pages, $rows_per_page, $pagination_params); ?>


<div id="rulesModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Quy định & Hướng dẫn sử dụng</h2>
            <span class="close-modal">&times;</span>
        </div>

        <div class="modal-body">
            <div class="accordion-card">
                <div class="accordion-header">
                    <h3>I. Tiêu chuẩn cộng đồng & Quy tắc đăng bài</h3>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="accordion-content">
                    <ul>
                        <span style="font-size: 1rem;">TUYỆT ĐỐI KHÔNG đăng tải các nội dung thuộc các nhóm sau:</span>
                        <li>
                            <strong>Vi phạm pháp luật và Thuần phong mỹ tục:</strong>
                            <br> - <em> Nội dung chống phá Nhà nước, vi phạm Luật An ninh mạng.
                                <br> - <em> Nội dung khiêu dâm, đồi trụy, trái với đạo đức và truyền thống văn hóa.
                                    <br> - <em> Cổ xúy tệ nạn xã hội, mê tín dị đoan.
                        </li>
                        <li>
                            <strong>Ngôn từ gây thù ghét và Bạo lực:</strong>
                            <br> - <em> Nội dung phân biệt chủng tộc, vùng miền, giới tính, tôn giáo.
                                <br> - <em> Xúc phạm danh dự, nhân phẩm của cá nhân hoặc tổ chức khác.
                                    <br> - <em> Cổ xúy bạo lực, bắt nạt trực tuyến (cyberbullying).
                        </li>
                        <li>
                            <strong>Spam & Lừa đảo:</strong>
                            <br> - <em> Prompt nhằm mục đích tạo ra mã độc, lừa đảo (scam), hoặc tấn công mạng.</ol>
                                <br> - <em> Đăng tải nội dung rác, trùng lặp liên tục, hoặc quảng cáo trái phép.</ol>
                        </li>
                        <li>
                            <strong>Bản quyền:</strong>
                            <br> - <em> Hãy tôn trọng quyền sở hữu trí tuệ.
                                <br> - <em> Không chia sẻ các nội dung có bản quyền mà không được sự cho phép (ví dụ: prompt yêu cầu tạo ra tác phẩm y hệt phong cách độc quyền của nghệ sĩ cụ thể nhằm mục đích thương mại hóa trái phép).
                        </li>
                    </ul>
                </div>
            </div>

            <div class="accordion-card">
                <div class="accordion-header">
                    <h3>II. Hướng dẫn soạn nội dung (Prompt)</h3>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="accordion-content">
                    <p>Vì một Prompt chất lượng sẽ giúp AI hiểu và trả về kết quả chính xác, chúng tôi yêu cầu bài đăng cần đáp ứng các tiêu chuẩn sau:</p>
                    <ul>
                        <li><strong>Tiêu đề:</strong> Bao quát nội dung/mục đích của Prompt, không nên đặt tiêu đề chung chung. VD: [Hành động chính] + [Đối tượng/Lĩnh vực].</li>
                        <li><strong>Mô tả:</strong> Ngắn gọn, nêu kết quả mong đợi, giúp người dùng hiểu nhanh Prompt này giải quyết vấn đề gì trước khi bấm vào xem chi tiết</li>
                        <li><strong>Nội dung chính:</strong>bạn không nên viết một câu lệnh sơ sài. Hãy tư duy theo Cấu trúc thành phần (Component-based) Nên chia thành các phần:
                            <br> - <em>Vai trò (Role)</em>
                            <br> - <em>Bối cảnh/Dữ liệu đầu vào (Context/Input)</em>
                            <br> - <em>Nhiệm vụ (Task)</em>
                            <br> - <em>Ràng buộc & Định dạng (Constraints)/Định dạng đầu ra (Output Format)</em>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="accordion-card">
                <div class="accordion-header">
                    <h3>III. Ví dụ bài đăng hợp lệ</h3>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="accordion-content">
                    <div class="example-box">
                        <strong>Tiêu đề:</strong> Tạo CV chuyên nghiệp<br>
                        <strong>Prompt:</strong> "Bạn là chuyên gia tuyển dụng. Hãy viết CV dựa trên thông tin: Tên [A], Kinh nghiệm [B]... Yêu cầu CV dài tối đa 2 trang, văn phong trang trọng."
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const isLoggedIn = <?= isset($_SESSION['account_id']) ? 'true' : 'false' ?>;
    let currentPromptId = 0;

    /* CLICK CARD → MỞ CHI TIẾT */
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('button') || e.target.closest('.run-btn')) return;
            const id = this.getAttribute('data-id');
            window.location.href = `detail_post.php?id=${id}`;
        });
    });


    /* MỞ POPUP BÁO CÁO (CÓ KIỂM TRA ĐĂNG NHẬP + RESET FORM) */
    document.querySelectorAll(".report-btn").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.stopPropagation();

            // KIỂM TRA ĐĂNG NHẬP
            if (!isLoggedIn) {
                alert("Bạn phải đăng nhập để báo cáo!");
                window.location.href = "../login/login.php?require_login=report";
                return;
            }

            const card = this.closest(".card");
            currentPromptId = card.getAttribute("data-id");

            // RESET LÝ DO MỖI LẦN MỞ POPUP
            document.getElementById("report-reason").value = "Nội dung không phù hợp";
            document.getElementById("report-custom").value = "";
            document.getElementById("report-custom").style.display = "none";

            document.getElementById("report-modal").style.display = "flex";
        });
    });


    /* SHOW/HIDE COMMENT WHEN SELECT "Khác" */
    document.getElementById("report-reason").addEventListener("change", function() {
        document.getElementById("report-custom").style.display =
            (this.value === "Khác") ? "block" : "none";
    });


    /* HỦY POPUP */
    document.getElementById("cancelReport").onclick = () => {
        document.getElementById("report-modal").style.display = "none";
    };


    /* GỬI BÁO CÁO */
    document.getElementById("submitReport").onclick = () => {
        let reason = document.getElementById("report-reason").value;

        if (reason === "Khác") {
            let custom = document.getElementById("report-custom").value.trim();
            if (!custom) {
                alert("Vui lòng nhập lý do báo cáo!");
                return;
            }
            reason = custom;
        }

        fetch("report.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "id=" + currentPromptId + "&reason=" + encodeURIComponent(reason)
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                document.getElementById("report-modal").style.display = "none";
            })
            .catch(err => {
                console.error(err);
                alert("Lỗi khi báo cáo!");
            });
    };
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Xử lý đóng mở Modal
        const modal = document.getElementById('rulesModal');
        const btnOpen = document.getElementById('btnOpenRules');
        const btnClose = rulesModal.querySelector('.close-modal');

        // Mở modal khi click icon info
        btnOpen.addEventListener('click', function() {
            modal.style.display = 'flex';
        });

        // Đóng modal khi click dấu X
        btnClose.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Đóng modal khi click ra ngoài vùng nội dung
        window.addEventListener('click', function(e) {
            if (e.target == modal) {
                modal.style.display = 'none';
            }
        });

        // 2. Xử lý Accordion (Xổ nội dung)
        const accordions = document.querySelectorAll('.accordion-header');

        accordions.forEach(acc => {
            acc.addEventListener('click', function() {
                // Tìm thẻ cha (card)
                const card = this.parentElement;

                // Toggle class 'active' để hiện/ẩn content
                card.classList.toggle('active');

                // (Tuỳ chọn) Đóng các thẻ khác khi mở thẻ này (Accordian một chiều)
                // document.querySelectorAll('.accordion-card').forEach(c => {
                //     if (c !== card) c.classList.remove('active');
                // });
            });
        });
    });
</script>

<script>
    // Lưu lại trang hiện tại mỗi khi người dùng ở trang danh sách
    // (chỉ chạy trên trang home, search, tag...)
    if (window.location.pathname.includes('home.php') ||
        window.location.search.includes('search=') ||
        window.location.search.includes('tag=')) {
        sessionStorage.setItem('lastListPage', location.href);
    }
</script>


<script src="../../public/js/user_comments.js"></script>
<?php include_once __DIR__ . '/layout/footer.php'; ?>