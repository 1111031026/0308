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
$sql = "SELECT cp.PostID, cp.Title, cp.Content, cp.PostDate, cp.ImageURL, u.Username, u.Status 
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
$sql = "SELECT ca.CommentID, ca.Content, ca.CommentTime, u.Username, u.Status 
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
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['Title']); ?> - 討論區</title>
    <link rel="stylesheet" href="../css/nav2.css">
    <link rel="stylesheet" href="../css/discuss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="discuss-container">
        <!-- 貼文詳情 -->
        <div class="post-detail">
            <h2><?php echo htmlspecialchars($post['Title']); ?></h2>
            <div class="post-meta">
                <span class="username"><?php echo htmlspecialchars($post['Username']); ?></span>
                <span class="user-status"><?php echo htmlspecialchars($post['Status']); ?></span>
                <span class="post-date"><?php echo date('Y-m-d H:i', strtotime($post['PostDate'])); ?></span>
            </div>
            <?php if (!empty($post['ImageURL'])): ?>
                <img src="<?php echo htmlspecialchars($post['ImageURL']); ?>" alt="貼文圖片" class="post-image">
            <?php endif; ?>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['Content'])); ?>
            </div>
            <a href="luntan.php" class="back-btn"><i class="fas fa-arrow-left"></i> 返回論壇</a>
        </div>

        <!-- 評論區 -->
        <div class="comments-section">
            <h3>評論區 (<?php echo mysqli_num_rows($comments); ?>)</h3>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="" class="comment-form">
                    <?php if ($comment_error): ?>
                        <p class="error"><?php echo htmlspecialchars($comment_error); ?></p>
                    <?php endif; ?>
                    <textarea name="comment" placeholder="發表你的評論..." required></textarea>
                    <button type="submit">發表評論</button>
                </form>
            <?php else: ?>
                <div class="login-prompt">
                    <p>請<a href="user_login.php">登入</a>後發表評論</p>
                </div>
            <?php endif; ?>

            <div class="comments-list">
                <?php if (mysqli_num_rows($comments) > 0): ?>
                    <?php while ($comment = mysqli_fetch_assoc($comments)): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <div class="user-info">
                                    <span class="username"><?php echo htmlspecialchars($comment['Username']); ?></span>
                                    <span class="user-status"><?php echo htmlspecialchars($comment['Status']); ?></span>
                                </div>
                                <span class="comment-date"><?php echo date('Y-m-d H:i', strtotime($comment['CommentTime'])); ?></span>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['Content'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-comments">
                        <p>目前還沒有評論，快來發表第一條吧！</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>