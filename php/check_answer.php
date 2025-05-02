<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'answer_similarity.php';

// 獲取POST數據
$data = json_decode(file_get_contents('php://input'), true);
$questionId = intval($data['questionId']);
$questionType = $data['questionType'];
$userAnswer = $data['answer'];

session_start(); // 確保 session 已啟動
$userId = $_SESSION['user_id'] ?? 0; // 從session中獲取用戶ID

// 檢查用戶是否登入
if ($userId === 0) {
    echo json_encode(['correct' => false, 'explanation' => '錯誤：用戶未登入或Session已過期。']);
    exit;
}

$response = ['correct' => false, 'explanation' => ''];

// 記錄答題歷史並更新成就的函數
function recordAnswer($userId, $questionId, $questionType, $userAnswer, $isCorrect) {
    global $conn;
    $table = $questionType . 'rec';
    $idField = $questionType . 'ID';

    // 插入答題記錄
    $insertSql = "INSERT INTO $table (UserID, $idField, UserAnswer, isCorrect, FinishTime) VALUES (?, ?, ?, ?, NOW())";
    $stmtRec = $conn->prepare($insertSql);
    if (!$stmtRec) {
        error_log("Prepare insert record failed: " . $conn->error);
        return; // 準備失敗，提前返回
    }
    $stmtRec->bind_param('iisi', $userId, $questionId, $userAnswer, $isCorrect);
    $executed = $stmtRec->execute();
    $stmtRec->close();

    if (!$executed) {
        error_log("Execute insert record failed: " . $stmtRec->error);
        return; // 執行失敗，提前返回
    }

    // 如果答案正確，更新成就表
    if ($isCorrect) {
        $choiceIncrement = ($questionType === 'choice') ? 1 : 0;
        $tfIncrement = ($questionType === 'tf') ? 1 : 0;
        $fillinIncrement = ($questionType === 'fill') ? 1 : 0;
        $pointsIncrement = 5; // 每答對一題加 5 分

        $updateAchieveSql = "INSERT INTO achievement (UserID, TotalPoints, ChoiceQuestionsCorrect, TFQuestionsCorrect, FillinQuestionsCorrect, ArticlesViewed) " .
                            "VALUES (?, ?, ?, ?, ?, 0) " .
                            "ON DUPLICATE KEY UPDATE " .
                            "TotalPoints = TotalPoints + ?, " .
                            "ChoiceQuestionsCorrect = ChoiceQuestionsCorrect + ?, " .
                            "TFQuestionsCorrect = TFQuestionsCorrect + ?, " .
                            "FillinQuestionsCorrect = FillinQuestionsCorrect + ?";

        $stmtAchieve = $conn->prepare($updateAchieveSql);
        if (!$stmtAchieve) {
            error_log("Prepare update achievement failed: " . $conn->error);
            return; // 準備失敗，提前返回
        }
        // For INSERT part: UserID, InitialPoints, InitialChoice, InitialTF, InitialFillin
        // For UPDATE part: PointsIncrement, ChoiceIncrement, TFIncrement, FillinIncrement
        $stmtAchieve->bind_param('iiiiiiiii', $userId, $pointsIncrement, $choiceIncrement, $tfIncrement, $fillinIncrement, $pointsIncrement, $choiceIncrement, $tfIncrement, $fillinIncrement);
        if (!$stmtAchieve->execute()) {
            error_log("Execute update achievement failed for UserID $userId: " . $stmtAchieve->error);
        }
        $stmtAchieve->close();
    }
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
        
        // 記錄答案和是否正確
        recordAnswer($userId, $questionId, $questionType, $userAnswer, $response['correct']);
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
            } else {
                // 如果不完全相同，使用DeepSeek API判斷答案相似度
                $api_key = 'sk-9483cae29d5644318c39537d786410f7';
                if (checkAnswerSimilarity($userAnswer, $row['CorrectAnswer'])) {
                    $response['correct'] = true;
                }
            }
        }
        
        // 記錄答案和是否正確
        recordAnswer($userId, $questionId, $questionType, $userAnswer, $response['correct']);
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
            }
        }
        
        // 記錄答案和是否正確
        recordAnswer($userId, $questionId, $questionType, $userAnswer, $response['correct']);
        break;
}

echo json_encode($response);