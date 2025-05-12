<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true) {
    header("Location: user_login.php");
    exit();
}

// 獲取搜尋和分類篩選參數
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';

// 獲取用戶ID
$user_id = $_SESSION['user_id'] ?? 0;

// 獲取用戶已購買的商品
$purchased_items = [];
if ($user_id > 0) {
    $purchase_sql = "SELECT ItemID FROM purchase WHERE UserID = ?";
    $purchase_stmt = $conn->prepare($purchase_sql);
    $purchase_stmt->bind_param("i", $user_id);
    $purchase_stmt->execute();
    $purchase_result = $purchase_stmt->get_result();
    
    while ($row = $purchase_result->fetch_assoc()) {
        $purchased_items[] = $row['ItemID'];
    }
    $purchase_stmt->close();
}

// 構建SQL查詢
// 構建SQL查詢
$sql = "SELECT * FROM merchandise WHERE Quantity > 0";

// 添加搜尋條件
if (!empty($search)) {
    $sql .= " AND Name LIKE '%" . $conn->real_escape_string($search) . "%'";
}

// 添加分類篩選條件
if (!empty($filter_category) && $filter_category != 'all') {
    if ($filter_category == 'owned') {
        // 如果選擇「已擁有」，則只顯示用戶已購買的商品
        if (!empty($purchased_items)) {
            $sql .= " AND ItemID IN (" . implode(',', $purchased_items) . ")";
        } else {
            // 如果用戶沒有購買任何商品，則返回空結果
            $sql .= " AND 1=0";
        }
    } else {
        $sql .= " AND Category = '" . $conn->real_escape_string($filter_category) . "'";
    }
}

// 使用 CASE 語句來自定義排序順序
$sql .= " ORDER BY CASE 
            WHEN Category = 'head' THEN 1 
            WHEN Category = 'background' THEN 2 
            WHEN Category = 'wallpaper' THEN 3 
            ELSE 4 
          END, Category";

$result = $conn->query($sql);

// 按分類整理商品
$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[$row['Category']][] = $row;
    }
}

// 獲取所有可用的分類
$category_sql = "SELECT DISTINCT Category FROM merchandise WHERE Quantity > 0";
$category_result = $conn->query($category_sql);
$available_categories = [];
if ($category_result && $category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $available_categories[] = $row['Category'];
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
    <link rel="stylesheet" href="../css/shop.css">
    <style>
        /* 搜尋和篩選區域樣式 */
        .search-filter-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .search-filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-input {
            flex: 1;
            min-width: 200px;
        }
        
        .search-input input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .category-filter {
            min-width: 200px;
        }
        
        .category-filter select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: white;
        }
        
        .search-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .search-button:hover {
            background-color: #45a049;
        }
        
        .reset-button {
            padding: 10px 20px;
            background-color: #f1f1f1;
            color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            text-decoration: none; /* 移除底線 */
            display: inline-block; /* 確保按鈕樣式正確 */
        }
        
        .reset-button:hover {
            background-color: #e0e0e0;
        }
        
        /* 已擁有標記樣式 */
        .product-info {
            position: relative;
        }
        
        .owned-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: #4CAF50;
            color: white;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .search-filter-form {
                flex-direction: column;
            }
            
            .search-input, .category-filter {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>
    
    <div class="shop-container">
        <h1 class="page-title">商城</h1>
        <!-- 搜尋和篩選區域 -->
        <div class="search-filter-container">
            <form class="search-filter-form" method="GET" action="shop.php" id="searchForm">
                <div class="search-input">
                    <input type="text" name="search" placeholder="搜尋商品名稱..." value="<?php echo htmlspecialchars($search); ?>" onkeypress="if(event.keyCode==13){event.preventDefault(); document.getElementById('searchForm').submit();}">
                </div>
                <div class="category-filter"> 
                    <select name="category" onchange="document.getElementById('searchForm').submit();"> 
                        <option value="all" <?php echo $filter_category == 'all' || empty($filter_category) ? 'selected' : ''; ?>>所有分類</option> 
                        <option value="owned" <?php echo $filter_category == 'owned' ? 'selected' : ''; ?>>已擁有</option>
                        <?php foreach ($available_categories as $cat): ?> 
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filter_category == $cat ? 'selected' : ''; ?>> 
                                <?php 
                                if ($cat == 'head') echo '頭像'; 
                                else if ($cat == 'background') echo '個人檔案背景'; 
                                else if ($cat == 'wallpaper') echo '桌布'; 
                                else echo htmlspecialchars($cat); 
                                ?> 
                            </option> 
                        <?php endforeach; ?> 
                    </select> 
                </div>
                <button type="submit" class="search-button">搜尋</button>
                <a href="shop.php" class="reset-button">重置</a>
            </form>
        </div>
        
        <?php if (empty($categories)): ?>
            <p class="no-products">目前沒有可用的商品<?php echo !empty($search) || !empty($filter_category) ? '符合搜尋條件' : ''; ?></p>
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
                                        <p class="product-quantity">剩餘數量: <span><?php echo $product['Quantity']; ?></span></p>
                                        <?php if (in_array($product['ItemID'], $purchased_items)): ?>
                                        <div class="owned-badge">已擁有</div>
                                        <?php endif; ?>
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
