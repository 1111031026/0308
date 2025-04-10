<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章測驗 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/quiz.css">
    <style>
        .quiz-section {
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .quiz-section h2 {
            color: #2196F3;
            margin-bottom: 15px;
        }
        .quiz-item {
            margin-bottom: 20px;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            background-color: white;
        }
        .teacher-info {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        .answer-btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .answer-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="content">
        <?php
        require_once 'db_connect.php';

        if (!isset($_GET['article_id'])) {
            echo '<p>請選擇要查看的文章測驗</p>';
            exit;
        }

        $article_id = intval($_GET['article_id']);

        // 選擇題
        $choice_sql = "SELECT cq.*, u.Username FROM choicequiz cq 
                      JOIN user u ON cq.UserID = u.UserID 
                      WHERE cq.ArticleID = ?";
        $stmt = $conn->prepare($choice_sql);
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $choice_result = $stmt->get_result();

        // 填空題
        $fill_sql = "SELECT fq.*, u.Username FROM fillquiz fq 
                    JOIN user u ON fq.UserID = u.UserID 
                    WHERE fq.ArticleID = ?";
        $stmt = $conn->prepare($fill_sql);
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $fill_result = $stmt->get_result();

        // 是非題
        $tf_sql = "SELECT tq.*, u.Username FROM tfquiz tq 
                  JOIN user u ON tq.UserID = u.UserID 
                  WHERE tq.ArticleID = ?";
        $stmt = $conn->prepare($tf_sql);
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $tf_result = $stmt->get_result();

        // 顯示選擇題
        if ($choice_result->num_rows > 0) {
            echo '<div class="quiz-section">';
            echo '<h2>選擇題</h2>';
            while ($row = $choice_result->fetch_assoc()) {
                echo '<div class="quiz-item">';
                echo '<p>' . htmlspecialchars($row['QuestionText']) . '</p>';
                echo '<p>A. ' . htmlspecialchars($row['OptionA']) . '</p>';
                echo '<p>B. ' . htmlspecialchars($row['OptionB']) . '</p>';
                echo '<p>C. ' . htmlspecialchars($row['OptionC']) . '</p>';
                echo '<p>D. ' . htmlspecialchars($row['OptionD']) . '</p>';
                echo '<div class="teacher-info">出題老師：' . htmlspecialchars($row['Username']) . '</div>';
                echo '<a href="answer.php?question_id=' . $row['choiceID'] . '&type=choice" class="answer-btn">前往作答</a>';
                echo '</div>';
            }
            echo '</div>';
        }

        // 顯示填空題
        if ($fill_result->num_rows > 0) {
            echo '<div class="quiz-section">';
            echo '<h2>填空題</h2>';
            while ($row = $fill_result->fetch_assoc()) {
                echo '<div class="quiz-item">';
                echo '<p>' . htmlspecialchars($row['QuestionText']) . '</p>';
                echo '<div class="teacher-info">出題老師：' . htmlspecialchars($row['Username']) . '</div>';
                echo '<a href="answer.php?question_id=' . $row['fillID'] . '&type=fill" class="answer-btn">前往作答</a>';
                echo '</div>';
            }
            echo '</div>';
        }

        // 顯示是非題
        if ($tf_result->num_rows > 0) {
            echo '<div class="quiz-section">';
            echo '<h2>是非題</h2>';
            while ($row = $tf_result->fetch_assoc()) {
                echo '<div class="quiz-item">';
                echo '<p>' . htmlspecialchars($row['QuestionText']) . '</p>';
                echo '<div class="teacher-info">出題老師：' . htmlspecialchars($row['Username']) . '</div>';
                echo '<a href="answer.php?question_id=' . $row['tfID'] . '&type=fill" class="answer-btn">前往作答</a>';
                echo '</div>';
            }
            echo '</div>';
        }

        if ($choice_result->num_rows === 0 && $fill_result->num_rows === 0 && $tf_result->num_rows === 0) {
            echo '<p>此文章暫無測驗題目</p>';
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>