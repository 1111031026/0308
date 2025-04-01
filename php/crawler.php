<?php
session_start();
// 添加測試用戶session
$_SESSION['user_id'] = 1; // 設置測試用戶ID
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站 - 網頁爬蟲</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/index.css">
    <style>
        .crawler-form {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .url-input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea.url-input {
            min-height: 100px;
            resize: vertical;
        }
        select.url-input {
            background-color: white;
            cursor: pointer;
        }
        input[type="file"].url-input {
            padding: 8px;
            background-color: #f8f9fa;
        }
        .submit-btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .content-display {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <main>
        <div class="crawler-form">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="text" name="title" class="url-input" placeholder="請輸入文章標題" required>
                <textarea name="description" class="url-input" placeholder="請輸入文章簡介" required></textarea>
                <select name="category" class="url-input" required>
                    <option value="">請選擇分類</option>
                    <option value="sdg13">SDG13 氣候永續</option>
                    <option value="sdg14">SDG14 海洋能源</option>
                    <option value="sdg15">SDG15 陸域永續</option>
                </select>
                <input type="file" name="image" class="url-input" accept="image/*">
                <input type="url" name="target_url" class="url-input" placeholder="請輸入要爬取的網址" required>
                <button type="submit" class="submit-btn">開始爬取</button>
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target_url'])) {
                $url = $_POST['target_url'];
                
                // 初始化cURL會話
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

                $html = curl_exec($ch);
                
                if ($html === false) {
                    echo '<div class="content-display">錯誤：' . curl_error($ch) . '</div>';
                } else {
                    // 創建DOM文檔
                    $dom = new DOMDocument();
                    @$dom->loadHTML($html, LIBXML_NOERROR);
                    
                    // 處理圖片路徑
                    $images = $dom->getElementsByTagName('img');
                    foreach ($images as $image) {
                        $src = $image->getAttribute('src');
                        if ($src) {
                            // 轉換相對路徑為絕對路徑
                            if (strpos($src, 'http') !== 0) {
                                $src = (strpos($src, '/') === 0)
                                    ? parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . $src
                                    : rtrim(dirname($url), '/') . '/' . $src;
                            }
                            $image->setAttribute('src', $src);
                        }
                    }
                    
                    // 獲取網頁標題
                    $titleNodes = $dom->getElementsByTagName('title');
                    $title = $titleNodes->length > 0 ? $titleNodes->item(0)->nodeValue : '';
                    
                    // 連接資料庫
                    require_once 'db_connect.php';
                    
                    // 處理上傳的圖片
                    $imageURL = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = '../uploads/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $imageFileName = uniqid() . '_' . basename($_FILES['image']['name']);
                        $targetPath = $uploadDir . $imageFileName;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                            $imageURL = 'uploads/' . $imageFileName;
                        }
                    }

                    // 準備SQL語句
                    $stmt = $conn->prepare("INSERT INTO article (ArticleURL, Title, Description, Category, ImageURL, Content, UserID) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $content = $dom->saveHTML();
                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                    } else {
                        // 如果用戶未登入，可以設置一個預設的管理員ID或顯示錯誤訊息
                        echo '<div class="content-display">錯誤：請先登入後再進行文章保存。</div>';
                        exit();
                    }
                    $userTitle = $_POST['title'];
                    $description = $_POST['description'];
                    $category = $_POST['category'];
                    $stmt->bind_param("ssssssi", $url, $userTitle, $description, $category, $imageURL, $content, $user_id);
                    
                    // 執行SQL
                    if ($stmt->execute()) {
                        echo '<div class="content-display">';
                        echo '<h2>文章已成功保存！</h2>';
                        echo '<p>標題：' . htmlspecialchars($title) . '</p>';
                        echo '<p><a href="article.php?id=' . $conn->insert_id . '">查看文章</a></p>';
                        echo '</div>';
                    } else {
                        echo '<div class="content-display">保存失敗：' . $stmt->error . '</div>';
                    }
                    
                    $stmt->close();
                    $conn->close();
                }
                
                curl_close($ch);
            }
            ?>
        </div>
    </main>
</body>
</html>