<?php
session_start(); // 確保 session 已啟動
require_once 'db_connect.php'; // 確保引入資料庫連接

// --- 更新文章瀏覽次數邏輯 ---
if (isset($_GET['id']) && isset($_SESSION['login_session']) && $_SESSION['login_session'] === true && isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] !== 'Teacher') {
    $articleID = intval($_GET['id']);
    $userID = $_SESSION['user_id'];

    // 檢查 user_article_views 表中是否已存在記錄
    $stmtCheck = $conn->prepare("SELECT 1 FROM user_article_views WHERE UserID = ? AND ArticleID = ?");
    $stmtCheck->bind_param("ii", $userID, $articleID);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        // 如果不存在記錄，則插入新記錄到 user_article_views
        $stmtInsertView = $conn->prepare("INSERT INTO user_article_views (UserID, ArticleID) VALUES (?, ?)");
        $stmtInsertView->bind_param("ii", $userID, $articleID);
        $inserted = $stmtInsertView->execute();
        $stmtInsertView->close();

        if ($inserted) {
            // 同時更新 achievement 表中的 ArticlesViewed
            // 使用 INSERT ... ON DUPLICATE KEY UPDATE 確保 achievement 記錄存在
            $stmtUpdateAchieve = $conn->prepare(
                "INSERT INTO achievement (UserID, ArticlesViewed, TotalPoints, ChoiceQuestionsCorrect, TFQuestionsCorrect, FillinQuestionsCorrect) " .
                "VALUES (?, 1, 0, 0, 0, 0) " .
                "ON DUPLICATE KEY UPDATE ArticlesViewed = ArticlesViewed + 1"
            );
            $stmtUpdateAchieve->bind_param("i", $userID);
            $stmtUpdateAchieve->execute();
            $stmtUpdateAchieve->close();
        } else {
            // 記錄錯誤，如果插入 user_article_views 失敗
            error_log("Failed to insert view record for UserID: " . $userID . ", ArticleID: " . $articleID . " Error: " . $conn->error);
        }
    }
    $stmtCheck->close();
}
// --- 更新結束 ---

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
        <?php
        include "nav.php";
        ?>
    </header>
    <!-- 固定懸浮選單 -->
    <nav class="sidebar">
        <ul class="sidebar-links">
            <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Teacher'): ?>
                    <li><a href="deepseek-test.php?article_id=<?php echo $_GET['id']; ?>"><img src="../img/new-quiz.png" alt="新增測驗" title="新增測驗"></a></li>
                    <li><a href="luntan.php"><img src="../img/share.png" alt="貼文區" title="貼文區"></a></li>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Student'): ?>
                    <li><a href="quiz.php?article_id=<?php echo $_GET['id']; ?>"><img src="../img/quiz.png" alt="測驗" title="測驗"></a></li>
                    <li><a href="luntan.php"><img src="../img/share.png" alt="貼文區" title="貼文區"></a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>



    <!-- 文章內容區域 -->
    <div class="content">
        <?php
        require_once 'db_connect.php';
        require_once 'article_analyzer.php';

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT Title, Description, Content, ArticleURL FROM article WHERE ArticleID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // 頂部：標題與簡介
                echo '<div class="article-header">';
                echo '<h1 class="article-title">' . htmlspecialchars($row['Title']) . '</h1>';
                echo '<p class="article-description">' . nl2br(htmlspecialchars($row['Description'])) . '</p>';
                echo '</div>';
                
                echo '<div class="article-source"><span style="color :black;">文章來源：</span><a href="' . htmlspecialchars($row['ArticleURL']) . '" target="_blank">' . htmlspecialchars($row['ArticleURL']) . '</a></div>';

                // 中間：AI重點整理
                $analysis = analyzeArticle($row['Content']);
                echo '<div class="article-ai-section">';
                echo '<h3 class="ai-title">AI重點整理</h3>';
                if (isset($analysis['error'])) {
                    echo '<div class="error">分析過程出現錯誤：' . htmlspecialchars($analysis['error']) . '</div>';
                } else {
                    echo '<div class="ai-analysis">';
                    echo nl2br(htmlspecialchars($analysis['choices'][0]['message']['content']));
                    echo '</div>';
                }
                echo '</div>';

                
                
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