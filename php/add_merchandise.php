<?php
session_start();

// 檢查是否為管理員登入
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

include 'db_connect.php';

$error_message = '';
$success_message = '';

// 定義商品類別
$categories = array(
    'head' => '頭像',
    'wallpaper' => '桌布',
    'background' => '背景'
);

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $points_required = intval($_POST['points_required']);
    $category = trim($_POST['category']);
    
    // 基本驗證
    if (empty($name)) {
        $error_message = "商品名稱不能為空";
    } elseif ($points_required < 0) {
        $error_message = "所需點數不能為負數";
    } elseif (empty($category)) {
        $error_message = "類別不能為空";
    } else {
        // 處理圖片上傳
        $image_url = null;
        $preview_url = null;
        
        // 處理主圖片
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = "../uploads/merchandise/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $error_message = "只允許上傳 JPG, JPEG, PNG 或 GIF 格式的圖片";
            } else {
                $new_filename = uniqid('merchandise_') . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_url = "uploads/merchandise/" . $new_filename;
                } else {
                    $error_message = "圖片上傳失敗";
                }
            }
        }
        
        // 處理預覽圖片
        if (!empty($_FILES['preview']['name'])) {
            $upload_dir = "../uploads/merchandise/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['preview']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $error_message = "只允許上傳 JPG, JPEG, PNG 或 GIF 格式的圖片";
            } else {
                $new_filename = uniqid('merchandise_preview_') . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['preview']['tmp_name'], $upload_path)) {
                    $preview_url = "uploads/merchandise/" . $new_filename;
                } else {
                    $error_message = "預覽圖片上傳失敗";
                }
            }
        }
        
        if (empty($error_message)) {
            // 準備插入SQL
            $sql = "INSERT INTO merchandise (Name, Description, PointsRequired, Category, ImageURL, PreviewURL) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisss", $name, $description, $points_required, $category, $image_url, $preview_url);
            
            if ($stmt->execute()) {
                $success_message = "商品新增成功";
                // 清空表單
                $name = '';
                $description = '';
                $points_required = '';
                $category = '';
            } else {
                $error_message = "新增失敗: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增商品</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="admin-container">
        <h1>新增商品</h1>
        
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="edit-form">
            <div class="form-group">
                <label for="name">商品名稱 *</label>
                <input type="text" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="description">商品描述</label>
                <textarea id="description" name="description" rows="4"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="points_required">所需點數 *</label>
                <input type="number" id="points_required" name="points_required" value="<?php echo isset($points_required) ? $points_required : ''; ?>" required min="0">
            </div>

            <div class="form-group">
                <label for="category">類別 *</label>
                <select id="category" name="category" required class="form-select">
                    <option value="">請選擇類別</option>
                    <?php foreach ($categories as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (isset($category) && $category === $value) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">主圖片</label>
                <input type="file" id="image" name="image" accept="image/*">
                <p class="help-text">支援的格式：JPG, JPEG, PNG, GIF</p>
            </div>

            <div class="form-group">
                <label for="preview">預覽圖片</label>
                <input type="file" id="preview" name="preview" accept="image/*">
                <p class="help-text">支援的格式：JPG, JPEG, PNG, GIF</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="save-btn">新增商品</button>
                <a href="merchandise_manage.php" class="cancel-btn">取消</a>
            </div>
        </form>
    </div>

    <style>
    .edit-form {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: bold;
    }
    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    .help-text {
        margin-top: 5px;
        color: #7f8c8d;
        font-size: 0.9em;
    }
    .form-actions {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    .save-btn {
        padding: 10px 30px;
        background-color: #2ecc71;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
    }
    .save-btn:hover {
        background-color: #27ae60;
    }
    .cancel-btn {
        padding: 10px 30px;
        background-color: #95a5a6;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
        text-decoration: none;
        display: inline-block;
    }
    .cancel-btn:hover {
        background-color: #7f8c8d;
    }
    .error-message,
    .success-message {
        margin-bottom: 20px;
        padding: 10px;
        border-radius: 4px;
        text-align: center;
    }
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .success-message {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .form-select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
        cursor: pointer;
    }
    .form-select:hover {
        border-color: #bbb;
    }
    .form-select:focus {
        border-color: #3498db;
        outline: none;
    }
    </style>
</body>
</html> 