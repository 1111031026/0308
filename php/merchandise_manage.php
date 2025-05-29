<?php
session_start();

// 檢查是否為管理員登入
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

include 'db_connect.php';

// 處理商品刪除
if (isset($_POST['delete_merchandise'])) {
    $item_id = intval($_POST['item_id']);
    // 先刪除相關的圖片文件
    $stmt = $conn->prepare("SELECT ImageURL, PreviewURL FROM merchandise WHERE ItemID = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($image_data = $result->fetch_assoc()) {
        if ($image_data['ImageURL'] && file_exists("../" . $image_data['ImageURL'])) {
            unlink("../" . $image_data['ImageURL']);
        }
        if ($image_data['PreviewURL'] && file_exists("../" . $image_data['PreviewURL'])) {
            unlink("../" . $image_data['PreviewURL']);
        }
    }
    
    // 刪除商品記錄
    $stmt = $conn->prepare("DELETE FROM merchandise WHERE ItemID = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $success_message = "商品已成功刪除";
    } else {
        $error_message = "刪除商品失敗";
    }
}

// 搜索功能
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where_clause = $search ? "WHERE Name LIKE '%$search%' OR Description LIKE '%$search%' OR Category LIKE '%$search%'" : "";

// 分頁設置
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 獲取總商品數
$total_result = $conn->query("SELECT COUNT(*) as total FROM merchandise $where_clause");
$total_items = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $per_page);

// 獲取商品列表
$sql = "
    SELECT ItemID, Name, Description, PointsRequired, Category, ImageURL, PreviewURL
    FROM merchandise 
    $where_clause 
    ORDER BY ItemID DESC 
    LIMIT $offset, $per_page
";
$merchandise = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
</head>
<body>
    
    
    <div class="admin-container">
        <div class="page-header">
            <h1>商品管理</h1>
            <a href="admin_dashboard.php" class="back-btn">返回儀表板</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- 功能按鈕 -->
        <div class="action-buttons">
            <a href="add_merchandise.php" class="add-btn">新增商品</a>
        </div>

        <!-- 搜索欄 -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="搜索商品名稱、描述或類別..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">搜索</button>
            </form>
        </div>

        <!-- 商品列表 -->
        <div class="merchandise-grid">
            <?php while ($item = $merchandise->fetch_assoc()): ?>
            <div class="merchandise-card">
                <div class="merchandise-image">
                    <?php if ($item['PreviewURL']): ?>
                        <img src="<?php echo '../' . htmlspecialchars($item['PreviewURL']); ?>" 
                             alt="<?php echo htmlspecialchars($item['Name']); ?>">
                    <?php elseif ($item['ImageURL']): ?>
                        <img src="<?php echo '../' . htmlspecialchars($item['ImageURL']); ?>" 
                             alt="<?php echo htmlspecialchars($item['Name']); ?>">
                    <?php else: ?>
                        <img src="../img/default-merchandise.png" alt="Default Image">
                    <?php endif; ?>
                </div>
                <div class="merchandise-info">
                    <h3><?php echo htmlspecialchars($item['Name']); ?></h3>
                    <p class="description"><?php echo htmlspecialchars(substr($item['Description'], 0, 100)) . (strlen($item['Description']) > 100 ? '...' : ''); ?></p>
                    <div class="merchandise-details">
                        <span class="points">所需點數: <?php echo number_format($item['PointsRequired']); ?></span>
                        <span class="category">類別: <?php echo htmlspecialchars($item['Category']); ?></span>
                    </div>
                    <div class="merchandise-actions">
                        <a href="edit_merchandise.php?id=<?php echo $item['ItemID']; ?>" class="edit-btn">編輯</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('確定要刪除此商品嗎？此操作無法撤銷。');">
                            <input type="hidden" name="item_id" value="<?php echo $item['ItemID']; ?>">
                            <button type="submit" name="delete_merchandise" class="delete-btn">刪除</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- 分頁 -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                   class="<?php echo $page === $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .back-btn {
        padding: 8px 15px;
        background-color: #34495e;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.9em;
        transition: background-color 0.3s;
    }

    .back-btn:hover {
        background-color: #2c3e50;
    }

    .action-buttons {
        margin: 20px 0;
        text-align: right;
        display: flex;
        justify-content: flex-end;
    }
    .add-btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: #27ae60;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s;
        width: auto;
        min-width: unset;
        max-width: unset;
        box-sizing: border-box;
    }
    .add-btn:hover {
        background-color: #219a52;
    }
    .merchandise-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .merchandise-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .merchandise-image {
        height: 200px;
        overflow: hidden;
    }
    .merchandise-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .merchandise-info {
        padding: 15px;
    }
    .merchandise-info h3 {
        margin: 0 0 10px 0;
        color: #2c3e50;
    }
    .description {
        color: #7f8c8d;
        margin-bottom: 10px;
        font-size: 0.9em;
        min-height: 40px;
    }
    .merchandise-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 0.9em;
    }
    .points {
        color: #e74c3c;
        font-weight: bold;
    }
    .category {
        color: #7f8c8d;
    }
    .merchandise-actions {
        display: flex;
        gap: 10px;
    }
    .edit-btn {
        background-color: #3498db;
        color: white;
        padding: 5px 15px;
        border-radius: 3px;
        text-decoration: none;
        font-size: 0.9em;
    }
    .edit-btn:hover {
        background-color: #2980b9;
    }
    .delete-btn {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 0.9em;
    }
    .delete-btn:hover {
        background-color: #c0392b;
    }
    .search-section {
        margin-bottom: 20px;
    }
    .search-form {
        display: flex;
        gap: 10px;
        max-width: 500px;
        margin: 0 auto;
    }
    .search-form input[type="text"] {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .search-form button {
        padding: 8px 20px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .search-form button:hover {
        background-color: #2980b9;
    }
    .pagination {
        margin-top: 20px;
        text-align: center;
    }
    .pagination a {
        display: inline-block;
        padding: 8px 12px;
        margin: 0 4px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
    }
    .pagination a.active {
        background-color: #3498db;
        color: white;
        border-color: #3498db;
    }
    </style>
</body>
</html> 