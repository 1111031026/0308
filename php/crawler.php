<?php
session_start();
// 添加測試用戶session
$user_id = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站 - 網頁爬蟲</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/crawler.css">
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <!-- 加載動畫覆蓋層 -->
    <div class="loading-overlay" id="loadingOverlay">
        <img src="../img/loading-earth.svg" class="loading-earth" alt="載入中">
        <div class="loading-text">
            <span>L</span>
            <span>O</span>
            <span>A</span>
            <span>D</span>
            <span>I</span>
            <span>N</span>
            <span>G</span>
            <span>.</span>
            <span>.</span>
            <span>.</span>
        </div>
    </div>

    <main>
        <div class="crawler-form">
            <form method="POST" action="" enctype="multipart/form-data" id="crawlerForm">
                <input type="text" name="title" class="url-input" placeholder="請輸入文章標題" required>
                <textarea name="description" class="url-input" placeholder="請輸入文章簡介" required></textarea>
                <select name="category" class="url-input" required>
                    <option value="">請選擇分類</option>
                    <option value="sdg13">氣候永續</option>
                    <option value="sdg14">海洋永續</option>
                    <option value="sdg15">陸域永續</option>
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
                            // 直接使用完整的URL
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

                    // 使用AI提取主要內容
                    require_once 'article_analyzer.php';
                    $content = $dom->saveHTML(); // 原始HTML內容
                    $extracted_content = extractContent($content);
                    
                    // 檢查提取的內容是否為中文
                    if (!preg_match('/[\x{4e00}-\x{9fa5}]/u', $extracted_content)) {
                        $extracted_content = "以下內容應以繁體中文呈現：\n\n" . $extracted_content;
                        $extracted_content = extractContent($extracted_content);
                    }
                    
                    // 準備SQL語句
                    $stmt = $conn->prepare("INSERT INTO article (ArticleURL, Title, Description, Category, ImageURL, Content, teacher_summary, UserID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                    } else {
                        echo '<div class="content-display">錯誤：請先登入後再進行文章保存。</div>';
                        exit();
                    }
                    $userTitle = $_POST['title'];
                    $description = $_POST['description'];
                    $category = $_POST['category'];
                    $stmt->bind_param("sssssssi", $url, $userTitle, $description, $category, $imageURL, $content, $extracted_content, $user_id);
                    
                    // 執行SQL
                    if ($stmt->execute()) {
                        echo '<div class="content-display">';
                        echo '<h2>重點已成功保存！</h2>';
                        echo '<p>標題：' . htmlspecialchars($title) . '</p>';
                        echo '<p><a href="ai_summary_editor.php?id=' . $conn->insert_id . '">查看重點</a></p>';
                        
                        // 更新教師積分
                        $updatePointsStmt = $conn->prepare("UPDATE teacher_achievement SET TotalPoints = TotalPoints + 5 WHERE UserID = ?");
                        if ($updatePointsStmt) {
                            $updatePointsStmt->bind_param("i", $user_id);
                            if ($updatePointsStmt->execute()) {
                                echo '<p>恭喜！您已獲得 5 點積分。</p>';
                            } else {
                                echo '<p>更新積分失敗：' . $updatePointsStmt->error . '</p>';
                            }
                            $updatePointsStmt->close();
                        } else {
                            echo '<p>準備更新積分語句失敗：' . $conn->error . '</p>';
                        }

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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('crawlerForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            form.addEventListener('submit', function() {
                loadingOverlay.classList.add('active');
            });
        });
    </script>
</body>
</html>