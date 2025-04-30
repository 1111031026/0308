<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'answer_similarity.php';

// 獲取POST數據
$data = json_decode(file_get_contents('php://input'), true);
$questionId = intval($data['questionId']);
$questionType = $data['questionType'];
$userAnswer = $data['answer'];
$userId = 1; // 這裡應該從session中獲取用戶ID

$response = ['correct' => false, 'explanation' => ''];

// 記錄答題歷史的函數
function recordAnswer($userId, $questionId, $questionType, $userAnswer) {
    global $conn;
    $table = $questionType . 'rec';
    $idField = $questionType . 'ID';
    
    $insertSql = "INSERT INTO $table (UserID, $idField, UserAnswer, FinishTime) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param('iis', $userId, $questionId, $userAnswer);
    $stmt->execute();
}

// 根據題型處理答案
switch($questionType) {
    case 'choice':
        $sql = "SELECT CorrectAnswer FROM choicequiz WHERE choiceID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row && strtoupper($userAnswer) === strtoupper($row['CorrectAnswer'])) {
            $response['correct'] = true;
            $response['explanation'] = '答對了！';
        } else {
            $response['explanation'] = '答錯了！';
        }
        
        // 記錄答案
        recordAnswer($userId, $questionId, $questionType, $userAnswer);
        break;
        
    case 'fill':
        $sql = "SELECT CorrectAnswer FROM fillquiz WHERE fillID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            // 先進行嚴格比對
            if (trim($userAnswer) === trim($row['CorrectAnswer'])) {
                $response['correct'] = true;
                $response['explanation'] = '答對了！';
            } else {
                // 如果不完全相同，使用DeepSeek API判斷答案相似度
                $api_key = 'sk-9483cae29d5644318c39537d786410f7';
                if (checkAnswerSimilarity($userAnswer, $row['CorrectAnswer'])) {
                    $response['correct'] = true;
                    $response['explanation'] = '答對了！';
                } else {
                    $response['explanation'] = '答錯了！';
                }
            }
        }
        
        // 記錄答案
        recordAnswer($userId, $questionId, $questionType, $userAnswer);
        break;
        
    case 'tf':
        $sql = "SELECT CorrectAnswer FROM tfquiz WHERE tfID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            $correctAnswer = $row['CorrectAnswer'] ? 'T' : 'F';
            if ($userAnswer === $correctAnswer) {
                $response['correct'] = true;
                $response['explanation'] = '答對了！';
            } else {
                $response['explanation'] = '答錯了！';
            }
        }
        
        // 記錄答案
        recordAnswer($userId, $questionId, $questionType, $userAnswer);
        break;
}

echo json_encode($response);