<?php
session_start();
include 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true || !isset($_SESSION['user_id'])) {
    // 如果未登入，重定向到登入頁面
    header('Location: user_login.php');
    exit;
}

// 初始化分類數組
$categories = [
    'sdg13' => ['name' => '氣候永續', 'posts' => []],
    'sdg14' => ['name' => '海洋永續', 'posts' => []],
    'sdg15' => ['name' => '陸域永續', 'posts' => []]
];

// 查詢所有貼文並關聯用戶名、身分和頭像
$sql = "SELECT cp.*, u.Username, u.Status, u.AvatarURL, a.Category, a.Title as ArticleTitle 
        FROM communitypost cp 
        JOIN user u ON cp.UserID = u.UserID 
        LEFT JOIN article a ON cp.ArticleID = a.ArticleID 
        ORDER BY cp.PostDate DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $category = $row['Category'] ?? '';
        // 確保類別是有效的
        if (!isset($categories[$category])) {
            continue;
        }
        $categories[$category]['posts'][] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>全部論壇 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/all_forums.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="forums-container">
        <div class="forums-header">
            <h1>全部論壇</h1>
            <p>探索各類永續發展議題的討論</p>
        </div>

        <div class="category-tabs">
            <button class="tab-btn active" data-category="all">全部</button>
            <button class="tab-btn" data-category="sdg13"><?php echo $categories['sdg13']['name']; ?></button>
            <button class="tab-btn" data-category="sdg14"><?php echo $categories['sdg14']['name']; ?></button>
            <button class="tab-btn" data-category="sdg15"><?php echo $categories['sdg15']['name']; ?></button>
        </div>

        <?php foreach ($categories as $categoryKey => $category): ?>
            <div class="category-section" id="<?php echo $categoryKey; ?>-section">
                <h2 class="category-title"><?php echo $category['name']; ?></h2>
                
                <div class="posts-container">
                    <?php if (empty($category['posts'])): ?>
                        <div class="no-posts">
                            <p>目前還沒有<?php echo $category['name']; ?>相關的貼文</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($category['posts'] as $post): ?>
                            <div class="post-card" data-category="<?php echo $categoryKey; ?>">
                                <div class="post-header">
                                    <div class="user-info">
                                        <?php if (!empty($post['AvatarURL'])): ?>
                                            <img src="../<?php echo htmlspecialchars($post['AvatarURL']); ?>" alt="用戶頭像" class="user-avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 8px;">
                                        <?php else: ?>
                                            <div class="default-avatar" style="width: 30px; height: 30px; border-radius: 50%; background: #6aafc7; display: flex; align-items: center; justify-content: center; margin-right: 8px; overflow: hidden;">
                                                <span style="font-size: 0.9rem; color: white;">👤</span>
                                            </div>
                                        <?php endif; ?>
                                        <span class="username"><?php echo htmlspecialchars($post['Username']); ?></span>
                                        <?php if (!empty($post['Status'])): ?>
                                            <span class="user-status" style="background-color: #e6f7ff; color: #1890ff; padding: 2px 8px; border-radius: 12px; font-size: 12px;"><?php echo htmlspecialchars($post['Status']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="post-date"><?php echo date('Y-m-d H:i', strtotime($post['PostDate'])); ?></span>
                                </div>
                                <h3 class="post-title">
                                    <a href="discuss.php?post_id=<?php echo $post['PostID']; ?>"><?php echo htmlspecialchars($post['Title']); ?></a>
                                </h3>
                                <div class="article-reference">
                                    <?php if (isset($post['ArticleTitle']) && !empty($post['ArticleTitle'])): ?>
                                        <span>文章: </span>
                                        <a href="article.php?id=<?php echo $post['ArticleID']; ?>"><?php echo htmlspecialchars($post['ArticleTitle']); ?></a>
                                    <?php else: ?>
                                        <span>獨立討論</span>
                                    <?php endif; ?>
                                </div>
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
        <?php endforeach; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 初始顯示全部貼文
            showCategory('all');
            
            // 為分類標籤添加點擊事件
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // 移除所有按鈕的active類
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    // 為當前按鈕添加active類
                    this.classList.add('active');
                    // 顯示對應分類的貼文
                    showCategory(this.dataset.category);
                });
            });
        });

        // 顯示指定分類的貼文
        function showCategory(category) {
            const allPosts = document.querySelectorAll('.post-card');
            
            if (category === 'all') {
                // 顯示所有分類區塊
                document.querySelectorAll('.category-section').forEach(section => {
                    section.style.display = 'block';
                });
            } else {
                // 隱藏所有分類區塊，只顯示選中的
                document.querySelectorAll('.category-section').forEach(section => {
                    section.style.display = 'none';
                });
                document.getElementById(category + '-section').style.display = 'block';
            }
        }
    </script>
</body>
</html>