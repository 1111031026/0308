<?php
session_start();
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "sustain";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("缺少商品ID");
}
$itemID = intval($_GET['id']);

// 先查詢圖片路徑，刪除檔案
$sql = "SELECT ImageURL, PreviewURL FROM merchandise WHERE ItemID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $itemID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (!empty($row['ImageURL']) && file_exists("../" . $row['ImageURL'])) {
        unlink("../" . $row['ImageURL']);
    }
    if (!empty($row['PreviewURL']) && file_exists("../" . $row['PreviewURL'])) {
        unlink("../" . $row['PreviewURL']);
    }
}

// 刪除資料庫紀錄
$sql = "DELETE FROM merchandise WHERE ItemID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $itemID);
if ($stmt->execute()) {
    header("Location: merchandise_manage.php?msg=deleted");
    exit();
} else {
    die("刪除失敗: " . $stmt->error);
}