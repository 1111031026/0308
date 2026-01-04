<?php
/**
 * 更新地區統計數據
 * 計算各地區的總分、平均分和參與學生數量
 */

function updateRegionStats($region = null) {
    require_once 'db_connect.php';
    global $conn;
    
    // 如果指定了地區，只更新該地區；否則更新所有地區
    if ($region) {
        $regions = [$region];
    } else {
        // 獲取所有有學生的地區
        $regions_sql = "SELECT DISTINCT Region FROM user WHERE Region IS NOT NULL AND Status = 'Student'";
        $regions_result = $conn->query($regions_sql);
        $regions = [];
        while ($row = $regions_result->fetch_assoc()) {
            $regions[] = $row['Region'];
        }
    }
    
    foreach ($regions as $current_region) {
        // 計算該地區的統計數據
        $stats_sql = "SELECT 
                        COUNT(DISTINCT a.UserID) AS StudentCount,
                        COALESCE(SUM(a.TotalPoints), 0) AS TotalPoints,
                        COALESCE(AVG(a.TotalPoints), 0) AS AveragePoints
                      FROM achievement a
                      JOIN user u ON a.UserID = u.UserID
                      WHERE u.Region = ? AND u.Status = 'Student'";
        
        $stats_stmt = $conn->prepare($stats_sql);
        $stats_stmt->bind_param("s", $current_region);
        $stats_stmt->execute();
        $stats_result = $stats_stmt->get_result();
        $stats = $stats_result->fetch_assoc();
        $stats_stmt->close();
        
        // 更新或插入地區統計
        $update_sql = "INSERT INTO region_leaderboard (Region, TotalPoints, AveragePoints, StudentCount) 
                       VALUES (?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE 
                       TotalPoints = VALUES(TotalPoints),
                       AveragePoints = VALUES(AveragePoints),
                       StudentCount = VALUES(StudentCount),
                       LastUpdated = CURRENT_TIMESTAMP";
        
        $update_stmt = $conn->prepare($update_sql);
        $total_points = intval($stats['TotalPoints']);
        $average_points = floatval($stats['AveragePoints']);
        $student_count = intval($stats['StudentCount']);
        
        $update_stmt->bind_param("sddi", $current_region, $total_points, $average_points, $student_count);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

// 如果直接執行此文件，更新所有地區統計
if (basename($_SERVER['PHP_SELF']) === 'update_region_stats.php') {
    updateRegionStats();
    echo "地區統計已更新";
}

?>

