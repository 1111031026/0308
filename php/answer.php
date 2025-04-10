<?php
require_once 'db_connect.php';

if (!isset($_GET['question_id']) || !isset($_GET['type'])) {
    echo '<p>參數錯誤</p>';
    exit;
}

$question_id = intval($_GET['question_id']);
$type = $_GET['type'];

// 處理答案提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = 1; // 這裡應該是從session中獲取已登錄用戶的ID
    $answer = $_POST['answer'];
    
    switch($type) {
        case 'choice':
            $sql = "INSERT INTO choicerec (UserID, choiceID, UserAnswer) VALUES (?, ?, ?)";
            break;
        case 'fill':
            $sql = "INSERT INTO fillrec (UserID, fillID, UserAnswer) VALUES (?, ?, ?)";
            break;
        case 'tf':
            $sql = "INSERT INTO tfrec (UserID, tfID, UserAnswer) VALUES (?, ?, ?)";
            break;
        default:
            echo '<p>題型錯誤</p>';
            exit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $question_id, $answer);
    
    if ($stmt->execute()) {
        echo '<script>alert("答案已提交！"); window.location.href = "quiz.php";</script>';
    } else {
        echo '<script>alert("提交失敗，請重試");</script>';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>答題 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/answer.css">
    <style>
        .answer-section {
            max-width: 800px;
            margin: 120px auto 20px; /* 增加上邊距到120px確保不被nav擋住 */
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            position: relative;
            z-index: 1;
        }
        .question-text {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .answer-form {
            margin-top: 20px;
        }
        .answer-option {
            margin: 10px 0;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="answer-section">
        <?php
        // 獲取題目內容
        switch($type) {
            case 'choice':
                $sql = "SELECT * FROM choicequiz WHERE choiceID = ?";
                break;
            case 'fill':
                $sql = "SELECT * FROM fillquiz WHERE fillID = ?";
                break;
            case 'tf':
                $sql = "SELECT * FROM tfquiz WHERE tfID = ?";
                break;
            default:
                echo '<p>題型錯誤</p>';
                exit;
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo '<div class="question-text">' . htmlspecialchars($row['QuestionText']) . '</div>';
            
            echo '<form class="answer-form" method="POST">';
            
            switch($type) {
                case 'choice':
                    echo '<div class="answer-option"><input type="radio" name="answer" value="A" required> A. ' . htmlspecialchars($row['OptionA']) . '</div>';
                    echo '<div class="answer-option"><input type="radio" name="answer" value="B" required> B. ' . htmlspecialchars($row['OptionB']) . '</div>';
                    echo '<div class="answer-option"><input type="radio" name="answer" value="C" required> C. ' . htmlspecialchars($row['OptionC']) . '</div>';
                    echo '<div class="answer-option"><input type="radio" name="answer" value="D" required> D. ' . htmlspecialchars($row['OptionD']) . '</div>';
                    break;
                case 'fill':
                    echo '<div class="answer-option"><input type="text" name="answer" required placeholder="請輸入您的答案" style="width: 100%; padding: 8px;"></div>';
                    break;
                case 'tf':
                    echo '<div class="answer-option"><input type="radio" name="answer" value="1" required> 是</div>';
                    echo '<div class="answer-option"><input type="radio" name="answer" value="0" required> 否</div>';
                    break;
            }
            
            echo '<button type="submit" class="submit-btn">提交答案</button>';
            echo '</form>';
        } else {
            echo '<p>找不到此題目</p>';
        }
        
        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>