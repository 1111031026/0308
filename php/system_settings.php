<?php
session_start();

// 檢查是否為管理員登入
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

include 'db_connect.php';

// 檢查系統設置表是否存在，如果不存在則創建
$sql = "CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql);

// 初始化默認設置
$default_settings = [
    'site_name' => ['value' => 'SustainHub', 'description' => '網站名稱'],
    'site_description' => ['value' => '永續環境教育平台', 'description' => '網站描述'],
    'points_per_correct_answer' => ['value' => '5', 'description' => '答對題目獲得的點數'],
    'points_per_article_view' => ['value' => '1', 'description' => '閱讀文章獲得的點數'],
    'points_per_article_publish' => ['value' => '10', 'description' => '發布文章獲得的點數'],
    'points_per_nearby_post' => ['value' => '5', 'description' => '發表附近討論貼文獲得的點數'],
    'maintenance_mode' => ['value' => '0', 'description' => '維護模式（1=開啟，0=關閉）'],
    'max_upload_size' => ['value' => '5242880', 'description' => '最大上傳文件大小（字節）'],
    'allowed_file_types' => ['value' => 'jpg,jpeg,png,gif', 'description' => '允許上傳的文件類型']
];

// 插入或更新默認設置
foreach ($default_settings as $key => $setting) {
    $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value, description) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->bind_param("sss", $key, $setting['value'], $setting['description']);
    $stmt->execute();
}

// 處理設置更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->bind_param("ss", $value, $key);
        $stmt->execute();
    }
    $success_message = "設置已成功更新！";
}

// 獲取當前設置
$result = $conn->query("SELECT * FROM system_settings ORDER BY setting_key");
$settings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系統設置</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <h1>系統設置</h1>
            <a href="admin_dashboard.php" class="back-btn">返回儀表板</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST" class="settings-form">
            <?php foreach ($settings as $setting): ?>
            <div class="setting-group">
                <label for="<?php echo $setting['setting_key']; ?>">
                    <?php echo htmlspecialchars($setting['description']); ?>
                </label>
                <?php if ($setting['setting_key'] === 'maintenance_mode'): ?>
                    <select name="settings[<?php echo $setting['setting_key']; ?>]" id="<?php echo $setting['setting_key']; ?>">
                        <option value="0" <?php echo $setting['setting_value'] == '0' ? 'selected' : ''; ?>>關閉</option>
                        <option value="1" <?php echo $setting['setting_value'] == '1' ? 'selected' : ''; ?>>開啟</option>
                    </select>
                <?php elseif ($setting['setting_key'] === 'allowed_file_types'): ?>
                    <input type="text" name="settings[<?php echo $setting['setting_key']; ?>]"
                           id="<?php echo $setting['setting_key']; ?>"
                           value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                           pattern="[a-zA-Z0-9,]*"
                           title="請使用逗號分隔文件類型，不含空格和特殊字符">
                <?php else: ?>
                    <input type="text" name="settings[<?php echo $setting['setting_key']; ?>]"
                           id="<?php echo $setting['setting_key']; ?>"
                           value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                <?php endif; ?>
                <p class="setting-hint">
                    <?php 
                    switch ($setting['setting_key']) {
                        case 'points_per_correct_answer':
                            echo '用戶答對問題時獲得的獎勵點數';
                            break;
                        case 'points_per_article_view':
                            echo '用戶閱讀文章時獲得的獎勵點數';
                            break;
                        case 'points_per_article_publish':
                            echo '用戶發布文章時獲得的獎勵點數';
                            break;
                        case 'points_per_nearby_post':
                            echo '用戶發表附近討論貼文時獲得的獎勵點數';
                            break;
                        case 'max_upload_size':
                            echo '上傳文件的最大大小（以字節為單位，5242880 = 5MB）';
                            break;
                        case 'allowed_file_types':
                            echo '允許上傳的文件類型（用逗號分隔）';
                            break;
                        case 'maintenance_mode':
                            echo '開啟維護模式將暫時限制普通用戶訪問網站';
                            break;
                    }
                    ?>
                </p>
            </div>
            <?php endforeach; ?>
            
            <div class="form-actions">
                <button type="submit" name="update_settings" class="save-btn">保存設置</button>
                <button type="reset" class="reset-btn">重置</button>
            </div>
        </form>
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

    .settings-form {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .setting-group {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #eee;
        border-radius: 4px;
    }
    .setting-group:hover {
        background-color: #f8f9fa;
    }
    .setting-group label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: bold;
    }
    .setting-group input,
    .setting-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    .setting-hint {
        margin-top: 5px;
        color: #7f8c8d;
        font-size: 0.9em;
    }
    .form-actions {
        margin-top: 30px;
        text-align: center;
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
    .reset-btn {
        padding: 10px 30px;
        background-color: #95a5a6;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
    }
    .reset-btn:hover {
        background-color: #7f8c8d;
    }
    </style>

    <script>
    // 添加表單提交確認
    document.querySelector('.settings-form').addEventListener('submit', function(e) {
        if (!confirm('確定要保存這些更改嗎？')) {
            e.preventDefault();
        }
    });

    // 添加重置確認
    document.querySelector('.reset-btn').addEventListener('click', function(e) {
        if (!confirm('確定要重置所有更改嗎？')) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html> 