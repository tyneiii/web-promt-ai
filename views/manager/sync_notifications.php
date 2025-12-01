<?php
// ======================================
//  IMPORT CONFIG (lấy $conn)
// ======================================
include_once __DIR__ . "/../../config.php";

// Check kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}


// ======================================
// 1️⃣ THÊM prompt_detail NẾU CÒN THIẾU
// ======================================

$sqlInsert = "
    INSERT INTO promptdetail (prompt_id, content, created_at)
    SELECT 
        p.prompt_id,
        CONCAT('Nội dung được tạo tự động cho: ', p.title, ' - ', p.short_description),
        NOW()
    FROM prompt p
    LEFT JOIN promptdetail d ON p.prompt_id = d.prompt_id
    WHERE d.prompt_id IS NULL
";

$conn->query($sqlInsert);


// ======================================
// 2️⃣ CẬP NHẬT DETAIL BỊ RÁC / NGẮN
// ======================================

$sqlUpdate = "
    UPDATE promptdetail d
    JOIN prompt p ON d.prompt_id = p.prompt_id
    SET 
        d.content = CONCAT('Nội dung cập nhật tự động cho: ', p.title, ' - ', p.short_description),
        d.created_at = NOW()
    WHERE d.content IS NULL 
       OR d.content = '' 
       OR LENGTH(d.content) < 5
";

$conn->query($sqlUpdate);


// ======================================
// 3️⃣ GHI LOG KẾT QUẢ
// ======================================
echo "<h3>Đồng bộ thành công!</h3>";
echo "<p>- Đã thêm các prompt_detail còn thiếu.</p>";
echo "<p>- Đã cập nhật các dòng dữ liệu rác / nội dung quá ngắn.</p>";

$conn->close();
