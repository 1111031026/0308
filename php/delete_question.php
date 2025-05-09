<?php
include 'db_connect.php';

// 檢查是否有POST請求和題目ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'])) {
    $question_id = intval($_POST['question_id']);
    
    // 準備刪除語句
    $sql = "DELETE FROM teacher_questions WHERE question_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    
    // 執行刪除操作
    $response = array();
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = '題目已成功刪除';
    } else {
        $response['success'] = false;
        $response['message'] = '刪除題目時發生錯誤';
    }
    
    $stmt->close();
    $conn->close();
    
    // 返回JSON響應
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // 如果不是有效的請求，返回錯誤
    header('Content-Type: application/json');
    echo json_encode(array(
        'success' => false,
        'message' => '無效的請求'
    ));
}