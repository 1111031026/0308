<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true) {
    header('Location: user_login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'Student';

// 獲取用戶資訊
$user_sql = "SELECT Region FROM user WHERE UserID = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

$user_region = $user['Region'] ?? null;
$needs_region_setup = empty($user_region);

// 檢查是否有錯誤訊息
$error_message = '';
if (isset($_SESSION['region_setup_error'])) {
    $error_message = $_SESSION['region_setup_error'];
    unset($_SESSION['region_setup_error']);
}

// 檢查是否有成功訊息
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == '1') {
    if (isset($_SESSION['region_setup_success'])) {
        $success_message = $_SESSION['region_setup_success'];
        unset($_SESSION['region_setup_success']);
    } else {
        $success_message = '操作成功！';
    }
}

// 時間範圍參數（總計/本月/本週）
$time_range = $_GET['range'] ?? 'total'; // total, monthly, weekly

// 如果已設定地區，獲取排行榜數據
$leaderboard_data = [];
$region_rankings = [];
$user_stats = null;
$region_stats = null;
$monthly_rewards = []; // 上月前三名獎勵

if (!$needs_region_setup) {
    // 獲取上月前三名獎勵（如果用戶有）
    $last_month = date('Y-m', strtotime('first day of last month'));
    $reward_sql = "SELECT RewardID, Rank, PointsAwarded, Status, Month
                   FROM monthly_ranking_rewards
                   WHERE UserID = ? AND Region = ? AND Month = ?
                   ORDER BY Rank ASC";
    $reward_stmt = $conn->prepare($reward_sql);
    $reward_stmt->bind_param("iss", $user_id, $user_region, $last_month);
    $reward_stmt->execute();
    $reward_result = $reward_stmt->get_result();
    while ($row = $reward_result->fetch_assoc()) {
        $monthly_rewards[] = $row;
    }
    $reward_stmt->close();
    
    // 根據時間範圍獲取排行榜數據
    if ($time_range === 'total') {
        // 總計排名（使用現有邏輯）
        $leaderboard_sql = "SELECT Region, TotalPoints, AveragePoints, StudentCount 
                            FROM region_leaderboard 
                            WHERE StudentCount > 0
                            ORDER BY TotalPoints DESC, AveragePoints DESC 
                            LIMIT 50";
        $leaderboard_result = $conn->query($leaderboard_sql);
        
        while ($row = $leaderboard_result->fetch_assoc()) {
            $leaderboard_data[] = $row;
        }
        
        // 地區個人排名（總計）
        $region_rankings_sql = "SELECT 
                                    a.UserID,
                                    a.TotalPoints AS Points,
                                    u.Username,
                                    u.AvatarURL,
                                    u.Region,
                                    (SELECT COUNT(*) + 1 
                                     FROM achievement a2 
                                     JOIN user u2 ON a2.UserID = u2.UserID 
                                     WHERE u2.Region = u.Region 
                                     AND a2.TotalPoints > a.TotalPoints) AS RegionRank
                                FROM achievement a
                                JOIN user u ON a.UserID = u.UserID
                                WHERE u.Region = ? AND u.Status = 'Student'
                                ORDER BY a.TotalPoints DESC, u.Username ASC";
    } elseif ($time_range === 'monthly') {
        // 本月排名（計算本月獲得的點數）
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t 23:59:59');
        
        // 地區排行榜（本月）
        $leaderboard_sql = "SELECT 
                                u.Region,
                                COALESCE(SUM(CASE 
                                    WHEN cr.FinishTime >= ? AND cr.FinishTime <= ? AND cr.isCorrect = 1 THEN 5
                                    WHEN tr.FinishTime >= ? AND tr.FinishTime <= ? AND tr.isCorrect = 1 THEN 5
                                    WHEN fr.FinishTime >= ? AND fr.FinishTime <= ? AND fr.isCorrect = 1 THEN 5
                                    ELSE 0
                                END), 0) AS TotalPoints,
                                COUNT(DISTINCT u.UserID) AS StudentCount
                            FROM user u
                            LEFT JOIN achievement a ON u.UserID = a.UserID
                            LEFT JOIN choicerec cr ON u.UserID = cr.UserID
                            LEFT JOIN tfrec tr ON u.UserID = tr.UserID
                            LEFT JOIN fillrec fr ON u.UserID = fr.UserID
                            WHERE u.Status = 'Student' AND u.Region IS NOT NULL
                            GROUP BY u.Region
                            HAVING TotalPoints > 0
                            ORDER BY TotalPoints DESC
                            LIMIT 50";
        $leaderboard_stmt = $conn->prepare($leaderboard_sql);
        $leaderboard_stmt->bind_param("ssssss", $month_start, $month_end, $month_start, $month_end, $month_start, $month_end);
        $leaderboard_stmt->execute();
        $leaderboard_result = $leaderboard_stmt->get_result();
        while ($row = $leaderboard_result->fetch_assoc()) {
            $leaderboard_data[] = $row;
        }
        $leaderboard_stmt->close();
        
        // 地區個人排名（本月）
        $region_rankings_sql = "SELECT 
                                    u.UserID,
                                    COALESCE(SUM(CASE 
                                        WHEN cr.FinishTime >= ? AND cr.FinishTime <= ? AND cr.isCorrect = 1 THEN 5
                                        WHEN tr.FinishTime >= ? AND tr.FinishTime <= ? AND tr.isCorrect = 1 THEN 5
                                        WHEN fr.FinishTime >= ? AND fr.FinishTime <= ? AND fr.isCorrect = 1 THEN 5
                                        ELSE 0
                                    END), 0) AS Points,
                                    u.Username,
                                    u.AvatarURL,
                                    u.Region,
                                    0 AS RegionRank
                                FROM user u
                                LEFT JOIN choicerec cr ON u.UserID = cr.UserID
                                LEFT JOIN tfrec tr ON u.UserID = tr.UserID
                                LEFT JOIN fillrec fr ON u.UserID = fr.UserID
                                WHERE u.Region = ? AND u.Status = 'Student'
                                GROUP BY u.UserID, u.Username, u.AvatarURL, u.Region
                                HAVING Points > 0
                                ORDER BY Points DESC, u.Username ASC";
    } else { // weekly
        // 本週排名（計算本週獲得的點數）
        $week_start = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $week_end = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        
        // 地區排行榜（本週）
        $leaderboard_sql = "SELECT 
                                u.Region,
                                COALESCE(SUM(CASE 
                                    WHEN cr.FinishTime >= ? AND cr.FinishTime <= ? AND cr.isCorrect = 1 THEN 5
                                    WHEN tr.FinishTime >= ? AND tr.FinishTime <= ? AND tr.isCorrect = 1 THEN 5
                                    WHEN fr.FinishTime >= ? AND fr.FinishTime <= ? AND fr.isCorrect = 1 THEN 5
                                    ELSE 0
                                END), 0) AS TotalPoints,
                                COUNT(DISTINCT u.UserID) AS StudentCount
                            FROM user u
                            LEFT JOIN achievement a ON u.UserID = a.UserID
                            LEFT JOIN choicerec cr ON u.UserID = cr.UserID
                            LEFT JOIN tfrec tr ON u.UserID = tr.UserID
                            LEFT JOIN fillrec fr ON u.UserID = fr.UserID
                            WHERE u.Status = 'Student' AND u.Region IS NOT NULL
                            GROUP BY u.Region
                            HAVING TotalPoints > 0
                            ORDER BY TotalPoints DESC
                            LIMIT 50";
        $leaderboard_stmt = $conn->prepare($leaderboard_sql);
        $leaderboard_stmt->bind_param("ssssss", $week_start, $week_end, $week_start, $week_end, $week_start, $week_end);
        $leaderboard_stmt->execute();
        $leaderboard_result = $leaderboard_stmt->get_result();
        while ($row = $leaderboard_result->fetch_assoc()) {
            $leaderboard_data[] = $row;
        }
        $leaderboard_stmt->close();
        
        // 地區個人排名（本週）
        $region_rankings_sql = "SELECT 
                                    u.UserID,
                                    COALESCE(SUM(CASE 
                                        WHEN cr.FinishTime >= ? AND cr.FinishTime <= ? AND cr.isCorrect = 1 THEN 5
                                        WHEN tr.FinishTime >= ? AND tr.FinishTime <= ? AND tr.isCorrect = 1 THEN 5
                                        WHEN fr.FinishTime >= ? AND fr.FinishTime <= ? AND fr.isCorrect = 1 THEN 5
                                        ELSE 0
                                    END), 0) AS Points,
                                    u.Username,
                                    u.AvatarURL,
                                    u.Region,
                                    0 AS RegionRank
                                FROM user u
                                LEFT JOIN choicerec cr ON u.UserID = cr.UserID
                                LEFT JOIN tfrec tr ON u.UserID = tr.UserID
                                LEFT JOIN fillrec fr ON u.UserID = fr.UserID
                                WHERE u.Region = ? AND u.Status = 'Student'
                                GROUP BY u.UserID, u.Username, u.AvatarURL, u.Region
                                HAVING Points > 0
                                ORDER BY Points DESC, u.Username ASC";
    }
    
    // 執行地區個人排名查詢
    if ($time_range === 'total') {
        $rank_stmt = $conn->prepare($region_rankings_sql);
        $rank_stmt->bind_param("s", $user_region);
    } else {
        $rank_stmt = $conn->prepare($region_rankings_sql);
        if ($time_range === 'monthly') {
            $rank_stmt->bind_param("sssssss", $month_start, $month_end, $month_start, $month_end, $month_start, $month_end, $user_region);
        } else {
            $rank_stmt->bind_param("sssssss", $week_start, $week_end, $week_start, $week_end, $week_start, $week_end, $user_region);
        }
    }
    
    $rank_stmt->execute();
    $rank_result = $rank_stmt->get_result();
    
    $temp_rankings = [];
    while ($row = $rank_result->fetch_assoc()) {
        $temp_rankings[] = $row;
    }
    $rank_stmt->close();
    
    // 計算排名（如果是本月或本週）
    if ($time_range !== 'total') {
        $rank = 1;
        $prev_points = null;
        foreach ($temp_rankings as $idx => $row) {
            if ($prev_points !== null && $row['Points'] < $prev_points) {
                $rank = $idx + 1;
            }
            $row['RegionRank'] = $rank;
            $region_rankings[] = $row;
            if ($row['UserID'] == $user_id) {
                $user_stats = $row;
            }
            $prev_points = $row['Points'];
        }
    } else {
        $region_rankings = $temp_rankings;
        foreach ($region_rankings as $row) {
            if ($row['UserID'] == $user_id) {
                $user_stats = $row;
            }
        }
    }
    
    // 獲取用戶地區的統計資訊（僅總計時顯示）
    if ($time_range === 'total') {
        $region_stats_sql = "SELECT TotalPoints, AveragePoints, StudentCount 
                             FROM region_leaderboard 
                             WHERE Region = ?";
        $region_stats_stmt = $conn->prepare($region_stats_sql);
        $region_stats_stmt->bind_param("s", $user_region);
        $region_stats_stmt->execute();
        $region_stats_result = $region_stats_stmt->get_result();
        $region_stats = $region_stats_result->fetch_assoc();
        $region_stats_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>地區排行榜 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/region_leaderboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="leaderboard-container">
        <div class="leaderboard-header">
            <h1><i class="fas fa-trophy"></i> 地區排行榜</h1>
            <p class="subtitle">看看哪個地區的學生最積極學習永續知識！</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message" style="max-width: 900px; margin: 0 auto 20px; padding: 15px; background: #fff5f5; color: #e53e3e; border-radius: 8px; border-left: 4px solid #e53e3e;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message" style="max-width: 900px; margin: 0 auto 20px; padding: 15px; background: #f0fff4; color: #2f855a; border-radius: 8px; border-left: 4px solid #2f855a;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($needs_region_setup): ?>
            <!-- 地區設定頁面 -->
            <div class="region-setup-section">
                <div class="setup-card">
                    <h2><i class="fas fa-map-marker-alt"></i> 設定您的地區</h2>
                    <p class="setup-description">為了參與地區排行榜，請先設定您的地區</p>
                    
                    <div class="setup-options">
                        <div class="setup-option" id="manualOption">
                            <div class="option-icon">
                                <i class="fas fa-hand-pointer"></i>
                            </div>
                            <h3>手動選擇地區</h3>
                            <p>從下拉選單中選擇您所在的縣市</p>
                            <form method="POST" action="set_region.php" class="region-form" id="manualForm">
                                <input type="hidden" name="method" value="manual">
                                <select name="region" class="region-select" required>
                                    <option value="">請選擇縣市</option>
                                    <option value="台北市">台北市</option>
                                    <option value="新北市">新北市</option>
                                    <option value="桃園市">桃園市</option>
                                    <option value="台中市">台中市</option>
                                    <option value="台南市">台南市</option>
                                    <option value="高雄市">高雄市</option>
                                    <option value="基隆市">基隆市</option>
                                    <option value="新竹市">新竹市</option>
                                    <option value="嘉義市">嘉義市</option>
                                    <option value="新竹縣">新竹縣</option>
                                    <option value="苗栗縣">苗栗縣</option>
                                    <option value="彰化縣">彰化縣</option>
                                    <option value="南投縣">南投縣</option>
                                    <option value="雲林縣">雲林縣</option>
                                    <option value="嘉義縣">嘉義縣</option>
                                    <option value="屏東縣">屏東縣</option>
                                    <option value="宜蘭縣">宜蘭縣</option>
                                    <option value="花蓮縣">花蓮縣</option>
                                    <option value="台東縣">台東縣</option>
                                    <option value="澎湖縣">澎湖縣</option>
                                    <option value="金門縣">金門縣</option>
                                    <option value="連江縣">連江縣</option>
                                </select>
                                <button type="submit" class="submit-btn">
                                    <i class="fas fa-check"></i> 確認設定
                                </button>
                            </form>
                        </div>
                        
                        <div class="setup-option" id="autoOption">
                            <div class="option-icon">
                                <i class="fas fa-crosshairs"></i>
                            </div>
                            <h3>自動獲取地區</h3>
                            <p>使用 GPS 自動判斷您所在的縣市</p>
                            <form method="POST" action="set_region.php" class="region-form" id="autoForm">
                                <input type="hidden" name="method" value="auto">
                                <input type="hidden" name="latitude" id="autoLat">
                                <input type="hidden" name="longitude" id="autoLng">
                                <button type="button" class="get-location-btn" id="getLocationBtn">
                                    <i class="fas fa-map-marker-alt"></i> 取得目前位置
                                </button>
                                <div id="locationStatus" class="location-status"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- 排行榜顯示頁面 -->
            <div class="leaderboard-content">
                <!-- 上月前三名獎勵區塊 -->
                <?php if (!empty($monthly_rewards)): ?>
                <div class="monthly-rewards-section">
                    <div class="rewards-header">
                        <h2><i class="fas fa-gift"></i> 上月排名獎勵</h2>
                        <p class="rewards-description">恭喜您在上月地區排名中獲得前三名！</p>
                    </div>
                    <div class="rewards-list">
                        <?php foreach ($monthly_rewards as $reward): ?>
                        <div class="reward-item <?php echo $reward['Status'] === 'CLAIMED' ? 'claimed' : ''; ?>">
                            <div class="reward-info">
                                <div class="reward-rank">
                                    <?php if ($reward['Rank'] == 1): ?>
                                        <span class="medal medal-1"><i class="fas fa-medal"></i></span>
                                    <?php elseif ($reward['Rank'] == 2): ?>
                                        <span class="medal medal-2"><i class="fas fa-medal"></i></span>
                                    <?php else: ?>
                                        <span class="medal medal-3"><i class="fas fa-medal"></i></span>
                                    <?php endif; ?>
                                    <span class="rank-text">第 <?php echo $reward['Rank']; ?> 名</span>
                                </div>
                                <div class="reward-points">
                                    <i class="fas fa-coins"></i> <?php echo number_format($reward['PointsAwarded']); ?> 點
                                </div>
                            </div>
                            <?php if ($reward['Status'] === 'PENDING'): ?>
                            <form method="POST" action="claim_monthly_reward.php" class="claim-form">
                                <input type="hidden" name="reward_id" value="<?php echo $reward['RewardID']; ?>">
                                <button type="submit" class="claim-btn">
                                    <i class="fas fa-hand-holding-usd"></i> 領取獎勵
                                </button>
                            </form>
                            <?php else: ?>
                            <div class="claimed-badge">
                                <i class="fas fa-check-circle"></i> 已領取
                                <?php if ($reward['AwardedDate']): ?>
                                    <span class="claimed-date"><?php echo date('Y-m-d', strtotime($reward['AwardedDate'])); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 時間範圍切換 -->
                <div class="time-range-selector">
                    <a href="?range=total" class="range-btn <?php echo $time_range === 'total' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i> 總計
                    </a>
                    <a href="?range=monthly" class="range-btn <?php echo $time_range === 'monthly' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> 本月
                    </a>
                    <a href="?range=weekly" class="range-btn <?php echo $time_range === 'weekly' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-week"></i> 本週
                    </a>
                </div>
                
                <!-- 地區個人排名排行榜 -->
                <?php if (!empty($region_rankings)): ?>
                <div class="leaderboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-users"></i> <?php echo htmlspecialchars($user_region); ?> 地區排名</h2>
                        <p class="section-description">
                            <?php 
                            if ($time_range === 'total') {
                                echo '依總分排序，共 ' . count($region_rankings) . ' 位學生';
                            } elseif ($time_range === 'monthly') {
                                echo '依本月獲得點數排序，共 ' . count($region_rankings) . ' 位學生';
                            } else {
                                echo '依本週獲得點數排序，共 ' . count($region_rankings) . ' 位學生';
                            }
                            ?>
                        </p>
                    </div>
                    
                    <div class="leaderboard-table">
                        <div class="table-header">
                            <div class="col-rank">排名</div>
                            <div class="col-username">學生</div>
                            <div class="col-total">總分</div>
                        </div>
                        <div class="table-body">
                            <?php 
                            foreach ($region_rankings as $student): 
                                $is_current_user = ($student['UserID'] == $user_id);
                                $rank = $student['RegionRank'];
                                $avatar = $student['AvatarURL'] ? '../' . htmlspecialchars($student['AvatarURL']) : '';
                            ?>
                            <div class="table-row <?php echo $is_current_user ? 'current-user' : ''; ?>">
                                <div class="col-rank">
                                    <?php if ($rank <= 3): ?>
                                        <span class="medal medal-<?php echo $rank; ?>">
                                            <i class="fas fa-medal"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="rank-number"><?php echo $rank; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-username">
                                    <div class="user-info-cell">
                                        <?php if ($avatar): ?>
                                            <img src="<?php echo $avatar; ?>" alt="頭像" class="user-avatar-small">
                                        <?php else: ?>
                                            <div class="default-avatar-small">👤</div>
                                        <?php endif; ?>
                                        <span class="username-text">
                                            <?php echo htmlspecialchars($student['Username']); ?>
                                            <?php if ($is_current_user): ?>
                                                <span class="current-user-badge">（我）</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-total"><?php echo number_format($student['Points'] ?? $student['TotalPoints'] ?? 0); ?> 分</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 地區總分排行榜 -->
                <div class="leaderboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-trophy"></i> 地區排行榜</h2>
                        <p class="section-description">
                            <?php 
                            if ($time_range === 'total') {
                                echo '依各地區學生總分排序';
                            } elseif ($time_range === 'monthly') {
                                echo '依各地區學生本月獲得點數排序';
                            } else {
                                echo '依各地區學生本週獲得點數排序';
                            }
                            ?>
                        </p>
                    </div>
                    
                    <?php if (empty($leaderboard_data)): ?>
                        <div class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>目前還沒有地區數據，快來成為第一個參與的學生吧！</p>
                        </div>
                    <?php else: ?>
                        <div class="leaderboard-table">
                            <div class="table-header">
                                <div class="col-rank">排名</div>
                                <div class="col-region">地區</div>
                                <div class="col-total">總分</div>
                                <div class="col-average">平均分</div>
                                <div class="col-students">參與人數</div>
                            </div>
                            <div class="table-body">
                                <?php 
                                $rank = 1;
                                foreach ($leaderboard_data as $row): 
                                    $is_user_region = ($row['Region'] === $user_region);
                                ?>
                                <div class="table-row <?php echo $is_user_region ? 'user-region' : ''; ?>">
                                    <div class="col-rank">
                                        <?php if ($rank <= 3): ?>
                                            <span class="medal medal-<?php echo $rank; ?>">
                                                <i class="fas fa-medal"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="rank-number"><?php echo $rank; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-region">
                                        <?php if ($is_user_region): ?>
                                            <span class="my-region-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['Region']); ?></span>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($row['Region']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-total"><?php echo number_format($row['TotalPoints']); ?></div>
                                    <?php if ($time_range === 'total' && isset($row['AveragePoints'])): ?>
                                    <div class="col-average"><?php echo number_format($row['AveragePoints'], 1); ?></div>
                                    <?php else: ?>
                                    <div class="col-average">-</div>
                                    <?php endif; ?>
                                    <div class="col-students"><?php echo $row['StudentCount']; ?> 人</div>
                                </div>
                                <?php 
                                $rank++;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // 自動獲取位置功能
        document.getElementById('getLocationBtn')?.addEventListener('click', function() {
            if (navigator.geolocation) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 定位中...';
                this.disabled = true;
                
                const statusDiv = document.getElementById('locationStatus');
                statusDiv.innerHTML = '';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        document.getElementById('autoLat').value = lat;
                        document.getElementById('autoLng').value = lng;
                        
                        // 提交表單
                        document.getElementById('autoForm').submit();
                    },
                    function(error) {
                        document.getElementById('getLocationBtn').innerHTML = '<i class="fas fa-map-marker-alt"></i> 取得目前位置';
                        document.getElementById('getLocationBtn').disabled = false;
                        
                        const statusDiv = document.getElementById('locationStatus');
                        statusDiv.innerHTML = '<p class="error-message">無法取得位置：' + error.message + '</p>';
                    }
                );
            } else {
                alert('您的瀏覽器不支援地理定位功能');
            }
        });
    </script>
</body>
</html>

