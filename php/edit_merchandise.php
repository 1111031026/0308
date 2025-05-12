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

// 取得商品資料
$sql = "SELECT * FROM merchandise WHERE ItemID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $itemID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("找不到該商品");
}
$row = $result->fetch_assoc();

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST["name"]);
    $description = $conn->real_escape_string($_POST["description"]);
    $pointsRequired = intval($_POST["pointsRequired"]);
    $category = $conn->real_escape_string($_POST["category"]);
    $quantity = intval($_POST["quantity"]); // Changed from available to quantity

    $imageURL = $row['ImageURL'];
    $previewURL = $row['PreviewURL'];

    // 主圖片上傳
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../product/";
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $newImageFileName = uniqid() . "_image_" . basename($_FILES["image"]["name"]);
        $target_file_image = $target_dir . $newImageFileName;
        if (in_array($imageFileType, ["jpg","jpeg","png","gif"])) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_image)) {
                $imageURL = "product/" . $newImageFileName;
            } else {
                $error_message = "主圖片上傳失敗。";
            }
        } else {
            $error_message = "主圖片格式錯誤。";
        }
    }
    // 預覽圖片上傳
    if (!isset($error_message) && isset($_FILES["preview_image"]) && $_FILES["preview_image"]["error"] == 0) {
        $target_dir_preview = "../product/";
        $previewFileType = strtolower(pathinfo($_FILES["preview_image"]["name"], PATHINFO_EXTENSION));
        $newPreviewFileName = uniqid() . "_preview_" . basename($_FILES["preview_image"]["name"]);
        $target_file_preview = $target_dir_preview . $newPreviewFileName;
        if (in_array($previewFileType, ["jpg","jpeg","png","gif"])) {
            if (move_uploaded_file($_FILES["preview_image"]["tmp_name"], $target_file_preview)) {
                $previewURL = "product/" . $newPreviewFileName;
            } else {
                $error_message = "預覽圖片上傳失敗。";
            }
        } else {
            $error_message = "預覽圖片格式錯誤。";
        }
    }
    // 更新資料庫
    if (!isset($error_message)) {
        $sql = "UPDATE merchandise SET Name=?, Description=?, PointsRequired=?, Category=?, ImageURL=?, PreviewURL=?, Quantity=? WHERE ItemID=?"; // Changed Available to Quantity
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisssii", $name, $description, $pointsRequired, $category, $imageURL, $previewURL, $quantity, $itemID); // Changed $available to $quantity
        if ($stmt->execute()) {
            $success_message = "商品更新成功！";
            // 重新取得最新資料
            $sql = "SELECT * FROM merchandise WHERE ItemID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $itemID);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
        } else {
            $error_message = "更新失敗：" . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯商品</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/merchandise_manage.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container admin-container">
    <h1>編輯商品</h1>
    <?php if (isset($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <form action="edit_merchandise.php?id=<?php echo $itemID; ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">商品名稱：</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['Name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">商品描述：</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($row['Description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="pointsRequired">所需點數：</label>
            <input type="number" id="pointsRequired" name="pointsRequired" value="<?php echo htmlspecialchars($row['PointsRequired']); ?>" required min="0">
        </div>
        <div class="form-group">
            <label for="category">類別：</label>
            <select id="category" name="category" required>
                <option value="background" <?php if($row['Category']==='background') echo 'selected'; ?>>背景</option>
                <option value="wallpaper" <?php if($row['Category']==='wallpaper') echo 'selected'; ?>>桌布</option>
                <option value="head" <?php if($row['Category']==='head') echo 'selected'; ?>>頭像</option>
            </select>
        </div>
        <div class="form-group">
            <label>目前主圖片：</label><br>
            <img src="../<?php echo htmlspecialchars($row['ImageURL']); ?>" alt="主圖片" style="max-width:100px;">
        </div>
        <div class="form-group">
            <label for="image">更換主圖片：</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <div class="form-group">
            <label>目前預覽圖片：</label><br>
            <img src="../<?php echo htmlspecialchars($row['PreviewURL']); ?>" alt="預覽圖片" style="max-width:100px;">
        </div>
        <div class="form-group">
            <label for="preview_image">更換預覽圖片：</label>
            <input type="file" id="preview_image" name="preview_image" accept="image/*">
        </div>
        <div class="form-group">
            <label for="quantity">數量：</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($row['Quantity'] ?? 0); ?>" required min="0">
        </div>
        <div class="form-buttons-container">
            <button type="submit" class="btn-submit">儲存修改</button>
            <button type="button" onclick="window.location.href='merchandise_manage.php'" class="btn-cancel btn-secondary">返回商品管理</button>
        </div>
    </form>
</div>
</body>
</html>