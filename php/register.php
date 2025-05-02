<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊頁面</title>
    <link rel="stylesheet" href="../css/register.css" media="all">
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: -100%; /* 改為百分比，確保完全隱藏 */
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            color: white;
            font-size: 16px;
            transition: right 0.5s ease; /* 只過渡 right 屬性 */
            z-index: 1000;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 200px; /* 確保內容不會太窄 */
            box-sizing: border-box;
        }
        .toast.success {
            border-left: 4px solid #2ecc71;
        }
        .toast.error {
            border-left: 4px solid #e74c3c;
        }
        .toast.show {
            right: 20px; /* 顯示時的位置 */
        }
        .toast-icon {
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="particles">
        <?php
        for ($i = 0; $i < 50; $i++) {
            $size = rand(8, 12);
            $left = rand(0, 100);
            $animationDelay = (rand(0, 1000) / 100);
            echo "<div class='particle' style='width: {$size}px; height: {$size}px; left: {$left}%; animation-delay: {$animationDelay}s;'></div>";
        }
        ?>
    </div>
    
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sustain";

    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        die("連接失敗: " . $conn->connect_error);
    }

    if (isset($_POST["Username"]) && isset($_POST["Password"]) && isset($_POST["Email"])) {
        $username = $conn->real_escape_string($_POST["Username"]);
        $password = $_POST["Password"];
        $email = $conn->real_escape_string($_POST["Email"]);
        $role = isset($_POST["role"]) ? $_POST["role"] : 'Student';

        $sql = "SELECT * FROM user WHERE Username='$username'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            echo "<div class='toast error'><span class='toast-icon'>❌</span>用戶名稱已存在，請選擇其他名稱。</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO user (Username, Password, Email, Status) VALUES ('$username', '$hashed_password', '$email', '$role')";
            
            if ($conn->query($sql) === TRUE) {
                $newUserId = $conn->insert_id; // 獲取新插入用戶的 ID

                // 如果角色是學生，則在 achievement 表中創建初始記錄
                if ($role === 'Student') {
                    $stmtAchieve = $conn->prepare("INSERT INTO achievement (UserID, TotalPoints, ArticlesViewed, ChoiceQuestionsCorrect, TFQuestionsCorrect, FillinQuestionsCorrect) VALUES (?, 0, 0, 0, 0, 0)");
                    if ($stmtAchieve) {
                        $stmtAchieve->bind_param("i", $newUserId);
                        if (!$stmtAchieve->execute()) {
                            // 記錄錯誤，但不阻止註冊成功訊息
                            error_log("Failed to create achievement record for UserID: " . $newUserId . " Error: " . $stmtAchieve->error);
                        }
                        $stmtAchieve->close();
                    } else {
                        error_log("Failed to prepare achievement insert statement: " . $conn->error);
                    }
                }

                echo "<div class='toast success'><span class='toast-icon'>✅</span>註冊成功！請登入。</div>";
                echo "<div class='center'><a class='login-button' href='user_login.php'>登入</a></div>";
            } else {
                echo "<div class='toast error'><span class='toast-icon'>❌</span>註冊失敗，請稍後再試。 Error: " . $conn->error . "</div>"; // 添加錯誤訊息
            }
        }
    }
    $conn->close();
    ?>

    <form action="register.php" method="post">
        <table>
        <h2>註冊一個帳號</h2>
            <tr>
                <td style="font-size: 14px;">使用者名稱：</td>
                <td><input type="text" name="Username" size="15" maxlength="50" required /></td>
            </tr>
            <tr>
                <td style="font-size: 14px;">使用者密碼：</td>
                <td><input type="password" name="Password" size="15" maxlength="50" required /></td>
            </tr>
            <tr>
                <td style="font-size: 14px;">電子郵件：</td>
                <td><input type="email" name="Email" size="15" maxlength="100" required /></td>
            </tr>
            <tr>
                <td style="font-size: 14px;">身分：</td>
                <td>
                    <input type="radio" name="role" value="Student" id="student" checked>
                    <label for="student" style="font-size: 14px;">學生</label>
                    <input type="radio" name="role" value="Teacher" id="teacher">
                    <label for="teacher" style="font-size: 14px;">老師</label>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="submit" value="註冊帳戶" />
                </td>
            </tr>
        </table>
    </form>

    <script>
    function showToast() {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toast => {
            setTimeout(() => {
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.remove();
                    }, 500); // 與 CSS transition 時間一致
                }, 5000); // 顯示3秒
            }, 100); // 初始延遲
        });
    }

    // 頁面載入時檢查是否有 toast 元素並觸發
    document.addEventListener('DOMContentLoaded', () => {
        const toasts = document.querySelectorAll('.toast');
        if (toasts.length > 0) {
            showToast();
        }
    });
    </script>
</body>
</html>