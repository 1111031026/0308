<?php
session_start();

// 檢查是否為管理員登入
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

include 'db_connect.php';

// 處理文章刪除
if (isset($_POST['delete_article'])) {
    $article_id = intval($_POST['article_id']);
    
    // 刪除文章相關的評論
    $stmt = $conn->prepare("DELETE FROM commentarea WHERE PostID = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    
    // 刪除文章相關的圖片記錄
    $stmt = $conn->prepare("DELETE FROM articleimage WHERE ArticleID = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    
    // 刪除文章瀏覽記錄
    $stmt = $conn->prepare("DELETE FROM user_article_views WHERE ArticleID = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    
    // 最後刪除文章
    $stmt = $conn->prepare("DELETE FROM article WHERE ArticleID = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $success_message = "文章及相關資料已成功刪除";
    } else {
        $error_message = "刪除文章失敗";
    }
}

// 搜索功能
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where_clause = $search ? "WHERE a.Title LIKE '%$search%' OR a.Content LIKE '%$search%' OR u.Username LIKE '%$search%'" : "";

// 分頁設置
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 獲取總文章數
$total_result = $conn->query("
    SELECT COUNT(*) as total 
    FROM article a 
    JOIN user u ON a.UserID = u.UserID 
    $where_clause
");
$total_articles = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_articles / $per_page);

// 獲取文章列表
$sql = "
    SELECT 
        a.ArticleID, 
        a.Title, 
        a.Content,
        a.Description,
        a.Category,
        a.created_at,
        u.Username,
        (SELECT COUNT(*) FROM commentarea WHERE PostID = a.ArticleID) as CommentCount,
        (SELECT COUNT(*) FROM user_article_views WHERE ArticleID = a.ArticleID) as ViewCount,
        (SELECT GROUP_CONCAT(ImageURL) FROM articleimage WHERE ArticleID = a.ArticleID) as Images
    FROM article a 
    JOIN user u ON a.UserID = u.UserID 
    $where_clause 
    ORDER BY a.created_at DESC 
    LIMIT $offset, $per_page
";
$articles = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章管理</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <h1>文章管理</h1>
            <a href="admin_dashboard.php" class="back-btn">返回儀表板</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- 搜索欄 -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="搜索文章標題、內容或作者..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">搜索</button>
            </form>
        </div>

        <!-- 文章列表 -->
        <div class="article-list">
            <?php while ($article = $articles->fetch_assoc()): ?>
            <div class="article-card">
                <div class="article-header">
                    <h3><?php echo htmlspecialchars($article['Title']); ?></h3>
                    <div class="article-meta">
                        <span class="author">作者: <?php echo htmlspecialchars($article['Username']); ?></span>
                        <span class="post-time">發布時間: <?php echo date('Y-m-d H:i', strtotime($article['created_at'])); ?></span>
                        <span class="category">分類: <?php echo htmlspecialchars($article['Category']); ?></span>
                    </div>
                </div>
                
                <?php if ($article['Description']): ?>
                <div class="article-description">
                    <?php echo htmlspecialchars($article['Description']); ?>
                </div>
                <?php endif; ?>
                
                <div class="article-content">
                    <?php 
                    $content = strip_tags($article['Content']);
                    echo htmlspecialchars(strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content); 
                    ?>
                </div>
                
                <?php if ($article['Images']): ?>
                <div class="article-images">
                    <?php 
                    $images = explode(',', $article['Images']);
                    foreach (array_slice($images, 0, 3) as $image): ?>
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="文章圖片">
                    <?php endforeach; ?>
                    <?php if (count($images) > 3): ?>
                        <div class="more-images">+<?php echo count($images) - 3; ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="article-stats">
                    <span title="評論數">💬 <?php echo $article['CommentCount']; ?></span>
                    <span title="瀏覽次數">👁️ <?php echo $article['ViewCount']; ?></span>
                </div>
                
                <div class="article-actions">
                    <a href="edit_article.php?id=<?php echo $article['ArticleID']; ?>" class="edit-btn">編輯</a>
                    <a href="article.php?id=<?php echo $article['ArticleID']; ?>" class="view-btn" target="_blank">查看</a>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('確定要刪除此文章嗎？此操作將同時刪除所有相關的評論、圖片和瀏覽記錄，且無法撤銷。');">
                        <input type="hidden" name="article_id" value="<?php echo $article['ArticleID']; ?>">
                        <button type="submit" name="delete_article" class="delete-btn">刪除</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- 分頁 -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                   class="<?php echo $page === $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .back-btn {
        padding: 8px 15px;
        background-color: #34495e;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.9em;
        transition: background-color 0.3s;
    }

    .back-btn:hover {
        background-color: #2c3e50;
    }

    .article-list {
        margin-top: 20px;
    }
    .article-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 20px;
    }
    .article-header {
        margin-bottom: 15px;
    }
    .article-header h3 {
        margin: 0 0 10px 0;
        color: #2c3e50;
        font-size: 1.4em;
    }
    .article-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        color: #7f8c8d;
        font-size: 0.9em;
    }
    .article-content {
        color: #34495e;
        margin: 15px 0;
        line-height: 1.6;
    }
    .article-images {
        display: flex;
        gap: 10px;
        margin: 15px 0;
        overflow-x: auto;
        padding-bottom: 10px;
    }
    .article-images img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 4px;
    }
    .more-images {
        width: 100px;
        height: 100px;
        background: rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        color: #666;
        font-size: 1.2em;
    }
    .article-stats {
        display: flex;
        gap: 20px;
        color: #7f8c8d;
        font-size: 0.9em;
        margin: 15px 0;
    }
    .article-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    .edit-btn, .view-btn, .delete-btn {
        padding: 6px 15px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9em;
        cursor: pointer;
    }
    .edit-btn {
        background-color: #f39c12;
        color: white;
    }
    .edit-btn:hover {
        background-color: #d68910;
    }
    .view-btn {
        background-color: #3498db;
        color: white;
    }
    .view-btn:hover {
        background-color: #2980b9;
    }
    .delete-btn {
        background-color: #e74c3c;
        color: white;
        border: none;
    }
    .delete-btn:hover {
        background-color: #c0392b;
    }
    .search-section {
        margin-bottom: 20px;
    }
    .search-form {
        display: flex;
        gap: 10px;
        max-width: 500px;
        margin: 0 auto;
    }
    .search-form input[type="text"] {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .search-form button {
        padding: 8px 20px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .search-form button:hover {
        background-color: #2980b9;
    }
    .pagination {
        margin-top: 20px;
        text-align: center;
    }
    .pagination a {
        display: inline-block;
        padding: 8px 12px;
        margin: 0 4px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
    }
    .pagination a.active {
        background-color: #3498db;
        color: white;
        border-color: #3498db;
    }
    .success-message,
    .error-message {
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 20px;
        text-align: center;
    }
    .success-message {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>
</body>
</html> 