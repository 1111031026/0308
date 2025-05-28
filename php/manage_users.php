<?php
session_start();

// 檢查是否為管理員登入
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

include 'db_connect.php';

// 處理用戶删除
if (isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    // 防止刪除自己
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM user WHERE UserID = ? AND Status != 'Admin'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success_message = "用戶已成功删除";
        } else {
            $error_message = "無法刪除該用戶";
        }
    } else {
        $error_message = "無法刪除當前登入的管理員賬號";
    }
}

// 處理用戶角色更新
if (isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];
    // 防止更改自己的角色
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE user SET Status = ? WHERE UserID = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success_message = "用戶角色已更新";
        } else {
            $error_message = "角色更新失敗";
        }
    } else {
        $error_message = "無法更改當前登入的管理員角色";
    }
}

// 搜索功能
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where_clause = $search ? "WHERE Username LIKE '%$search%' OR Email LIKE '%$search%'" : "";

// 分頁設置
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 獲取總用戶數
$total_result = $conn->query("SELECT COUNT(*) as total FROM user $where_clause");
$total_users = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $per_page);

// 獲取用戶列表
$sql = "SELECT UserID, Username, Email, Status, JoinDate FROM user $where_clause ORDER BY JoinDate DESC LIMIT $offset, $per_page";
$users = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用戶管理</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
</head>
<body>
    
    <div class="admin-container">
        <div class="page-header">
            <h1>用戶管理</h1>
            <a href="admin_dashboard.php" class="back-btn">返回儀表板</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- 搜索欄 -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="搜索用戶名或電子郵件..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">搜索</button>
            </form>
        </div>

        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>用戶名</th>
                        <th>Email</th>
                        <th>角色</th>
                        <th>註冊日期</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['Username']); ?></td>
                        <td><?php echo htmlspecialchars($user['Email']); ?></td>
                        <td>
                            <?php if ($user['UserID'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                    <select name="new_role" onchange="this.form.submit()" <?php echo $user['Status'] === 'Admin' ? 'disabled' : ''; ?>>
                                        <option value="Student" <?php echo $user['Status'] === 'Student' ? 'selected' : ''; ?>>Student</option>
                                        <option value="Teacher" <?php echo $user['Status'] === 'Teacher' ? 'selected' : ''; ?>>Teacher</option>
                                        <option value="Admin" <?php echo $user['Status'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            <?php else: ?>
                                <?php echo htmlspecialchars($user['Status']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($user['JoinDate'])); ?></td>
                        <td>
                            <?php if ($user['UserID'] != $_SESSION['user_id'] && $user['Status'] !== 'Admin'): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('確定要刪除此用戶嗎？此操作無法撤銷。');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                    <button type="submit" name="delete_user" class="delete-btn">刪除</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
    .delete-btn {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
    }
    .delete-btn:hover {
        background-color: #c0392b;
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
    select {
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    </style>
</body>
</html> 