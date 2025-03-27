<?php
require_once 'db_connect.php';

// 創建文章表
$sql = "CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    content LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "文章表創建成功";
} else {
    echo "創建表失敗: " . $conn->error;
}

$conn->close();
?>