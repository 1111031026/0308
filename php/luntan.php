<?php
session_start();
include 'db_connect.php'; // 假設您的資料庫連接檔案是 db_connect.php

$article_id = null;
$posts = [];
$error_message = '';
$article_title = '論壇'; // 預設標題

// 從 GET 請求中獲取 ArticleID
if (isset($_GET['article_id'])) {
    $article_id = (int)$_GET['article_id'];

    // 根據 ArticleID 獲取文章標題 (可選，如果需要顯示特定文章的論壇)
    // 假設您有一個 articles 資料表
    $stmt_article = $conn->prepare("SELECT Title FROM article WHERE ArticleID = ?");
    if ($stmt_article) {
        $stmt_article->bind_param("i", $article_id);
        $stmt_article->execute();
        $result_article = $stmt_article->get_result();
        if ($row_article = $result_article->fetch_assoc()) {
            $article_title = htmlspecialchars($row_article['Title']) . " - 討論區";
        }
        $stmt_article->close();
    } else {
        // 處理查詢文章標題失敗的情況，可以記錄錯誤或使用預設標題
    }

    // 查詢與 ArticleID 相關的貼文，並獲取用戶名
    // 修改 SQL 查詢以 JOIN user 資料表
    $stmt = $conn->prepare("SELECT cp.*, u.Username FROM communitypost cp JOIN user u ON cp.UserID = u.UserID WHERE cp.ArticleID = ? ORDER BY cp.PostDate DESC");
    if ($stmt) {
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
        $stmt->close();
    } else {
        $error_message = "無法讀取貼文，請稍後再試。錯誤：" . $conn->error;
    }
} else {
    $error_message = "錯誤：缺少文章 ID。";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $article_title; ?></title>
    <link rel="stylesheet" href="../css/luntan.css">
    <link rel="stylesheet" href="../css/nav.css"> <!-- 假設導航樣式 -->
</head>
<body>
    <?php include 'nav.php'; // 引入導航欄 ?>

    <div class="forum-container">
        <div class="forum-header">
            <h2><?php echo $article_title; ?></h2>
            <div class="forum-actions">
                <!-- <form class="search-form" method="GET" action="luntan.php">
                    <input type="hidden" name="article_id" value="<?php echo htmlspecialchars($article_id); ?>">
                    <input type="text" name="search_query" placeholder="搜尋貼文...">
                    <button type="submit">搜尋</button>
                </form> -->
                <?php if ($article_id): ?>
                <a href="post.php?article_id=<?php echo htmlspecialchars($article_id); ?>" class="new-post-btn">發表貼文</a>
                <?php endif; ?>
                 <a href="article.php?id=<?php echo htmlspecialchars($article_id ?? ''); ?>" class="back-to-article">返回文章</a>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="posts-container">
            <?php if (empty($posts) && empty($error_message)): ?>
                <div class="no-posts">
                    <p>目前還沒有貼文，快來發表第一篇吧！</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div class="user-info">
                                <span class="username"><?php echo htmlspecialchars($post['Username']); ?></span>
                                <!-- <span class="user-status">Student</span> --> <!-- 可根據用戶角色動態顯示 -->
                            </div>
                            <span class="post-date"><?php echo date('Y-m-d H:i', strtotime($post['PostDate'])); ?></span>
                        </div>
                        <h3 class="post-title"><a href="discuss.php?post_id=<?php echo $post['PostID']; ?>"><?php echo htmlspecialchars($post['Title']); ?></a></h3>
                        <div class="post-content">
                            <?php 
                                // 限制內容顯示長度，並添加 "..."
                                $content_preview = mb_substr(strip_tags($post['Content']), 0, 150, 'UTF-8');
                                if (mb_strlen(strip_tags($post['Content']), 'UTF-8') > 150) {
                                    $content_preview .= '...';
                                }
                                echo nl2br(htmlspecialchars($content_preview)); 
                            ?>
                        </div>
                         <a href="discuss.php?post_id=<?php echo $post['PostID']; ?>" class="read-more-btn">閱讀更多</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>