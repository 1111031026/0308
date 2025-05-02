<?php
session_start();
require_once 'db_connect.php';

// 檢查是否有搜尋關鍵字
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = " WHERE Title LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}

// 獲取所有貼文
$sql = "SELECT cp.PostID, cp.Title, cp.Content, cp.PostDate, cp.ImageURL, u.Username, u.Status 
        FROM communitypost cp 
        JOIN user u ON cp.UserID = u.UserID" . $search_condition . "
        ORDER BY cp.PostDate DESC";

// 執行查詢並檢查錯誤
$result = mysqli_query($conn, $sql);
if (!$result) {
    die('查詢錯誤: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社群論壇</title>
    <link rel="stylesheet" href="../css/nav2.css">
    <link rel="stylesheet" href="../css/luntan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="forum-container">
        <div class="forum-header">
            <h2>社群論壇</h2>
            <div class="forum-actions">
                <form class="search-form" action="" method="GET">
                    <input type="text" name="search" placeholder="搜尋貼文..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="post.php" class="new-post-btn"><i class="fas fa-plus"></i> 新增貼文</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="posts-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div class="user-info">
                                <span class="username"><?php echo htmlspecialchars($row['Username']); ?></span>
                                <span class="user-status"><?php echo htmlspecialchars($row['Status']); ?></span>
                            </div>
                            <span class="post-date"><?php echo date('Y-m-d H:i', strtotime($row['PostDate'])); ?></span>
                        </div>
                        <h3 class="post-title">
                            <a href="discuss.php?post_id=<?php echo $row['PostID']; ?>">
                                <?php echo htmlspecialchars($row['Title']); ?>
                            </a>
                        </h3>
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($row['Content'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-posts">
                    <p>目前還沒有任何貼文。</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <p>成為第一個發表貼文的人！</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/luntanscript.js"></script>
</body>
</html>