<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sustain";

// 創建連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 設置編碼
$conn->set_charset("utf8mb4");
?>