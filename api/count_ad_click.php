<?php
    include_once __DIR__ . '/../config.php';

    if (!isset($conn) || $conn->connect_error) {
        error_log("Lỗi kết nối CSDL: " . ($conn->connect_error ?? 'Biến $conn không tồn tại'));
        header("Location: /index.php"); 
        exit();
    }

    $metric_id_to_update = isset($_GET['ad']) ? (int)$_GET['ad'] : 1; 


    $landing_url = "https://hopeinconnection.com/"; 


    $sql = "UPDATE revenuemetrics 
            SET 
                current_month_clicks = current_month_clicks + 1, 
                update_at = NOW() 
            WHERE 
                metric_id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Lỗi SQL Prepare: " . $conn->error);
        header("Location: " . $landing_url); 
        exit();
    }

    $stmt->bind_param("i", $metric_id_to_update);

    if (!$stmt->execute()) {
        error_log("Lỗi SQL Execute: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    header("Location: " . $landing_url);
    exit(); 
?>