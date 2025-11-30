<?php
include_once __DIR__ . '/../config.php';

if (!isset($conn) || $conn->connect_error) {
    error_log("Lỗi kết nối CSDL: " . ($conn->connect_error ?? 'Biến $conn không tồn tại'));
    header("Location: /index.php");
    exit();
}

$metric_id = isset($_GET['ad']) ? (int)$_GET['ad'] : 1;
$landing_url = "https://www.thegioididong.com/";

// số tiền mỗi click 
$revenue_per_click = 1; // vì last_month_revenue là DECIMAL(10,0), dùng số nguyên


//1. KIỂM TRA metric_id ĐÃ TỒN TẠI CHƯA


$check = $conn->prepare("SELECT metric_id FROM revenuemetrics WHERE metric_id = ?");
$check->bind_param("i", $metric_id);
$check->execute();
$result = $check->get_result();

   //2. NẾU KHÔNG TỒN TẠI -> TẠO MỚI

if ($result->num_rows === 0) {

    $insert = $conn->prepare("
        INSERT INTO revenuemetrics (metric_id, current_month_clicks, last_month_revenue, update_at)
        VALUES (?, 1, ?, NOW())
    ");
    $insert->bind_param("ii", $metric_id, $revenue_per_click);
    $insert->execute();
    $insert->close();

    $check->close();
    $conn->close();
    header("Location: " . $landing_url);
    exit();
}

$check->close();

   // 3. NẾU ĐÃ TỒN TẠI -> UPDATE

$update = $conn->prepare("
    UPDATE revenuemetrics
    SET 
        current_month_clicks = current_month_clicks + 1,
        last_month_revenue = last_month_revenue + ?,
        update_at = NOW()
    WHERE metric_id = ?
");
$update->bind_param("ii", $revenue_per_click, $metric_id);

$update->execute();
$update->close();

$conn->close();

header("Location: " . $landing_url);
exit();

?>
