<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站 - 文章1</title>
    <link rel="stylesheet" href="../css/luntan.css">
</head>

<body>
    <!-- 主導航欄 -->
    <header>
        <?php include "nav.html"; ?>
    </header>

    <!-- 文章內容區 -->
    <div class="article-container">
        <h1>主題1</h1>
        <div class="content-area">
            <textarea class="content-textarea" placeholder="內容..." disabled></textarea>
        </div>
        <div class="user-info">
            <span>發布者: user1013</span>
        </div>

        <!-- 留言區 -->
        <div class="comment-section">
            <h2>留言區</h2>
            <div class="comments-container">
                <!-- 增加更多假資料模擬留言 -->
                <div class="comment">
                    <span class="comment-user">user001:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user002:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user1013:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user003:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user004:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user005:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user006:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user007:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user008:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user009:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user010:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user011:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment">
                    <span class="comment-user">user012:</span>
                    <span class="comment-text">------</span>
                </div>
                <div class="comment-input-container">
                    <textarea class="comment-input" placeholder="輸入您的留言..."></textarea>
                    <button class="comment-submit">提交</button>
                </div>
                <!-- 更多假留言 -->
            </div>
            <!-- 留言輸入框（嵌入留言區） -->

        </div>
    </div>

    <script src="../js/luntanscript.js"></script>
</body>

</html>