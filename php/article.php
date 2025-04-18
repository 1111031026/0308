<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/1.css">
    <link rel="stylesheet" href="../css/nav.css">
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
                    <li><a href="luntan.php"><img src="../img/share.png" alt="新增貼文" title="新增貼文"></a></li>
                    <li><a href="luntan.php"><img src="../img/share.png" alt="貼文區" title="貼文區"></a></li>
                    <li><a href="quiz.php?article_id=<?php echo $_GET['id']; ?>"><img src="../img/quiz.png" alt="測驗" title="測驗"></a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>



    <!-- 文章內容區域 -->
    <div class="content">
        <?php
        require_once 'db_connect.php';

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT Title, Content, ArticleURL FROM article WHERE ArticleID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                echo '<div class="article-source">文章來源：<a href="' . htmlspecialchars($row['ArticleURL']) . '" target="_blank">' . htmlspecialchars($row['ArticleURL']) . '</a></div>';
                echo '<div class="article-content">';
                echo $row['Content'];
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