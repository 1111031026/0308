<?php
require_once 'db_connect.php';

// SQL 語句用於創建 user_article_views 表
$sql = "CREATE TABLE IF NOT EXISTS user_article_views (
    UserID INT NOT NULL,
    ArticleID INT NOT NULL,
    ViewTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (UserID, ArticleID),
    FOREIGN KEY (UserID) REFERENCES user(UserID) ON DELETE CASCADE,
    FOREIGN KEY (ArticleID) REFERENCES article(ArticleID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// 執行 SQL 語句
if ($conn->query($sql) === TRUE) {
    echo "資料表 'user_article_views' 檢查或建立成功。<br>";
} else {
    echo "建立資料表時發生錯誤: " . $conn->error . "<br>";
}

// 檢查 article 表是否存在 ArticleID 欄位 (用於外鍵約束)
$check_article_sql = "SHOW COLUMNS FROM article LIKE 'ArticleID'";
$result_article = $conn->query($check_article_sql);
if ($result_article->num_rows == 0) {
    echo "警告：'article' 資料表中缺少 'ArticleID' 欄位，外鍵約束可能無法正確建立或運作。<br>";
} else {
    // 額外檢查 ArticleID 是否為主鍵或具有唯一索引，這對於外鍵是必需的
    $check_key_sql = "SHOW INDEX FROM article WHERE Key_name = 'PRIMARY' AND Column_name = 'ArticleID'";
    $result_key = $conn->query($check_key_sql);
    if ($result_key->num_rows == 0) {
        echo "警告：'article' 資料表中的 'ArticleID' 欄位不是主鍵，外鍵約束可能無法正確建立或運作。<br>";
    }
}

// 檢查 user 表是否存在 UserID 欄位 (用於外鍵約束)
$check_user_sql = "SHOW COLUMNS FROM user LIKE 'UserID'";
$result_user = $conn->query($check_user_sql);
if ($result_user->num_rows == 0) {
    echo "警告：'user' 資料表中缺少 'UserID' 欄位，外鍵約束可能無法正確建立或運作。<br>";
}

$conn->close();

echo "請注意：您需要手動執行此 PHP 檔案 (例如透過瀏覽器訪問它) 來建立資料表。";
?>
