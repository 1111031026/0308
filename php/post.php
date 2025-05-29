<?php
session_start();
include 'db_connect.php'; // 假設您的資料庫連接檔案是 db_connect.php

// 檢查用戶是否登入
if (!isset($_SESSION['user_id'])) {
    // 如果未登入，可以重定向到登入頁面或顯示錯誤訊息
    header('Location: user_login.php'); // 假設登入頁面是 user_login.php
    exit;
}

$user_id = $_SESSION['user_id'];
$article_id = null;
$error_message = '';
$success_message = '';

// 從 GET 請求中獲取 ArticleID
if (isset($_GET['article_id'])) {
    $article_id = $_GET['article_id'];
} else {
    // 如果沒有 ArticleID，可以導回 luntan.php 或顯示錯誤
    // 這裡暫時設置為一個提示，實際應用中應有更完善的處理
    $error_message = "錯誤：缺少文章 ID。";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $article_id) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $error_message = "標題和內容不能為空。";
    } else {
        $post_date = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO communitypost (Title, Content, PostDate, UserID, ArticleID) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssii", $title, $content, $post_date, $user_id, $article_id);
            if ($stmt->execute()) {
                $success_message = "貼文已成功發佈！";
                // 可以選擇重定向到 luntan.php
                // header('Location: luntan.php?article_id=' . $article_id);
                // exit;
            } else {
                $error_message = "發佈失敗，請稍後再試。錯誤：" . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "資料庫錯誤，請稍後再試。錯誤：" . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增貼文</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/post.css">
    <link rel="stylesheet" href="../css/nav3.css"> <!-- 假設導航樣式 -->
</head>
<body>
    <?php include 'nav.php'; // 引入導航欄 ?>
    <div style="height: 70px;"></div>

    <div class="post-container">
        <h2>新增貼文</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div> <!-- 可以為成功訊息添加樣式 -->
        <?php endif; ?>

        <?php if ($article_id): // 只有在有 article_id 時才顯示表單 ?>
        <form class="post-form" method="POST" action="post.php?article_id=<?php echo htmlspecialchars($article_id); ?>">
            <div class="form-group">
                <label for="title">標題</label>
                <input type="text" id="title" name="title" required class="form-control">
            </div>
            <div class="form-group">
                <label for="content">內容</label>
                <textarea id="content" name="content" rows="10" required class="form-control"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="submit-btn">發佈貼文</button>
                <a href="luntan.php?article_id=<?php echo htmlspecialchars($article_id); ?>" class="cancel-btn">取消並返回論壇</a>
            </div>
        </form>
        <?php elseif(empty($error_message)): // 如果沒有 article_id 且沒有其他錯誤訊息 ?>
            <p>無法載入表單，缺少必要的文章資訊。</p>
        <?php endif; ?>
    </div>
</body>
</html>