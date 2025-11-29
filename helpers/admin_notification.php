<?php
function getAdminNotificationCount($conn) {

    // Đếm bài chờ duyệt
    $waiting = $conn->query("
        SELECT COUNT(*) AS total 
        FROM prompt 
        WHERE status = 'waiting'
    ")->fetch_assoc()['total'];

    // Đếm bài bị báo cáo
    $report = $conn->query("
        SELECT COUNT(*) AS total 
        FROM prompt 
        WHERE status = 'report'
    ")->fetch_assoc()['total'];

    return [
        'waiting' => $waiting,
        'report' => $report,
        'total' => $waiting + $report
    ];
}
?>