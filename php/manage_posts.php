<?php
session_start();

// 檢查是否為管理員登入
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

include 'db_connect.php';

// 處理文章刪除
if (isset($_POST['delete_post'])) {
    $post_id = intval($_POST['post_id']);
    $stmt = $conn->prepare("DELETE FROM communitypost WHERE PostID = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $success_message = "文章已成功刪除";
    } else {
        $error_message = "刪除文章失敗";
    }
}

// 搜索功能
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where_clause = $search ? "WHERE cp.Title LIKE '%$search%' OR cp.Content LIKE '%$search%' OR u.Username LIKE '%$search%'" : "";

// 分頁設置
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 獲取總文章數
$total_result = $conn->query("
    SELECT COUNT(*) as total 
    FROM communitypost cp 
    JOIN user u ON cp.UserID = u.UserID 
    $where_clause
");
$total_posts = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $per_page);

// 獲取文章列表
$sql = "
    SELECT cp.PostID, cp.Title, cp.Content, cp.PostDate, cp.ImageURL, 
           u.Username, u.Status, 
           (SELECT COUNT(*) FROM comments WHERE PostID = cp.PostID) as CommentCount 
    FROM communitypost cp 
    JOIN user u ON cp.UserID = u.UserID 
    $where_clause 
    ORDER BY cp.PostDate DESC 
    LIMIT $offset, $per_page
";
$posts = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章管理</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="admin-container">
        <h1>文章管理</h1>
        
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
        <div class="posts-grid">
            <?php while ($post = $posts->fetch_assoc()): ?>
            <div class="post-card">
                <div class="post-header">
                    <h3><?php echo htmlspecialchars($post['Title']); ?></h3>
                    <span class="post-date"><?php echo date('Y-m-d H:i', strtotime($post['PostDate'])); ?></span>
                </div>
                <div class="post-content">
                    <?php 
                    $content = strip_tags($post['Content']);
                    echo htmlspecialchars(strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content); 
                    ?>
                </div>
                <div class="post-footer">
                    <div class="post-info">
                        <span class="author">作者: <?php echo htmlspecialchars($post['Username']); ?></span>
                        <span class="comments">評論: <?php echo $post['CommentCount']; ?></span>
                    </div>
                    <div class="post-actions">
                        <a href="discuss.php?post_id=<?php echo $post['PostID']; ?>" class="view-btn" target="_blank">查看</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('確定要刪除此文章嗎？此操作無法撤銷。');">
                            <input type="hidden" name="post_id" value="<?php echo $post['PostID']; ?>">
                            <button type="submit" name="delete_post" class="delete-btn">刪除</button>
                        </form>
                    </div>
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
    .posts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .post-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 15px;
        display: flex;
        flex-direction: column;
    }
    .post-header {
        margin-bottom: 10px;
    }
    .post-header h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.2em;
    }
    .post-date {
        color: #7f8c8d;
        font-size: 0.9em;
    }
    .post-content {
        color: #34495e;
        margin-bottom: 15px;
        flex-grow: 1;
    }
    .post-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }
    .post-info {
        font-size: 0.9em;
        color: #7f8c8d;
    }
    .post-info span {
        margin-right: 10px;
    }
    .post-actions {
        display: flex;
        gap: 10px;
    }
    .view-btn {
        background-color: #3498db;
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        text-decoration: none;
        font-size: 0.9em;
    }
    .view-btn:hover {
        background-color: #2980b9;
    }
    .delete-btn {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 0.9em;
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
    </style>
</body>
</html> 