<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../config.php';

$account_id = $_SESSION['account_id'] ?? '';
$name_user = $_SESSION['name_user'] ?? '';

$search = $_GET['search'] ?? '';

include_once __DIR__ . '/../../controller/user/prompt.php';
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
$view_status = $_GET['view_status'] ?? 'unread';
$pagination_params = [
    'search' => $search,
    'tag' => $tag,
    'view_status' => $view_status,
];
$total_rows = totalPrompts($account_id, $search, $tag, $conn, $view_status);
$total_pages = ceil($total_rows / $rows_per_page);
$hot_prompts = getHotPrompts($conn, 5);
$following_users = [];
if ($account_id) {
    $following_users = getFollowingUsers($account_id, $conn);
}
$prompts = getPrompts($account_id, $search, $tag, $conn, $rows_per_page, $offset, $view_status);
function is_saved($conn, $account_id, $prompt_id)
{
    $account_id = (int)$account_id;
    $prompt_id  = (int)$prompt_id;
    $sql = "SELECT save_id 
            FROM `save` 
            WHERE account_id = $account_id 
              AND prompt_id = $prompt_id
            LIMIT 1";

    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return true;
    }
    return false; // chưa lưu
}

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

    <div class="box-decor">
        <h3 class="follow-title">Đang theo dõi <i class="fa-solid fa-user-group"></i></h3>

        <div class="follow-list">

            <?php if (!isset($_SESSION['account_id'])): ?>

                <a href="" class="item-link">
                    <div class="item">Bạn cần đăng nhập để xem.</div>
                </a>
        </div>

    <?php elseif (empty($following_users)): ?>

        <a href="" class="item-link">
            <div class="item">Bạn chưa theo dõi ai.</div>
        </a>

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
                            <i class="<?= is_saved($conn, $account_id, $prompt['prompt_id']) ? 'fa-solid' : 'fa-regular' ?> fa-bookmark"></i>
                            <?= $prompt['save_count'] ?>
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

            <div class="accordion-card">
                <div class="accordion-header">
                    <h3>IV. Hướng dẫn nhận Hoa hồng & Chia sẻ doanh thu</h3>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="accordion-content">
                    <p>Để khuyến khích cộng đồng chia sẻ Prompt chất lượng, Ban quản trị (Admin) sẽ trích một phần doanh thu từ quảng cáo trên website để chia sẻ lại cho người dùng.</p>
                    <strong>1. Nguyên tắc hoạt động:</strong>
                    <ul>
                        <li>Website hiển thị quảng cáo. Doanh thu của website phụ thuộc vào lượt hiển thị và click vào quảng cáo trong tháng đó.</li>
                        <li>Doanh thu này <strong>không cố định</strong> (tháng này có thể cao hơn hoặc thấp hơn tháng trước).</li>
                    </ul>
                    <strong>2. Điều kiện nhận thưởng:</strong>
                    <ul>
                        <li>Hệ thống sẽ tổng kết vào <strong>ngày cuối cùng của tháng</strong>.</li>
                        <li>Các tài khoản có bài đăng nằm trong <strong>Top Tương tác</strong> (nhiều lượt Thích/Tim và Save nhất) sẽ đủ điều kiện nhận hoa hồng.</li>
                        <li>Các bài đăng vi phạm quy tắc cộng đồng sẽ bị loại bỏ khỏi danh sách tính thưởng.</li>
                    </ul>
                    <strong>3. Yêu cầu bắt buộc:</strong>
                    <ul>
                        <li>Người dùng PHẢI cập nhật đầy đủ <strong>Thông tin ngân hàng</strong> trong phần "Cài đặt tài khoản" (Profile).</li>
                        <li>Thông tin bao gồm: <em>Tên ngân hàng, Số tài khoản, Tên chủ tài khoản</em>.</li>
                        <li>Admin không chịu trách nhiệm nếu người dùng cung cấp sai thông tin dẫn đến chuyển khoản thất bại.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const isLoggedIn = <?= isset($_SESSION['account_id']) ? 'true' : 'false' ?>;
    let currentPromptId = 0;
    const accountId = <?= $account_id ? (int)$account_id : 'null' ?>; // Lấy account_id từ PHP

    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('button') || e.target.closest('.run-btn')) return;
            const id = this.getAttribute('data-id');
            window.location.href = `detail_post.php?id=${id}`;
        });
    });

    document.querySelectorAll(".report-btn").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.stopPropagation();

            if (!isLoggedIn) {
                alert("Bạn phải đăng nhập để báo cáo!");
                window.location.href = "../login/login.php?require_login=report";
                return;
            }
        });
    });

    document.getElementById("report-reason").addEventListener("change", function() {
        document.getElementById("report-custom").style.display =
            (this.value === "Khác") ? "block" : "none";
    });

    document.getElementById("cancelReport").onclick = () => {
        document.getElementById("report-modal").style.display = "none";
    };

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
        const modal = document.getElementById('rulesModal');
        const btnOpen = document.getElementById('btnOpenRules');
        const btnClose = rulesModal.querySelector('.close-modal');

        btnOpen.addEventListener('click', function() {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        btnClose.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        window.addEventListener('click', function(e) {
            if (e.target == modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        const accordions = document.querySelectorAll('.accordion-header');

        accordions.forEach(acc => {
            acc.addEventListener('click', function() {
                const card = this.parentElement;
                card.classList.toggle('active');
            });
        });

        if (isLoggedIn) {
            function markAsViewed(promptId) {
                const endpoint = '../../public/ajax/track_view.php';
                fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            prompt_id: promptId,
                            account_id: accountId
                        }),
                        keepalive: true
                    })
                    .then(response => {
                        if (!response.ok) {
                            console.error('Track view failed:', response.status);
                        }
                        return response.json();
                    })
                    .catch(error => {
                        console.error('Error tracking view:', error);
                    });
            }

            const options = {
                root: null, // Theo dõi trong viewport
                rootMargin: '0px',
                threshold: 1.0 // 100% hiển thị
            };
            const promptObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const promptElement = entry.target;
                        const promptId = parseInt(promptElement.getAttribute('data-id'));
                        if (promptId) {
                            markAsViewed(promptId);
                            observer.unobserve(promptElement);
                        }
                    }
                });
            }, options);

            document.querySelectorAll('.card').forEach(card => {
                promptObserver.observe(card);
            });
        }

    });
</script>

<script>
    if (window.location.pathname.includes('home.php') ||
        window.location.search.includes('search=') ||
        window.location.search.includes('tag=')) {
        sessionStorage.setItem('lastListPage', location.href);
    }
</script>


<script src="../../public/js/user_comments.js"></script>
<?php include_once __DIR__ . '/layout/footer.php'; ?>