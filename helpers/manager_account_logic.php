<?php
include_once __DIR__ ."/../Controller/account.php";
$search = $_GET['search'] ?? '';
$role = $_GET["role"] ?? '';
$is_active = $_GET["is_active"] ?? '';
$search_columns = $_GET['search_columns'] ?? [];
if(!empty($_GET['rows_per_page'])) {
    $rows_per_page = max((int)($_GET['rows_per_page']), 10);
}
else{
    $rows_per_page = 10;
}
$current_page = (int)($_GET['page'] ?? 1);
$offset = ($current_page - 1) * $rows_per_page;
$result = getAccounts($conn, $search, $role, $search_columns, $is_active, $rows_per_page, $offset);
$total_rows = $result['total'];
$accounts = $result['accounts'];
$total_pages = ceil($total_rows / $rows_per_page);
$pagination_params = [
    'search' => $search,
    'role' => $role,
    'is_active' => $is_active,
    'search_columns' => $search_columns,
];
$action_result = handlePostActions($conn);
if ($action_result) {
    $page_params = [
        'rows_per_page' => $_GET['rows_per_page'] ?? 25,
        'page' => $_GET['page'] ?? 1,
    ];
    $query_params = array_merge($pagination_params, $page_params);
    handlePrgRedirect($action_result, $query_params);
}

$mess = getMess();
function handlePostActions($conn)
    {
        if (isset($_POST["btnSave"])) {
            $accountId = (int)$_POST["account_id"];
            $username = $_POST["username"];
            $newRole = (int)$_POST["new_role"];
            return updateRole($conn, $accountId, $username, $newRole);
        }
        if (isset($_POST["btnStatus"])) {
            $accountId = (int)$_POST["account_id"];
            $username = $_POST["username"];
            $actionType = $_POST["action_type"];
            $newStatus = ($actionType === 'unlock') ? 1 : 0;
            return updateAccountStatus($conn, $accountId, $username, $newStatus);
        }
        return null;
    }
?>