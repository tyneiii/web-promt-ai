 <?php
    function getAccounts($conn, $search, $role, $columns)
    {
        $allowed_columns = ['account_id', 'username', 'email'];
        if(empty($columns)){
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
    ?>