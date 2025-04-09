<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站 - 查看所有題目</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/view-all-qusetion.css">
</head>
<body>
    <header>
        <?php
        include "nav.php";
        ?>
    </header>

    <!-- 題目表格 -->
    <div class="table-container">
        <table class="question-table">
            <thead>
                <tr>
                    <th>題目編號</th>
                    <th>內文</th>
                    <th>類型</th>
                    <th>文章標題</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'db_connect.php';
                
                // 從資料庫讀取題目
                $sql = "SELECT tq.*, a.Title FROM teacher_questions tq LEFT JOIN article a ON tq.article_id = a.ArticleID ORDER BY tq.question_id";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . str_pad($row['question_id'], 3, '0', STR_PAD_LEFT) . "</td>";
                        echo "<td>" . htmlspecialchars($row['content']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['question_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Title'] ?? '無對應文章') . "</td>";
                        echo "<td><button class='edit-btn' onclick=\"window.location.href='deepseek-test.php?edit_id=" . $row['question_id'] . "&article_id=" . $row['article_id'] . "'\">返回編輯</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>目前沒有任何題目</td></tr>";
                }
                
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- 控制按鈕 -->
    <div class="control-buttons">
        <button class="control-btn add-new" onclick="window.location.href='deepseek-test.php'">增加新的題目</button>
        <button class="control-btn submit-all" onclick="window.location.href='index.php'">確認送出所有題目</button>
    </div>
</body>
</html>