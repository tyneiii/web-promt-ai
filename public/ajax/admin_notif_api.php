<?php
include_once __DIR__ . "/../../config.php";

$waiting = $conn->query("SELECT COUNT(*) AS total FROM prompt WHERE status = 'waiting'")
    ->fetch_assoc()['total'];

$report = $conn->query("SELECT COUNT(*) AS total FROM prompt WHERE status = 'report'")
    ->fetch_assoc()['total'];

echo json_encode([
    'waiting' => (int)$waiting,
    'report'  => (int)$report,
    'total'   => (int)($waiting + $report)
]);
