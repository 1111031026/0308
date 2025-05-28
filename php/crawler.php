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
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Microsoft JhengHei', '微軟正黑體', Arial, sans-serif;
        }
        
        .crawler-form {
            max-width: 800px;
            margin: 30px auto;
            padding: 35px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .url-input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #ffffff;
        }

        .url-input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
            background-color: #ffffff;
        }

        textarea.url-input {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }

        select.url-input {
            background-color: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007CB2%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 12px auto;
        }

        input[type="file"].url-input {
            padding: 10px;
            background-color: #f8f9fa;
            border: 2px dashed #4CAF50;
        }

        input[type="file"].url-input:hover {
            background-color: #f0f0f0;
        }

        .submit-btn {
            padding: 12px 25px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .submit-btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .submit-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .content-display {
            margin-top: 30px;
            padding: 30px;
            border: none;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .content-display h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
        }

        .content-display p {
            color: #34495e;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .content-display a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .content-display a:hover {
            color: #45a049;
            text-decoration: underline;
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
                    <option value="sdg13">氣候永續</option>
                    <option value="sdg14">海洋能源</option>
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
                    $downloadedImages = array();
                    foreach ($images as $image) {
                        $src = $image->getAttribute('src');
                        if ($src) {
                            // 轉換相對路徑為絕對路徑
                            if (strpos($src, 'http') !== 0) {
                                $src = (strpos($src, '/') === 0)
                                    ? parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . $src
                                    : rtrim(dirname($url), '/') . '/' . $src;
                            }
                            
                            // 下載圖片
                            $uploadDir = '../uploads/';
                            if (!file_exists($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }
                            
                            // 移除URL中的查詢參數和特殊字符
                            $cleanedUrl = preg_replace('/[\?&].*/', '', $src);
                            $imageFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\-\.]/', '_', basename($cleanedUrl));
                            $targetPath = $uploadDir . $imageFileName;
                            
                            // 使用curl下載圖片
                            $ch_img = curl_init($src);
                            curl_setopt($ch_img, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch_img, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch_img, CURLOPT_SSL_VERIFYPEER, false);
                            $img_data = curl_exec($ch_img);
                            curl_close($ch_img);
                            
                            if ($img_data && file_put_contents($targetPath, $img_data)) {
                                $downloadedImages[] = 'uploads/' . $imageFileName;
                                $image->setAttribute('src', '../uploads/' . $imageFileName);
                            }
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

                    // 使用AI提取主要內容 - 這將被存入 teacher_summary 欄位
                    require_once 'article_analyzer.php';
                    $content = $dom->saveHTML(); // 原始HTML內容 - 這將被存入 Content 欄位
                    $extracted_content = extractContent($content); // 提取AI統整後的內容
                    
                    // 檢查提取的內容是否為中文，如果不是則添加提示
                    if (!preg_match('/[\x{4e00}-\x{9fa5}]/u', $extracted_content)) {
                        // 如果沒有中文字符，再次嘗試提取並明確要求中文回答
                        $extracted_content = "以下內容應以繁體中文呈現：\n\n" . $extracted_content;
                        $extracted_content = extractContent($extracted_content); // 再次提取，確保內容為繁體中文
                    }
                    
                    // 確保提取的內容是繁體中文，這將被存入 teacher_summary 欄位
                    
                    // 準備SQL語句
                    $stmt = $conn->prepare("INSERT INTO article (ArticleURL, Title, Description, Category, ImageURL, Content, teacher_summary, UserID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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
                    $stmt->bind_param("sssssssi", $url, $userTitle, $description, $category, $imageURL, $content, $extracted_content, $user_id);
                    
                    // 執行SQL
                    if ($stmt->execute()) {
                        echo '<div class="content-display">';
                        echo '<h2>重點已成功保存！</h2>';
                        echo '<p>標題：' . htmlspecialchars($title) . '</p>';
                        echo '<p><a href="ai_summary_editor.php?id=' . $conn->insert_id . '">查看重點</a></p>';
                        
                        // --- 新增：更新教師積分 --- 
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
                        // --- 更新結束 ---

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