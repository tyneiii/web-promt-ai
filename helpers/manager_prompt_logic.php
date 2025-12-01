<?php
$selectedStatus = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$search_columns = $_GET['search_columns'] ?? [];
if (!empty($_GET['rows_per_page'])) {
    $rows_per_page = max((int)($_GET['rows_per_page']), 10);
} else {
    $rows_per_page = 10;
}
$current_page = (int)($_GET['page'] ?? 1);
$offset = ($current_page - 1) * $rows_per_page;
$result = getAlldPrompts($conn, $search, $selectedStatus, $search_columns, $rows_per_page, $offset);
$total_rows = $result['total'];
$posts = $result['prompts'];
$total_pages = ceil($total_rows / $rows_per_page);
$pagination_params = [
    'search' => $search,
    'selectedStatus' => $selectedStatus,
    'search_columns' => $search_columns,
];
