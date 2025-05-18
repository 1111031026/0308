<?php
session_start();
require_once 'db_connect.php';

// --- 更新文章瀏覽次數邏輯 ---
if (
    isset($_GET['id']) &&
    isset($_SESSION['login_session']) && $_SESSION['login_session'] === true &&
    isset($_SESSION['user_id']) &&
    isset($_SESSION['role']) && $_SESSION['role'] !== 'Teacher'
) {
    $articleID = intval($_GET['id']);
    $userID = $_SESSION['user_id'];

    // 檢查是否已有觀看紀錄
    $stmtCheck = $conn->prepare("SELECT 1 FROM user_article_views WHERE UserID = ? AND ArticleID = ?");
    $stmtCheck->bind_param("ii", $userID, $articleID);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        $stmtInsertView = $conn->prepare("INSERT INTO user_article_views (UserID, ArticleID) VALUES (?, ?)");
        $stmtInsertView->bind_param("ii", $userID, $articleID);
        $inserted = $stmtInsertView->execute();
        $stmtInsertView->close();

        if ($inserted) {
            $stmtUpdateAchieve = $conn->prepare(
                "INSERT INTO achievement (UserID, ArticlesViewed, TotalPoints, ChoiceQuestionsCorrect, TFQuestionsCorrect, FillinQuestionsCorrect)
                 VALUES (?, 1, 0, 0, 0, 0)
                 ON DUPLICATE KEY UPDATE ArticlesViewed = ArticlesViewed + 1"
            );
            $stmtUpdateAchieve->bind_param("i", $userID);
            $stmtUpdateAchieve->execute();
            $stmtUpdateAchieve->close();
        } else {
            error_log("Failed to insert view record: UserID $userID, ArticleID $articleID. Error: " . $conn->error);
        }
    }
    $stmtCheck->close();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/1.css">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/article.css">
    <style>
        .article-source {
            background-color: #f8f9fa;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-left: 4px solid #4CAF50;
            font-size: 14px;
        }
        .article-source a {
            color: #2196F3;
            text-decoration: none;
        }
        .article-source a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <?php include "nav.php"; ?>
</header>

<!-- 側邊選單 -->
<nav class="sidebar">
    <ul class="sidebar-links">
        <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
            <?php if ($_SESSION['role'] === 'Teacher'): ?>
                <?php
                $articleID = intval($_GET['id']);
                $currentUserID = $_SESSION['user_id'];
                $isAuthor = false;

                $authorStmt = $conn->prepare("SELECT UserID FROM article WHERE ArticleID = ?");
                $authorStmt->bind_param("i", $articleID);
                $authorStmt->execute();
                $authorResult = $authorStmt->get_result();

                if ($authorRow = $authorResult->fetch_assoc()) {
                    $isAuthor = ($authorRow['UserID'] == $currentUserID);
                }
                $authorStmt->close();
                ?>
                <li><a href="deepseek-test.php?article_id=<?= $articleID; ?>"><img src="../img/new-quiz.svg" alt="新增測驗" title="新增測驗"></a></li>
                <?php if ($isAuthor): ?>
                    <li><a href="ai_summary_editor.php?id=<?= $articleID; ?>"><img src="../img/edit.svg" alt="編輯文章" title="編輯文章"></a></li>
                <?php endif; ?>
                <li><a href="luntan.php?article_id=<?= $articleID; ?>"><img src="../img/share.svg" alt="貼文區" title="貼文區"></a></li>
            <?php elseif ($_SESSION['role'] === 'Admin'): ?>
                <?php
                $articleID = intval($_GET['id']);
                ?>
                <li><a href="deepseek-test.php?article_id=<?= $articleID; ?>"><img src="../img/new-quiz.svg" alt="新增測驗" title="新增測驗"></a></li>
                <li><a href="quiz.php?article_id=<?= $articleID; ?>"><img src="../img/quiz.svg" alt="測驗" title="測驗"></a></li>
                <li><a href="ai_summary_editor.php?id=<?= $articleID; ?>"><img src="../img/edit.svg" alt="編輯文章" title="編輯文章"></a></li>
                <li><a href="luntan.php?article_id=<?= $articleID; ?>"><img src="../img/share.svg" alt="貼文區" title="貼文區"></a></li>
            <?php elseif ($_SESSION['role'] === 'Student'): ?>
                <li><a href="quiz.php?article_id=<?= $_GET['id']; ?>"><img src="../img/quiz.svg" alt="測驗" title="測驗"></a></li>
                <li><a href="luntan.php?article_id=<?= $_GET['id']; ?>"><img src="../img/share.svg" alt="貼文區" title="貼文區"></a></li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</nav>

<!-- 文章內容 -->
<div class="content">
    <?php
    require_once 'article_analyzer.php';

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT Title, Description, teacher_summary, ArticleURL FROM article WHERE ArticleID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo '<div class="article-header">';
            echo '<h1 class="article-title">' . htmlspecialchars($row['Title']) . '</h1>';
            echo '<p class="article-description">' . nl2br(htmlspecialchars($row['Description'])) . '</p>';
            echo '</div>';

            echo '<div class="article-source"><span style="color:black;">文章來源：</span><a href="' . htmlspecialchars($row['ArticleURL']) . '" target="_blank">' . htmlspecialchars($row['ArticleURL']) . '</a></div>';

            if (!empty($row['teacher_summary'])) {
                echo '<div class="article-ai-section">';
                echo '<h3 class="ai-title">AI重點整理</h3>';
                echo '<div class="ai-analysis">' . nl2br(htmlspecialchars($row['teacher_summary'])) . '</div>';
                echo '</div>';
            } else {
                echo '<div class="article-ai-section">';
                echo '<h3 class="ai-title">重點整理</h3>';
                echo '<div class="ai-analysis" style="color:#888;">尚未有重點整理</div>';
                echo '</div>';
            }
        } else {
            echo '<p>找不到該文章</p>';
        }
        $stmt->close();
    } else {
        echo '<p>請選擇要查看的文章</p>';
    }

    $conn->close();
    ?>
</div>
</body>
</html>
