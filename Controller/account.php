 <?php
    function handlePostActions($conn)
    {
        if (isset($_POST["btnSave"])) {
            $accountId = (int)$_POST["account_id"];
            $newRole = (int)$_POST["new_role"];
            return updateRole($conn, $accountId, $newRole);
        }
        if (isset($_POST["btnStatus"])) {
            $accountId = (int)$_POST["account_id"];
            $actionType = $_POST["action_type"];
            $newStatus = ($actionType === 'unlock') ? 1 : 0;
            return updateAccountStatus($conn, $accountId, $newStatus);
        }
        return null;
    }
    function checkSuccess($execute_success, $stmt, $rows_affected, $account_id)
    {
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
                'message' => "Không có thay đổi nào."
            ];
        }
    }
    function getAccounts($conn, $search, $role, $columns, $is_active)
    {
        $allowed_columns = ['account_id', 'username', 'email'];
        if (empty($columns)) {
            $columns = $allowed_columns;
        }
        $conditions = [];
        $bind = "ss";
        $where = TRUE;
        if (!empty($columns)) {
            foreach ($columns as $column) {
                $conditions[] = "account.$column LIKE ?";
                $bind .= "s";
            }
            $where = "(" . implode(" OR ", $conditions) . ")";
        }
        $sql = "SELECT account.account_id, account.username, account.email, account.is_active, role.role_name
            FROM account
            JOIN role ON account.role_id=role.role_id
            WHERE $where AND role.role_name LIKE ? AND account.is_active LIKE ? ";
        $stmt = $conn->prepare($sql);
        $like_search = "%" . $search . "%";
        $like_role = "%" . $role . "%";
        $like_active = "%" . $is_active . "%";
        if (empty($columns)) {
            $stmt->bind_param($bind, $like_role, $like_active);
        } else {
            switch (count($columns)) {
                case 1:
                    $stmt->bind_param($bind, $like_search, $like_role, $like_active);
                    break;
                case 2:
                    $stmt->bind_param($bind, $like_search, $like_search, $like_role, $like_active);
                    break;
                case 3:
                    $stmt->bind_param($bind, $like_search, $like_search, $like_search, $like_role, $like_active);
                    break;
            }
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    function updateRole($conn, $account_id, $role)
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
        return checkSuccess($execute_success, $stmt, $rows_affected,$account_id);
    }
    function updateAccountStatus($conn, $account_id, $status)
    {
        $sql = "UPDATE account SET is_active = ? WHERE account_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("SQL Prepare Failed (changeRole): " . $conn->error . " | Query: " . $sql);
            return [
                'success' => false,
                'message' => "Lỗi hệ thống (Mã 502). Không thể chuẩn bị truy vấn."
            ];
        }
        $stmt->bind_param("ii", $status, $account_id);
        $execute_success = $stmt->execute();
        $rows_affected = $stmt->affected_rows;
        $stmt->close();
        return checkSuccess($execute_success, $stmt, $rows_affected, $account_id);
    }
    ?>