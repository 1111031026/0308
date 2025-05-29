<?php
// 確保啟動 session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 驗證用戶是否登入且是老師
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true || $_SESSION['role'] !== 'Teacher') {
    header("Location: index.php");
    exit();
}

// 資料庫連接
require_once 'db_connect.php';

// 處理刪除文章請求
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $articleID = $_GET['delete'];
    $userID = $_SESSION['user_id'];
    
    // 確認文章屬於當前用戶
    $checkSql = "SELECT * FROM article WHERE ArticleID = ? AND UserID = ?";
    $checkStmt = $conn->prepare($checkSql);
    if ($checkStmt) {
        $checkStmt->bind_param("ss", $articleID, $userID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            // 文章屬於當前用戶，可以刪除
            $deleteSql = "DELETE FROM article WHERE ArticleID = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            if ($deleteStmt) {
                $deleteStmt->bind_param("s", $articleID);
                $deleteStmt->execute();
                
                // 重定向以避免重複刪除
                header("Location: teacher_articles.php?deleted=1");
                exit();
            }
        }
        $checkStmt->close();
    }
}

// 獲取當前老師的 UserID
$teacherID = $_SESSION['user_id'] ?? null;

// 查詢條件和排序
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// 構建查詢
$sql = "SELECT * FROM article WHERE UserID = ?";
$params = [$teacherID];

// 添加分類過濾
if (!empty($category)) {
    $sql .= " AND Category = ?";
    $params[] = $category;
}

// 添加搜索條件
if (!empty($search)) {
    $sql .= " AND (Title LIKE ? OR Content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// 添加排序
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC";
        break;
    case 'a-z':
        $sql .= " ORDER BY Title ASC";
        break;
    case 'z-a':
        $sql .= " ORDER BY Title DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC";
        break;
}

// 準備分頁
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9; // 每頁文章數
$offset = ($page - 1) * $perPage;

// 獲取總文章數
$countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$stmt = $conn->prepare($countSql);
if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalArticles = $row['COUNT(*)'];
    $totalPages = ceil($totalArticles / $perPage);
    $stmt->close();
} else {
    $totalArticles = 0;
    $totalPages = 0;
}

// 添加分頁限制
$sql .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;

// 執行查詢
$stmt = $conn->prepare($sql);
$articles = [];
if ($stmt) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    $stmt->close();
}

// 獲取所有分類用於過濾
$categorySql = "SELECT DISTINCT Category FROM article WHERE UserID = ?";
$categoryStmt = $conn->prepare($categorySql);
$categories = [];
if ($categoryStmt) {
    $categoryStmt->bind_param('s', $teacherID);
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
    while ($row = $categoryResult->fetch_assoc()) {
        if (!empty($row['Category'])) {
            $categories[] = $row['Category'];
        }
    }
    $categoryStmt->close();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的文章 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/teacher_articles.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <main class="container">
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">
                文章已成功刪除
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <h1>我的文章列表</h1>
            <p>管理您在永續小站發佈的所有文章，包含氣候、海洋及陸域永續相關主題</p>
        </div>

        <div class="filter-controls">
            <div class="filter-categories">
                <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => ''])); ?>" class="category-filter <?php echo empty($category) ? 'active' : ''; ?>">
                    全部
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => $cat])); ?>" class="category-filter <?php echo $category === $cat ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="search-sort">
                <form class="search-input" method="GET" action="">
                    <?php 
                    // 保留其他 GET 參數
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'search' && $key !== 'page') {
                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                        }
                    }
                    ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#a0aec0">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-14a6 6 0 110 12 6 6 0 010-12z" />
                        <path d="M21 21l-5-5" stroke="#a0aec0" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <input type="text" name="search" placeholder="搜尋文章..." value="<?php echo htmlspecialchars($search); ?>">
                </form>

                <form id="sortForm">
                    <?php 
                    // 保留其他 GET 參數
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'sort' && $key !== 'page') {
                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                        }
                    }
                    ?>
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>最新發佈</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>最早發佈</option>
                        <option value="a-z" <?php echo $sort === 'a-z' ? 'selected' : ''; ?>>標題 A-Z</option>
                        <option value="z-a" <?php echo $sort === 'z-a' ? 'selected' : ''; ?>>標題 Z-A</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="articles-container">
            <?php if (empty($articles)): ?>
            <div class="no-articles">
                <h3>您還沒有發佈任何文章</h3>
                <p>前往文章編輯區，創建您的第一篇永續相關文章，分享您的知識與見解。</p>
                <a href="crawler.php" class="create-article-btn">創建新文章</a>
            </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                <div class="article-card">
                    <div class="article-actions">
                        <a href="ai_summary_editor.php?id=<?php echo $article['ArticleID']; ?>" class="action-btn edit-btn" title="編輯文章">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </a>
                        <a href="?delete=<?php echo $article['ArticleID']; ?>" class="action-btn delete-btn" title="刪除文章" onclick="return confirm('確定要刪除這篇文章嗎？');">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </a>
                    </div>
                    <div class="article-image" style="background-image: url('../<?php echo !empty($article['ImageURL']) ? htmlspecialchars($article['ImageURL']) : 'img/default-article.jpg'; ?>')"></div>
                    <div class="article-content">
                        <?php
                        $categoryClass = '';
                        switch (strtolower($article['Category'] ?? '')) {
                            case 'climate':
                            case '氣候永續':
                                $categoryClass = 'category-climate';
                                break;
                            case 'ocean':
                            case '海洋永續':
                                $categoryClass = 'category-ocean';
                                break;
                            case 'land':
                            case 'landscape':
                            case '陸域永續':
                                $categoryClass = 'category-land';
                                break;
                            default:
                                $categoryClass = 'category-climate';
                        }
                        ?>
                        <span class="article-category <?php echo $categoryClass; ?>"><?php echo htmlspecialchars($article['Category'] ?? '未分類'); ?></span>
                        <h3 class="article-title"><?php echo htmlspecialchars($article['Title'] ?? '無標題'); ?></h3>
                        <p class="article-excerpt">
                            <?php 
                            // 優先使用簡介，如果沒有則使用內容
                            if (!empty($article['Description'])) {
                                echo htmlspecialchars(mb_substr($article['Description'], 0, 150, 'UTF-8')) . (mb_strlen($article['Description'], 'UTF-8') > 150 ? '...' : '');
                            } else {
                                echo htmlspecialchars(mb_substr(strip_tags($article['Content'] ?? ''), 0, 150, 'UTF-8')) . '...';
                            }
                            ?>
                        </p>
                        <div class="article-footer">
                            <span class="article-date">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <?php echo date('Y-m-d', strtotime($article['created_at'] ?? 'now')); ?>
                            </span>
                            <div class="article-actions-footer">
                                <a href="article_questions.php?article_id=<?php echo $article['ArticleID']; ?>" class="article-questions-btn" title="查看題目">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    題目
                                </a>
                                <a href="article.php?id=<?php echo $article['ArticleID']; ?>" class="article-read-more">
                                    閱讀文章
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])); ?>" class="pagination-btn prev <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                上一頁
            </a>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => min($totalPages, $page + 1)])); ?>" class="pagination-btn next <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                下一頁
            </a>
        </div>
        <?php endif; ?>
    </main>

    <script>
        // 自動提交搜尋表單
        document.querySelector('.search-input input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('.search-input').submit();
            }
        });
    </script>
</body>
</html> 