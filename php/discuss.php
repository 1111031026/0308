<?php
session_start();
require_once 'db_connect.php';

// 驗證貼文ID
$post_id = isset($_GET['post_id']) && filter_var($_GET['post_id'], FILTER_VALIDATE_INT) ? (int)$_GET['post_id'] : 0;
if ($post_id <= 0) {
    header('Location: luntan.php');
    exit();
}

// 查詢貼文詳情
$sql = "SELECT cp.PostID, cp.Title, cp.Content, cp.PostDate, cp.ImageURL, cp.ArticleID, u.Username, u.Status, u.AvatarURL 
        FROM communitypost cp 
        JOIN user u ON cp.UserID = u.UserID 
        WHERE cp.PostID = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die('貼文查詢準備失敗: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'i', $post_id);
if (!mysqli_stmt_execute($stmt)) {
    die('貼文查詢執行失敗: ' . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 檢查貼文是否存在
if (!$post) {
    header('Location: luntan.php');
    exit();
}

// 處理評論提交
$comment_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $comment = trim($_POST['comment'] ?? '');
    
    if (empty($comment)) {
        $comment_error = '評論內容不能為空';
    } elseif (mb_strlen($comment, 'UTF-8') > 1000) {
        $comment_error = '評論內容不能超過1000字元';
    } else {
        $user_id = $_SESSION['user_id'];
        $sql = "INSERT INTO commentarea (PostID, UserID, Content, CommentTime) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt === false) {
            $comment_error = '評論插入準備失敗: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'iis', $post_id, $user_id, $comment);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: discuss.php?post_id=$post_id");
                exit();
            } else {
                $comment_error = '評論插入失敗: ' . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// 查詢評論列表
$sql = "SELECT ca.CommentID, ca.Content, ca.CommentTime, u.UserID, u.Username, u.Status, u.AvatarURL 
        FROM commentarea ca 
        JOIN user u ON ca.UserID = u.UserID 
        WHERE ca.PostID = ? 
        ORDER BY ca.CommentTime ASC";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die('評論查詢準備失敗: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'i', $post_id);
if (!mysqli_stmt_execute($stmt)) {
    die('評論查詢執行失敗: ' . mysqli_stmt_error($stmt));
}

$comments = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// 獲取文章標題
$article_title = '未知文章';
if (isset($post['ArticleID']) && $post['ArticleID'] > 0) {
    $article_sql = "SELECT Title FROM article WHERE ArticleID = ?";
    $article_stmt = mysqli_prepare($conn, $article_sql);
    mysqli_stmt_bind_param($article_stmt, 'i', $post['ArticleID']);
    mysqli_stmt_execute($article_stmt);
    $article_result = mysqli_stmt_get_result($article_stmt);
    $article_row = mysqli_fetch_assoc($article_result);
    $article_title = $article_row ? $article_row['Title'] : '未知文章';
    mysqli_stmt_close($article_stmt);
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['Title']); ?> - 討論區</title>
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/discuss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* 頭像樣式 */
        .commenter-avatar {
            transition: opacity 0.2s ease, transform 0.2s ease;
            cursor: pointer;
        }
        .commenter-avatar:hover {
            transform: scale(1.1); /* 滑鼠懸停時放大 */
        }
        .commenter-avatar.loading {
            opacity: 0.5; /* 點擊時半透明 */
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="forum-container">
        <div class="navigation-links">
            <?php if (isset($post['ArticleID']) && $post['ArticleID'] > 0): ?>
                <a href="luntan.php?article_id=<?php echo $post['ArticleID']; ?>" class="back-link">
                    <i class="fas fa-arrow-left"></i> 返回「<?php echo htmlspecialchars($article_title); ?>」討論區
                </a>
                <a href="article.php?id=<?php echo $post['ArticleID']; ?>" class="back-link">
                    <i class="fas fa-book"></i> 返回文章
                </a>
            <?php else: ?>
                <a href="luntan.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> 返回論壇
                </a>
            <?php endif; ?>
        </div>

        <div class="post-container">
            <!-- 貼文詳情 -->
            <div class="post-detail">
                <h1 class="post-title"><?php echo htmlspecialchars($post['Title']); ?></h1>
                
                <div class="post-meta">
                    <div class="author-info">
                        <?php if (!empty($post['AvatarURL'])): ?>
                            <img src="../<?php echo htmlspecialchars($post['AvatarURL']); ?>" alt="用戶頭像" class/sound/110466.mp3 class="author-avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 8px;">
                        <?php else: ?>
                            <span class="author-avatar"><i class="fas fa-user-circle"></i></span>
                        <?php endif; ?>
                        <span class="author-name"><?php echo htmlspecialchars($post['Username']); ?></span>
                        <?php if (!empty($post['Status'])): ?>
                            <span class="author-status"><?php echo htmlspecialchars($post['Status']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="post-time">
                        <i class="far fa-clock"></i>
                        <span><?php echo date('Y-m-d H:i', strtotime($post['PostDate'])); ?></span>
                    </div>
                </div>
                
                <?php if (!empty($post['ImageURL'])): ?>
                    <div class="post-image-container">
                        <img src="<?php echo htmlspecialchars($post['ImageURL']); ?>" alt="貼文圖片" class="post-image">
                    </div>
                <?php endif; ?>
                
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['Content'])); ?>
                </div>
            </div>

            <!-- 評論區 -->
            <div class="comments-section">
                <h2 class="section-title">留言區 <span class="comment-count"><?php echo mysqli_num_rows($comments); ?></span></h2>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="comment-form-container">
                        <form method="POST" action="" class="comment-form">
                            <?php if ($comment_error): ?>
                                <div class="error-message"><?php echo htmlspecialchars($comment_error); ?></div>
                            <?php endif; ?>
                            <div class="form-group">
                                <textarea name="comment" placeholder="發表你的留言..." required></textarea>
                                <button type="submit" class="submit-btn">留言</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>請<a href="user_login.php">登入</a>後發表留言</p>
                    </div>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (mysqli_num_rows($comments) > 0): ?>
                        <?php while ($comment = mysqli_fetch_assoc($comments)): ?>
                            <div class="comment-card">
                                <div class="comment-header">
                                    <div class="commenter-info">
                                        <?php
                                        // 根據 Status 動態設置跳轉目標
                                        $achievement_page = $comment['Status'] === 'teacher' ? 'teacher_achievement.php' : 'achievement.php';
                                        ?>
                                        <?php if (!empty($comment['AvatarURL'])): ?>
                                            <a href="<?php echo $achievement_page; ?>?user_id=<?php echo htmlspecialchars($comment['UserID']); ?>" class="avatar-link" data-user-id="<?php echo htmlspecialchars($comment['UserID']); ?>">
                                                <img src="../<?php echo htmlspecialchars($comment['AvatarURL']); ?>" alt="用戶頭像" class="commenter-avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 8px;">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo $achievement_page; ?>?user_id=<?php echo htmlspecialchars($comment['UserID']); ?>" class="avatar-link" data-user-id="<?php echo htmlspecialchars($comment['UserID']); ?>">
                                                <span class="commenter-avatar"><i class="fas fa-user-circle"></i></span>
                                            </a>
                                        <?php endif; ?>
                                        <span class="commenter-name"><?php echo htmlspecialchars($comment['Username']); ?></span>
                                        <?php if (!empty($comment['Status'])): ?>
                                            <span class="commenter-status"><?php echo htmlspecialchars($comment['Status']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-time">
                                        <i class="far fa-clock"></i>
                                        <span><?php echo date('Y-m-d H:i', strtotime($comment['CommentTime'])); ?></span>
                                    </div>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['Content'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-comments">
                            <p>目前還沒有留言，快來發表第一條吧！</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 處理頭像點擊的載入動畫和防抖
        document.querySelectorAll('.avatar-link').forEach(link => {
            let isLocked = false; // 防抖鎖定狀態

            link.addEventListener('click', function(e) {
                e.preventDefault(); // 阻止默認跳轉

                // 如果鎖定中，忽略點擊
                if (isLocked) return;

                // 設置鎖定
                isLocked = true;

                const avatar = this.querySelector('.commenter-avatar');
                if (avatar) {
                    avatar.classList.add('loading'); // 添加載入效果

                    // 200ms 後移除載入效果
                    setTimeout(() => {
                        avatar.classList.remove('loading');
                    }, 200);
                }

                // 300ms 後執行跳轉
                setTimeout(() => {
                    window.location.href = this.href; // 執行跳轉
                }, 300);

                // 500ms 後解鎖，允許下一次點擊
                setTimeout(() => {
                    isLocked = false;
                }, 500);
            });
        });
    </script>
</body>
</html>