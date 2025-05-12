<?php
session_start();

// 檢查用戶是否為管理員
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "sustain";

// 創建資料庫連接
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST["name"]);
    $description = $conn->real_escape_string($_POST["description"]);
    $pointsRequired = intval($_POST["pointsRequired"]);
    $category = $conn->real_escape_string($_POST["category"]);
    $quantity = intval($_POST["quantity"]); // Changed from available to quantity

    $imageURL = null;
    $previewURL = null;

    // 處理主圖片上傳
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../product/";
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $newImageFileName = uniqid() . "_image_" . basename($_FILES["image"]["name"]);
        $target_file_image = $target_dir . $newImageFileName;

        if ($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png" || $imageFileType == "gif") {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_image)) {
                $imageURL = "product/" . $newImageFileName;
            } else {
                $error_message = "抱歉，上傳主圖片時出現錯誤。";
            }
        } else {
            $error_message = "抱歉，主圖片只允許 JPG、JPEG、PNG 和 GIF 文件。";
        }
    } else {
        $error_message = "請選擇要上傳的主圖片。";
    }

    // 處理預覽圖片上傳 (僅當沒有主圖片錯誤時)
    if (!isset($error_message) && isset($_FILES["preview_image"]) && $_FILES["preview_image"]["error"] == 0) {
        $target_dir_preview = "../product/";
        $previewFileType = strtolower(pathinfo($_FILES["preview_image"]["name"], PATHINFO_EXTENSION));
        $newPreviewFileName = uniqid() . "_preview_" . basename($_FILES["preview_image"]["name"]);
        $target_file_preview = $target_dir_preview . $newPreviewFileName;

        if ($previewFileType == "jpg" || $previewFileType == "jpeg" || $previewFileType == "png" || $previewFileType == "gif") {
            if (move_uploaded_file($_FILES["preview_image"]["tmp_name"], $target_file_preview)) {
                $previewURL = "product/" . $newPreviewFileName;
            } else {
                $error_message = "抱歉，上傳預覽圖片時出現錯誤。";
            }
        } else {
            $error_message = "抱歉，預覽圖片只允許 JPG、JPEG、PNG 和 GIF 文件。";
        }
    } elseif (!isset($error_message) && (!isset($_FILES["preview_image"]) || $_FILES["preview_image"]["error"] != 0) ){
        $error_message = "請選擇要上傳的預覽圖片。";
    }

    // 如果圖片都成功上傳，則插入數據到資料庫
    if (!isset($error_message) && $imageURL && $previewURL) {
        $sql = "INSERT INTO merchandise (Name, Description, PointsRequired, Category, ImageURL, PreviewURL, Quantity) 
                VALUES (?, ?, ?, ?, ?, ?, ?)"; // Changed Available to Quantity
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisssi", $name, $description, $pointsRequired, $category, $imageURL, $previewURL, $quantity); // Changed $available to $quantity

        if ($stmt->execute()) {
            $success_message = "商品新增成功！";
        } else {
            $error_message = "錯誤：" . $stmt->error;
        }
        $stmt->close();
    } elseif (!isset($error_message)) {
        // 如果 $imageURL 或 $previewURL 為空，但沒有其他錯誤，說明是文件選擇問題
        if (!$imageURL) $error_message = $error_message ?? "請確認主圖片已成功上傳且格式正確。";
        if (!$previewURL) $error_message = $error_message ?? "請確認預覽圖片已成功上傳且格式正確。";
    }
}

// 獲取所有商品
$sql = "SELECT * FROM merchandise ORDER BY ItemID DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/merchandise_manage.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container admin-container">
        <h1>商品管理</h1>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="merchandise-form">
            <h2>新增商品</h2>
            <form action="merchandise_manage.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">商品名稱：</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="description">商品描述：</label>
                    <textarea id="description" name="description"></textarea>
                </div>

                <div class="form-group">
                    <label for="pointsRequired">所需點數：</label>
                    <input type="number" id="pointsRequired" name="pointsRequired" required min="0">
                </div>

                <div class="form-group">
                    <label for="category">類別：</label>
                    <select id="category" name="category" required>
                        <option value="background">背景</option>
                        <option value="wallpaper">桌布</option>
                        <option value="head">頭像</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="image">商品主圖片：</label>
                    <input type="file" id="image" name="image" required accept="image/*">
                </div>

                <div class="form-group">
                    <label for="preview_image">商品預覽圖片：</label>
                    <input type="file" id="preview_image" name="preview_image" required accept="image/*">
                </div>

                <div class="form-group">
                    <label for="quantity">數量：</label>
                    <input type="number" id="quantity" name="quantity" value="0" required min="0">
                </div>

                <button type="submit" class="btn-submit">新增商品</button>
            </form>
        </div>

        <div class="merchandise-list">
            <h2>商品列表</h2>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>主圖片</th>
                        <th>預覽圖片</th>
                        <th>名稱</th>
                        <th>描述</th>
                        <th>所需點數</th>
                        <th>類別</th>
                        <th>數量</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['ItemID']; ?></td>
                        <td><img src="../<?php echo htmlspecialchars($row['ImageURL']); ?>" alt="<?php echo htmlspecialchars($row['Name']); ?> 主圖片"></td>
                        <td><img src="../<?php echo htmlspecialchars($row['PreviewURL']); ?>" alt="<?php echo htmlspecialchars($row['Name']); ?> 預覽圖片"></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Description']); ?></td>
                        <td><?php echo htmlspecialchars($row['PointsRequired']); ?></td>
                        <td><?php echo htmlspecialchars($row['Category']); ?></td>
                        <td><?php echo htmlspecialchars($row['Quantity'] ?? 0); ?></td>
                        <td class="actions">
                            <a href="edit_merchandise.php?id=<?php echo $row['ItemID']; ?>">編輯</a>
                            <a href="delete_merchandise.php?id=<?php echo $row['ItemID']; ?>" onclick="return confirm('確定要刪除此商品嗎？');" class="delete-btn">刪除</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>