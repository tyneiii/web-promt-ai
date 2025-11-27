<?php
function handlePrgRedirect($result, $current_get_params)
{
    if (!empty($result) && is_array($result)) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['status_message'] = $result;
    }
    $filtered_params = array_filter($current_get_params, function ($value) {
        return $value !== null && $value !== '';
    });
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    if (!empty($filtered_params)) {
        $redirect_url .= '?' . http_build_query($filtered_params);
    }
    header("Location: " . $redirect_url);
    exit();
}

function getMess()
{
    $result = [];
    if (isset($_SESSION['status_message'])) {
        $result = $_SESSION['status_message'];
        unset($_SESSION['status_message']);
    }
    return $result;
}

function printMess($result)
{
    if (!is_array($result) || empty($result['message'])) {
        return;
    }
    $mess = $result['message'];
    $class = $result['success'] ? 'alert-success' : 'alert-error';
    echo "<div class='" . $class . "'>"
        . htmlspecialchars($mess) .
        "</div>";
}
function getFlexiblePaginationUrl($page, $rows_per_page, $extra_params)
{
    $params = [
        'page' => $page,
        'rows_per_page' => $rows_per_page,
    ];
    $all_params = array_merge($params, $extra_params);
    $search_columns = $all_params['search_columns'] ?? [];
    unset($all_params['search_columns']); 
    $query_string = http_build_query(array_filter($all_params, function ($value) {
        return $value !== '' && $value !== null;
    }));
    if (!empty($search_columns)) {
        foreach ($search_columns as $col) {
            $query_string .= '&search_columns[]=' . urlencode($col);
        }
    }
    return '?' . $query_string;
}
function renderPagination($current_page,$total_pages,$rows_per_page,$extra_params = []) 
{
    if ($total_pages <= 1) {
        return '';
    }
    $get_url = 'getFlexiblePaginationUrl';
    ob_start();
?>
    <div class="pagination-container">
        <p>Trang <?= $current_page ?> / <?= $total_pages ?></p>
        <div class="pagination-links">
            <?php if ($current_page > 1): ?>
                <a href="<?= $get_url(1, $rows_per_page, $extra_params) ?>"
                    class="page-link">
                    <i class="fa-solid fa-angles-left"></i>
                </a>
                <a href="<?= $get_url(max(1, $current_page - 1), $rows_per_page, $extra_params) ?>"
                    class="page-link">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
            <?php endif ?>
            <?php
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <a href="<?= $get_url($i, $rows_per_page, $extra_params) ?>"
                    class="page-link <?= ($i == $current_page) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <?php if ($current_page < $total_pages): ?>
                <a href="<?= $get_url(min($total_pages, $current_page + 1), $rows_per_page, $extra_params) ?>"
                    class="page-link">
                    <i class="fa-solid fa-angle-right"></i>
                </a>
                <a href="<?= $get_url($total_pages, $rows_per_page, $extra_params) ?>"
                    class="page-link">
                    <i class="fa-solid fa-angles-right"></i>
                </a>
            <?php endif ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}
