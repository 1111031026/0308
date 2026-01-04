<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true) {
    header('Location: user_login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$reward_id = isset($_POST['reward_id']) ? intval($_POST['reward_id']) : 0;

if ($reward_id <= 0) {
    $_SESSION['region_setup_error'] = '無效的獎勵編號';
    header('Location: region_leaderboard.php');
    exit;
}

// 檢查獎勵是否存在且屬於當前用戶
$check_sql = "SELECT RewardID, UserID, PointsAwarded, Status 
              FROM monthly_ranking_rewards 
              WHERE RewardID = ? AND UserID = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $reward_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$reward = $check_result->fetch_assoc();
$check_stmt->close();

if (!$reward) {
    $_SESSION['region_setup_error'] = '找不到該獎勵記錄';
    header('Location: region_leaderboard.php');
    exit;
}

if ($reward['Status'] === 'CLAIMED') {
    $_SESSION['region_setup_error'] = '該獎勵已經領取過了';
    header('Location: region_leaderboard.php');
    exit;
}

// 開始事務
$conn->begin_transaction();

try {
    // 更新獎勵狀態
    $update_reward_sql = "UPDATE monthly_ranking_rewards 
                         SET Status = 'CLAIMED', AwardedDate = NOW() 
                         WHERE RewardID = ?";
    $update_reward_stmt = $conn->prepare($update_reward_sql);
    $update_reward_stmt->bind_param("i", $reward_id);
    $update_reward_stmt->execute();
    $update_reward_stmt->close();
    
    // 更新用戶點數
    $user_role = $_SESSION['role'] ?? 'Student';
    $points_table = ($user_role === 'Teacher') ? 'teacher_achievement' : 'achievement';
    $points_awarded = intval($reward['PointsAwarded']);
    
    $update_points_sql = "INSERT INTO {$points_table} (UserID, TotalPoints";
    if ($user_role === 'Student') {
        $update_points_sql .= ", ArticlesViewed, ChoiceQuestionsCorrect, TFQuestionsCorrect, FillinQuestionsCorrect) VALUES (?, ?, 0, 0, 0, 0) ON DUPLICATE KEY UPDATE TotalPoints = TotalPoints + ?";
    } else {
        $update_points_sql .= ", SDG13ArticlesPublished, SDG14ArticlesPublished, SDG15ArticlesPublished) VALUES (?, ?, 0, 0, 0) ON DUPLICATE KEY UPDATE TotalPoints = TotalPoints + ?";
    }
    
    $update_points_stmt = $conn->prepare($update_points_sql);
    if ($user_role === 'Student') {
        $update_points_stmt->bind_param("iii", $user_id, $points_awarded, $points_awarded);
    } else {
        $update_points_stmt->bind_param("iii", $user_id, $points_awarded, $points_awarded);
    }
    $update_points_stmt->execute();
    $update_points_stmt->close();
    
    // 提交事務
    $conn->commit();
    
    $_SESSION['region_setup_success'] = "成功領取 {$points_awarded} 點獎勵！";
    header('Location: region_leaderboard.php?success=1');
    exit;
    
} catch (Exception $e) {
    // 回滾事務
    $conn->rollback();
    $_SESSION['region_setup_error'] = '領取獎勵失敗：' . $e->getMessage();
    header('Location: region_leaderboard.php');
    exit;
}

$conn->close();
?>

