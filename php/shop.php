<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true) {
    header("Location: user_login.php");
    exit();
}

// 獲取所有商品
$sql = "SELECT * FROM merchandise WHERE Available = 1 ORDER BY Category";
$result = $conn->query($sql);

// 按分類整理商品
$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[$row['Category']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站-商店</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/shop.css">
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>
    
    <div class="shop-container">
        <h1 class="page-title">永續小站-商店</h1>
        
        <?php if (empty($categories)): ?>
            <p class="no-products">目前沒有可用的商品</p>
        <?php else: ?>
            <?php foreach ($categories as $category => $products): ?>
                <div class="category-section">
                    <h2 class="category-title">
                        <?php 
                        if ($category == 'head') echo '頭像';
                        else if ($category == 'background') echo '個人檔案背景';
                        else if ($category == 'wallpaper') echo '桌布';
                        else echo htmlspecialchars($category);
                        ?>
                    </h2>
                    <div class="product-list">
                        <?php foreach ($products as $product): ?>
                            <div class="product">
                                <a href="product_detail.php?id=<?php echo $product['ItemID']; ?>">
                                    <div class="product-image">
                                        <img src="../<?php echo htmlspecialchars($product['ImageURL']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name"><?php echo htmlspecialchars($product['Name']); ?></h3>
                                        <p class="product-points">所需點數: <?php echo $product['PointsRequired']; ?></p>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>