<?php
session_start();
require_once 'db_connect.php';

// 僅允許老師進入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: user_login.php');
    exit;
}

// 取得文章ID
$articleID = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($articleID <= 0) {
    echo '無效的文章ID';
    exit;
}

// 取得原始AI統整內容與爬蟲內容
$stmt = $conn->prepare("SELECT Title, Content, teacher_summary FROM article WHERE ArticleID = ?");
$stmt->bind_param("i", $articleID);
$stmt->execute();
$result = $stmt->get_result();
if (!$row = $result->fetch_assoc()) {
    echo '找不到該文章';
    exit;
}
$title = $row['Title'];
$content = $row['Content'];
$teacher_summary = $row['teacher_summary'] ?? '';
$stmt->close();

// 儲存AI統整內容
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_summary'])) {
    $new_summary = $_POST['teacher_summary'];
    $stmt = $conn->prepare("UPDATE article SET teacher_summary = ? WHERE ArticleID = ?");
    $stmt->bind_param("si", $new_summary, $articleID);
    if ($stmt->execute()) {
        $teacher_summary = $new_summary;
        $msg = 'AI統整內容已儲存！';
    } else {
        $msg = '儲存失敗：' . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>AI統整內容編輯 - <?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/achievement.css">
    <style>
        .editor-container { max-width: 800px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #eee; }
        textarea { width: 100%; min-height: 200px; padding: 10px; font-size: 16px; border-radius: 4px; border: 1px solid #ccc; }
        .btn-save { padding: 10px 30px; background: #4CAF50; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn-save:hover { background: #388e3c; }
        .msg { color: #2196F3; margin-bottom: 10px; }
        .section-title { font-weight: bold; margin-top: 20px; }
        .raw-content { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 20px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <header><?php include "nav.php"; ?></header>
    <div class="editor-container">
        <h2>AI統整內容編輯</h2>
        <form method="POST">
            <div class="section-title">AI統整內容（可編輯）：</div>
            <textarea name="teacher_summary" required><?php echo htmlspecialchars($teacher_summary); ?></textarea>
            <br><br>
            <button type="submit" class="btn-save">儲存</button>
        </form>
        <?php if (!empty($msg)) echo '<div class="msg">' . htmlspecialchars($msg) . '</div>'; ?>
    </div>
</body>
</html>