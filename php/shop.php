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

// 獲取用戶已購買的商品z
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
$sql = "SELECT * FROM merchandise"; // Removed quantity condition\n$category_sql = "SELECT DISTINCT Category FROM merchandise";\n// Removed quantity condition

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
$category_sql = "SELECT DISTINCT Category FROM merchandise";
$category_result = $conn->query($category_sql);
$available_categories = [];
if ($category_result && $category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $available_categories[] = $row['Category'];
    }
}

$userPoints = 0;
if ($user_id > 0) {
    $points_sql = "SELECT TotalPoints FROM achievement WHERE UserID = ?";
    $points_stmt = $conn->prepare($points_sql);
    $points_stmt->bind_param("i", $user_id);
    $points_stmt->execute();
    $points_result = $points_stmt->get_result();
    if ($points_row = $points_result->fetch_assoc()) {
        $userPoints = $points_row['TotalPoints'] ?? 0;
    }
    $points_stmt->close();
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
    <link rel="stylesheet" href="../css/nav3.css">
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
                            <?php
                            $is_wallpaper = ($product['Category'] == 'wallpaper');
                            $is_owned = in_array($product['ItemID'], $purchased_items);
                            $card_class = 'product'; // 使用現有的 class 'product'
                            if ($is_wallpaper && $is_owned) {
                                $card_class .= ' owned-wallpaper';
                            }
                            ?>
                            <div class="<?php echo $card_class; ?>">
                                <a href="product_detail.php?id=<?php echo $product['ItemID']; ?>">
                                    <div class="product-image">
                                        <img src="../<?php echo htmlspecialchars($product['ImageURL']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name"><?php echo htmlspecialchars($product['Name']); ?></h3>
                                        <p class="product-points">所需點數: <?php echo $product['PointsRequired']; ?></p>
                                        <p class="user-points">目前點數: <?php echo $userPoints; ?></p>
                                        <?php if ($is_owned): ?>
                                            <div class="owned-badge">已擁有</div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <?php if ($is_wallpaper && $is_owned): ?>
                                    <a href="../<?php echo htmlspecialchars($product['ImageURL']); ?>" download class="download-wallpaper-btn" onclick="event.stopPropagation();">下載桌布</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>
