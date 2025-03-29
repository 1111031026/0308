<?php
require_once 'db_connect.php';

// 新增question_type欄位到questions表格
$sql = "ALTER TABLE questions ADD COLUMN IF NOT EXISTS question_type ENUM('選擇題', '是非題', '問答題') NOT NULL DEFAULT '問答題'";

if ($conn->query($sql) === TRUE) {
    echo "成功新增question_type欄位";
} else {
    echo "新增欄位失敗: " . $conn->error;
}

$conn->close();
?>