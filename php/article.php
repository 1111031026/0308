<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站</title>
    <link rel="stylesheet" href="../css/1.css">
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
            <li><a href="#"><img src="../img/share.png" alt="分享" title="分享"></a></li>
            <li><a href="#"><img src="../img/quiz.png" alt="測驗" title="測驗"></a></li>
            <li><a href="2.php"><img src="../img/new-quiz.png" alt="新增測驗" title="新增測驗"></a></li>
        </ul>
    </nav>



    <!-- 文章內容區域 -->
    <div class="content">
        <?php
        require_once 'db_connect.php';

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT Title, Content FROM article WHERE ArticleID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
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