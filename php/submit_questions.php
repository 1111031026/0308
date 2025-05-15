<?php
header('Content-Type: application/json');
include 'db_connect.php';

try {
    // 從teacher_questions表中獲取所有題目
    $sql = "SELECT * FROM teacher_questions";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $question_type = $row['question_type'];
            $content = $row['content'];
            $article_id = $row['article_id'];
            $answer = $row['answer'] ?? null;
            $correct_answer = $row['correct_answer'] ?? null;
            $option_a = $row['option_a'] ?? null;
            $option_b = $row['option_b'] ?? null;
            $option_c = $row['option_c'] ?? null;
            $option_d = $row['option_d'] ?? null;

            // 根據題目類型將資料插入到對應的資料表
            $user_id = $row['userID'];
            switch($question_type) {
                case '選擇題':
                    $sql = "INSERT INTO choicequiz (QuestionText, ArticleID, CorrectAnswer, UserID, OptionA, OptionB, OptionC, OptionD) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    break;
                case '問答題':
                    $sql = "INSERT INTO fillquiz (QuestionText, ArticleID, CorrectAnswer, UserID) VALUES (?, ?, ?, ?)";
                    break;
                case '是非題':
                    $sql = "INSERT INTO tfquiz (QuestionText, ArticleID, CorrectAnswer, UserID) VALUES (?, ?, ?, ?)";
                    break;
                default:
                    throw new Exception("未知的題目類型：" . $question_type);
            }

            $stmt = $conn->prepare($sql);
            if ($question_type === '選擇題') {
                $stmt->bind_param("sisissss", $content, $article_id, $answer, $user_id, $option_a, $option_b, $option_c, $option_d);
            } else if ($question_type === '是非題') {
                $stmt->bind_param("siii", $content, $article_id, $correct_answer, $user_id);
            } else {
                $stmt->bind_param("sisi", $content, $article_id, $answer, $user_id);
            }
            if (!$stmt->execute()) {
                throw new Exception("插入資料失敗：" . $stmt->error);
            }
            $stmt->close();
        }

        // 清空teacher_questions表
        $clearSql = "DELETE FROM teacher_questions";
        if (!$conn->query($clearSql)) {
            throw new Exception("清空題目表失敗：" . $conn->error);
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '沒有找到任何題目']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}