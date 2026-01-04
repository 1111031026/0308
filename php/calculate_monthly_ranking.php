<?php
/**
 * 計算每月前三名排名並建立獎勵記錄
 * 建議每月1號執行此腳本
 */

require_once 'db_connect.php';

// 獲取上個月的月份（格式：YYYY-MM）
$last_month = date('Y-m', strtotime('first day of last month'));
$month_start = date('Y-m-01 00:00:00', strtotime('first day of last month'));
$month_end = date('Y-m-t 23:59:59', strtotime('last day of last month'));

echo "計算 {$last_month} 月的排名...\n";

// 檢查是否已經計算過
$check_sql = "SELECT COUNT(*) as count FROM monthly_ranking_rewards WHERE Month = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $last_month);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_row = $check_result->fetch_assoc();
$check_stmt->close();

if ($check_row['count'] > 0) {
    echo "{$last_month} 月的排名已經計算過了，跳過。\n";
    exit;
}

// 獲取獎勵點數設定
$settings_sql = "SELECT setting_key, setting_value FROM system_settings 
                 WHERE setting_key IN ('points_monthly_rank_1', 'points_monthly_rank_2', 'points_monthly_rank_3')";
$settings_result = $conn->query($settings_sql);
$points_config = [
    1 => 100, // 預設值
    2 => 50,
    3 => 30
];
while ($row = $settings_result->fetch_assoc()) {
    if ($row['setting_key'] === 'points_monthly_rank_1') {
        $points_config[1] = intval($row['setting_value']);
    } elseif ($row['setting_key'] === 'points_monthly_rank_2') {
        $points_config[2] = intval($row['setting_value']);
    } elseif ($row['setting_key'] === 'points_monthly_rank_3') {
        $points_config[3] = intval($row['setting_value']);
    }
}

// 獲取所有有學生的地區
$regions_sql = "SELECT DISTINCT Region FROM user WHERE Region IS NOT NULL AND Status = 'Student'";
$regions_result = $conn->query($regions_sql);
$regions = [];
while ($row = $regions_result->fetch_assoc()) {
    $regions[] = $row['Region'];
}

$total_rewards = 0;

// 對每個地區計算前三名
foreach ($regions as $region) {
    // 計算該地區學生在本月獲得的點數
    $ranking_sql = "SELECT 
                        u.UserID,
                        u.Username,
                        COALESCE(SUM(CASE 
                            WHEN cr.FinishTime >= ? AND cr.FinishTime <= ? AND cr.isCorrect = 1 THEN 5
                            WHEN tr.FinishTime >= ? AND tr.FinishTime <= ? AND tr.isCorrect = 1 THEN 5
                            WHEN fr.FinishTime >= ? AND fr.FinishTime <= ? AND fr.isCorrect = 1 THEN 5
                            ELSE 0
                        END), 0) AS MonthlyPoints
                    FROM user u
                    LEFT JOIN choicerec cr ON u.UserID = cr.UserID
                    LEFT JOIN tfrec tr ON u.UserID = tr.UserID
                    LEFT JOIN fillrec fr ON u.UserID = fr.UserID
                    WHERE u.Region = ? AND u.Status = 'Student'
                    GROUP BY u.UserID, u.Username
                    HAVING MonthlyPoints > 0
                    ORDER BY MonthlyPoints DESC, u.Username ASC
                    LIMIT 3";
    
    $ranking_stmt = $conn->prepare($ranking_sql);
    $ranking_stmt->bind_param("sssssss", $month_start, $month_end, $month_start, $month_end, $month_start, $month_end, $region);
    $ranking_stmt->execute();
    $ranking_result = $ranking_stmt->get_result();
    
    $rank = 1;
    $prev_points = null;
    
    while ($row = $ranking_result->fetch_assoc()) {
        // 處理同分情況（同分時並列排名）
        if ($prev_points !== null && $row['MonthlyPoints'] < $prev_points) {
            $rank++;
        }
        
        // 只獎勵前三名
        if ($rank <= 3) {
            $points_awarded = $points_config[$rank];
            
            // 插入獎勵記錄
            $insert_sql = "INSERT INTO monthly_ranking_rewards 
                          (UserID, Region, Month, Rank, PointsAwarded, Status) 
                          VALUES (?, ?, ?, ?, ?, 'PENDING')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("issii", $row['UserID'], $region, $last_month, $rank, $points_awarded);
            $insert_stmt->execute();
            $insert_stmt->close();
            
            $total_rewards++;
            echo "  {$region} 第 {$rank} 名：{$row['Username']} ({$row['MonthlyPoints']} 分) - 獎勵 {$points_awarded} 點\n";
        }
        
        $prev_points = $row['MonthlyPoints'];
    }
    
    $ranking_stmt->close();
}

echo "\n完成！共建立 {$total_rewards} 筆獎勵記錄。\n";
$conn->close();
?>

