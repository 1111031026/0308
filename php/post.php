<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否已登入
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    
    // 驗證輸入
    $errors = [];
    if (empty($title)) {
        $errors[] = '標題不能為空';
    }
    if (empty($content)) {
        $errors[] = '內容不能為空';
    }
    
    // 如果沒有錯誤，保存貼文
    if (empty($errors)) {
        // 使用 Title 欄位，匹配資料表結構
        $sql = "INSERT INTO communitypost (UserID, Title, Content, PostDate) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        
        // 檢查 mysqli_prepare 是否成功
        if ($stmt === false) {
            $errors[] = '資料庫準備語句失敗: ' . mysqli_error($conn);
        } else {
            // 綁定參數，'iss' 表示整數、字串、字串
            mysqli_stmt_bind_param($stmt, 'iss', $user_id, $title, $content);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: luntan.php');
                exit();
            } else {
                $errors[] = '保存失敗: ' . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增貼文</title>
    <link rel="stylesheet" href="../css/nav2.css">
    <link rel="stylesheet" href="../css/post.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="post-container">
        <h2>新增貼文</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="post-form">
            <div class="form-group">
                <label for="title">標題</label>
                <input type="text" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="content">內容</label>
                <textarea id="content" name="content" rows="10" required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">發布貼文</button>
                <a href="luntan.php" class="cancel-btn">取消</a>
            </div>
        </form>
    </div>
</body>
</html>