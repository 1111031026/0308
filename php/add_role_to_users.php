<?php
require_once 'db_connect.php';

// 添加role欄位到users表格
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('teacher', 'student') NOT NULL DEFAULT 'student'";

if ($conn->query($sql) === TRUE) {
    echo "成功添加role欄位到users表格";
} else {
    echo "添加欄位失敗: " . $conn->error;
}

$conn->close();
?>