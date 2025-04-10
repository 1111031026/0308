<?php
header('Content-Type: application/json');

require_once 'db_connect.php';

// 獲取POST數據
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['type']) || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

$type = $data['type'];
$id = intval($data['id']);

// 開始事務
$conn->begin_transaction();

try {
    // 根據題目類型選擇對應的表
    switch ($type) {
        case 'choice':
            $staging_table = 'choicequizstagingarea';
            $target_table = 'choicequiz';
            $sql = "INSERT INTO $target_table (QuestionText, OptionA, OptionB, OptionC, OptionD, CorrectAnswer, UserID, ArticleID) 
                    SELECT QuestionText, OptionA, OptionB, OptionC, OptionD, CorrectAnswer, UserID, ArticleID 
                    FROM $staging_table WHERE QuestionID = ?";
            break;
            
        case 'fill':
            $staging_table = 'fillquizstagingarea';
            $target_table = 'fillquiz';
            $sql = "INSERT INTO $target_table (QuestionText, CorrectAnswer, UserID, ArticleID) 
                    SELECT QuestionText, CorrectAnswer, UserID, ArticleID 
                    FROM $staging_table WHERE QuestionID = ?";
            break;
            
        case 'tf':
            $staging_table = 'tfquizstagingarea';
            $target_table = 'tfquiz';
            $sql = "INSERT INTO $target_table (QuestionText, CorrectAnswer, UserID, ArticleID) 
                    SELECT QuestionText, CorrectAnswer, UserID, ArticleID 
                    FROM $staging_table WHERE QuestionID = ?";
            break;
            
        default:
            throw new Exception('無效的題目類型');
    }
    
    // 準備並執行插入語句
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('準備SQL語句失敗：' . $conn->error);
    }
    
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        throw new Exception('執行插入操作失敗：' . $stmt->error);
    }
    
    // 如果插入成功，刪除暫存區的題目
    $delete_sql = "DELETE FROM $staging_table WHERE QuestionID = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    if (!$delete_stmt) {
        throw new Exception('準備刪除SQL語句失敗：' . $conn->error);
    }
    
    $delete_stmt->bind_param('i', $id);
    if (!$delete_stmt->execute()) {
        throw new Exception('執行刪除操作失敗：' . $delete_stmt->error);
    }
    
    // 提交事務
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => '題目上架成功']);
    
} catch (Exception $e) {
    // 發生錯誤時回滾事務
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    // 關閉資料庫連接
    $conn->close();
}
?>