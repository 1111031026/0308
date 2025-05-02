<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit();
}

// 獲取商品ID
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$user_id = $_SESSION['user_id'] ?? 0;

if ($item_id <= 0 || $user_id <= 0) {
    echo json_encode(['success' => false, 'message' => '無效的請求']);
    exit();
}

// 開始事務
$conn->begin_transaction();

try {
    // 獲取商品信息
    $sql = "SELECT * FROM merchandise WHERE ItemID = ? AND Available = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('商品不存在或已下架');
    }
    
    $product = $result->fetch_assoc();
    $stmt->close();
    
    // 獲取用戶點數
    $sql = "SELECT Points FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('用戶不存在');
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // 檢查點數是否足夠
    if ($user['Points'] < $product['PointsRequired']) {
        throw new Exception('點數不足');
    }
    
    // 更新用戶點數
    $new_points = $user['Points'] - $product['PointsRequired'];
    $sql = "UPDATE users SET Points = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_points, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('更新點數失敗');
    }
    $stmt->close();
    
    // 記錄購買歷史
    $sql = "INSERT INTO purchase_history (UserID, ItemID, PurchaseDate, PointsSpent) VALUES (?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $item_id, $product['PointsRequired']);
    
    if (!$stmt->execute()) {
        throw new Exception('記錄購買歷史失敗');
    }
    $stmt->close();
    
    // 提交事務
    $conn->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // 回滾事務
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();