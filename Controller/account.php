 <?php
    function getAccounts($conn, $search, $role, $columns)
    {
        $allowed_columns = ['account_id', 'username', 'email'];
        if (empty($columns)) {
            $columns = $allowed_columns;
        }
        $conditions = [];
        $bind = "s";
        $where = TRUE;
        if (!empty($columns)) {
            foreach ($columns as $column) {
                $conditions[] = "account.$column LIKE ?";
                $bind .= "s";
            }
            $where = "(" . implode(" OR ", $conditions) . ")";
        }
        $sql = "SELECT account.account_id, account.username, account.email, role.role_name
            FROM account
            JOIN role ON account.role_id=role.role_id
            WHERE $where AND role.role_name LIKE ? ";
        $stmt = $conn->prepare($sql);
        $like_search = "%" . $search . "%";
        $like_role = "%" . $role . "%";
        if (empty($columns)) {
            $stmt->bind_param($bind, $like_role);
        } else {
            switch (count($columns)) {
                case 1:
                    $stmt->bind_param($bind, $like_search, $like_role);
                    break;
                case 2:
                    $stmt->bind_param($bind, $like_search, $like_search, $like_role);
                    break;
                case 3:
                    $stmt->bind_param($bind, $like_search, $like_search, $like_search, $like_role);
                    break;
            }
        }
        $stmt->execute();
        return $stmt->get_result();
    }
   function changeRole($conn, $account_id, $role)
{
    $sql = "UPDATE account SET role_id = ? WHERE account_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("SQL Prepare Failed (changeRole): " . $conn->error . " | Query: " . $sql);
        return [
            'success' => false,
            'message' => "Lỗi hệ thống (Mã 502). Không thể chuẩn bị truy vấn."
        ];
    }
    $stmt->bind_param("ii", $role, $account_id);
    $execute_success = $stmt->execute();
    $rows_affected = $stmt->affected_rows;
    $stmt->close();
    if (!$execute_success) {
        error_log("SQL Execute Failed (changeRole): " . $stmt->error . " | Account ID: " . $account_id);
        return [
            'success' => false,
            'message' => "Có lỗi xảy ra trong quá trình cập nhật vai trò."
        ];
    }
    if ($rows_affected > 0) {
        return [
            'success' => true,
            'message' => "Cập nhật thành công tài khoản có ID={$account_id}."
        ];
    } else {
        return [
            'success' => true, 
            'message' => "Thao tác trên tài khoản có ID={$account_id}, không có thay đổi nào."
        ];
    }
}
    ?>