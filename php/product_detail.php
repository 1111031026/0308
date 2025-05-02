<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true) {
    header("Location: user_login.php");
    exit();
}

// 獲取商品ID
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id <= 0) {
    header("Location: shop.php");
    exit();
}

// 獲取商品詳細信息
$sql = "SELECT * FROM merchandise WHERE ItemID = ? AND Available = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: shop.php");
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

// 獲取用戶點數
$user_id = $_SESSION['user_id'] ?? 0;
$points = 0;

if ($user_id > 0) {
    $sql = "SELECT Points FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $points = $user['Points'] ?? 0;
    }
    $stmt->close();
}

// 設置分類中文名稱
$category_name = '';
if ($product['Category'] == 'head') $category_name = '頭像';
else if ($product['Category'] == 'background') $category_name = '個人檔案背景';
else if ($product['Category'] == 'wallpaper') $category_name = '桌布';
else $category_name = $product['Category'];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['Name']); ?> - 永續小站商店</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/shop.css">
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>
    
    <div class="product-detail-container">
        <div class="product-detail">
            <div class="product-images">
                <div class="main-image">
                    <img src="../<?php echo htmlspecialchars($product['ImageURL']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">
                </div>
                <?php if (!empty($product['PreviewURL'])): ?>
                <div class="preview-image">
                    <h3>實裝效果預覽</h3>
                    <img src="../<?php echo htmlspecialchars($product['PreviewURL']); ?>" alt="預覽效果">
                </div>
                <?php endif; ?>
            </div>
            
            <div class="product-info-detail">
                <h1><?php echo htmlspecialchars($product['Name']); ?></h1>
                <p class="product-category">分類: <?php echo $category_name; ?></p>
                <p class="product-description"><?php echo htmlspecialchars($product['Description']); ?></p>
                <p class="product-points-required">所需點數: <span><?php echo $product['PointsRequired']; ?></span></p>
                <p class="user-points">您目前的點數: <span><?php echo $points; ?></span></p>
                
                <?php if ($points >= $product['PointsRequired']): ?>
                    <button class="buy-button" onclick="confirmPurchase(<?php echo $product['ItemID']; ?>)">購買商品</button>
                <?php else: ?>
                    <button class="buy-button disabled" disabled>點數不足</button>
                <?php endif; ?>
                
                <a href="shop.php" class="back-button">返回商店</a>
            </div>
        </div>
    </div>
    
    <!-- 購買確認彈窗 -->
    <div id="purchase-modal" class="modal">
        <div class="modal-content">
            <h2>確認購買</h2>
            <p>您確定要購買 <span id="product-name"><?php echo htmlspecialchars($product['Name']); ?></span> 嗎？</p>
            <p>將消耗 <span id="points-required"><?php echo $product['PointsRequired']; ?></span> 點數</p>
            <div class="modal-buttons">
                <button onclick="processPurchase(<?php echo $product['ItemID']; ?>)">確認</button>
                <button onclick="closeModal()">取消</button>
            </div>
        </div>
    </div>
    
    <!-- 購買成功彈窗 -->
    <div id="success-modal" class="modal">
        <div class="modal-content success">
            <div class="checkmark-container">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <h2>購買成功！</h2>
            <p>您已成功購買商品</p>
            <button onclick="redirectToShop()">確認</button>
        </div>
    </div>
    
    <script>
    function confirmPurchase(itemId) {
        document.getElementById('purchase-modal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('purchase-modal').style.display = 'none';
    }
    
    function processPurchase(itemId) {
        // 關閉確認彈窗
        document.getElementById('purchase-modal').style.display = 'none';
        
        // 發送購買請求
        fetch('process_purchase.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'item_id=' + itemId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 顯示成功彈窗
                document.getElementById('success-modal').style.display = 'flex';
            } else {
                alert('購買失敗: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('發生錯誤，請稍後再試');
        });
    }
    
    function redirectToShop() {
        window.location.href = 'shop.php';
    }
    </script>
</body>
</html>