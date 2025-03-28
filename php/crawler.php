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
            <form method="POST" action="">
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
                    
                    // 準備SQL語句
                    $stmt = $conn->prepare("INSERT INTO articles (url, title, content) VALUES (?, ?, ?)");
                    $content = $dom->saveHTML();
                    $stmt->bind_param("sss", $url, $title, $content);
                    
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