<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站 - 編輯區</title>
    <link rel="stylesheet" href="../css/2.css">
</head>
<body>
    <header>
        <?php
        include "nav.php";
        ?>
    </header>
    

    <!-- 編輯區 -->
    <div class="edit-container">
        <!-- 編輯區標題 -->
        <div class="edit-header">
            <span>題目編號: 00001</span>
        </div>

        <!-- 類型選擇區域 -->
        <div class="type-selection">
            <span class="type-label">類型:</span>
            <div class="type-buttons">
                <button class="type-btn" data-type="true-false">是非</button>
                <button class="type-btn" data-type="multiple-choice" data-active="true">選擇</button>
                <button class="type-btn" data-type="fill-in">填空</button>
            </div>
        </div>

        <!-- 編輯內容區域 -->
        <div class="edit-content">
            <h2>題目內文:</h2>
            <textarea class="content-textarea" placeholder="請輸入題目內文"></textarea>

            <!-- 選擇題選項 -->
            <div class="options multiple-choice-options" data-active="true">
                <div class="option">
                    <label>選項A:</label>
                    <input type="text" placeholder="選項A">
                </div>
                <div class="option">
                    <label>選項B:</label>
                    <input type="text" placeholder="選項B">
                </div>
                <div class="option">
                    <label>選項C:</label>
                    <input type="text" placeholder="選項C">
                </div>
                <div class="option">
                    <label>選項D:</label>
                    <input type="text" placeholder="選項D">
                </div>
            </div>

            <!-- 是非題選項 -->
            <div class="options true-false-options">
                <div class="true-false-buttons">
                    <button class="true-false-btn" data-value="true">是</button>
                    <button class="true-false-btn" data-value="false">否</button>
                </div>
            </div>

            <!-- 填空題選項 -->
            <div class="options fill-in-options">
                <h2>答案:</h2>
                <textarea class="content-textarea" placeholder="請輸入答案"></textarea>
            </div>
        </div>
    </div>

    <!-- 外部控制按鈕（水平排列，靜態放置在底部） -->
    <div class="external-controls">
        <button class="external-btn view-all" onclick="window.location.href='view-all-qusetion.php'">查看所有題目</button>
        <button class="external-btn save">確認儲存</button>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>