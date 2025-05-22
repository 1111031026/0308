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
        $msg = '重點整理已儲存！';
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
    <title>重點整理重點整理編輯 - <?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/ai_summary_editor.css">
</head>
<body>
    <header><?php include "nav.php"; ?></header>
    <div class="editor-container">
        <h2>重點整理編輯</h2>
        <form method="POST">
            <div class="section-title">重點整理（可編輯）：</div>
            <div style="color:#888;font-size:14px;margin-bottom:5px;">請以繁體中文撰寫或編輯統整內容，避免使用英文。</div>
            <textarea name="teacher_summary" required><?php echo htmlspecialchars($teacher_summary); ?></textarea>
            <br><br>
            <button type="submit" class="btn-save">儲存</button>
        </form>
        <?php if (!empty($msg)) echo '<div class="msg">' . htmlspecialchars($msg) . '</div>'; ?>
    </div>
</body>
</html>