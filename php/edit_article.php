<?php
session_start();

// 檢查是否為管理員登入
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

include 'db_connect.php';

$error_message = '';
$success_message = '';

// 檢查是否有文章ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_article.php");
    exit();
}

$article_id = intval($_GET['id']);

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $content = trim($_POST['content']);
    $article_url = trim($_POST['article_url']);
    $teacher_summary = trim($_POST['teacher_summary']);
    
    // 基本驗證
    if (empty($title)) {
        $error_message = "標題不能為空";
    } elseif (empty($category)) {
        $error_message = "分類不能為空";
    } elseif (empty($article_url)) {
        $error_message = "文章連結不能為空";
    } else {
        try {
            // 更新文章基本資訊
            $sql = "UPDATE article SET 
                    Title = ?,
                    Category = ?,
                    Description = ?,
                    Content = ?,
                    ArticleURL = ?,
                    teacher_summary = ?
                    WHERE ArticleID = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", 
                $title, 
                $category, 
                $description, 
                $content, 
                $article_url, 
                $teacher_summary,
                $article_id
            );
            
            if ($stmt->execute()) {
                $success_message = "文章更新成功";
            } else {
                $error_message = "更新失敗";
            }
            
        } catch (Exception $e) {
            $error_message = "更新失敗: " . $e->getMessage();
        }
    }
}

// 獲取文章資料
$stmt = $conn->prepare("SELECT * FROM article WHERE ArticleID = ?");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_article.php");
    exit();
}

$article = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯文章</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
    <!-- 引入 TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/your-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
            height: 400
        });
    </script>
</head>
<body>
    
    <div class="admin-container">
        <h1>編輯文章</h1>
        
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST" class="edit-form">
            <div class="form-group">
                <label for="title">標題 *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['Title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="category">分類 *</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($article['Category']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">簡介</label>
                <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($article['Description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="article_url">文章連結 *</label>
                <input type="url" id="article_url" name="article_url" value="<?php echo htmlspecialchars($article['ArticleURL']); ?>" required>
            </div>

            <div class="form-group">
                <label for="content">文章內容</label>
                <textarea id="content" name="content"><?php echo htmlspecialchars($article['Content']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="teacher_summary">老師重點整理</label>
                <textarea id="teacher_summary" name="teacher_summary" rows="4"><?php echo htmlspecialchars($article['teacher_summary']); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="save-btn">保存更改</button>
                <a href="manage_article.php" class="cancel-btn">取消</a>
            </div>
        </form>
    </div>

    <style>
    .edit-form {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: bold;
    }
    .form-group input[type="text"],
    .form-group input[type="url"],
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    .form-actions {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    .save-btn {
        padding: 10px 30px;
        background-color: #2ecc71;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
    }
    .save-btn:hover {
        background-color: #27ae60;
    }
    .cancel-btn {
        padding: 10px 30px;
        background-color: #95a5a6;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
        text-decoration: none;
        display: inline-block;
    }
    .cancel-btn:hover {
        background-color: #7f8c8d;
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