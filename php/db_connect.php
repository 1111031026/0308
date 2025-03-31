<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sustain";  // 修改為正確的資料庫名稱

// 創建連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 設置字符集
$conn->set_charset("utf8mb4");

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}
?>