<?php
    function getAccounts($conn, $search, $role){
        $sql="SELECT account.account_id, account.username, account.email, role.role_name 
            FROM account
            JOIN role ON account.role_id=role.role_id
            WHERE account.username LIKE ? AND role.role_id LIKE ? ";
        $stmt=$conn->prepare($sql);
        $like_search= "%".$search."%";
        $like_role= "%".$role."%";
        $stmt->bind_param("ss",$like_search,$like_role);
        $stmt->execute();
        return $stmt->get_result();
    }
?>