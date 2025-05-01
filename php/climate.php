<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>氣候行動 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav2.css">
    <link rel="stylesheet" href="../css/climate.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <!-- 引入 Noto Sans 思源黑體 -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="page-container">
        <!-- 第一區塊：背景圖片與標題 -->
        <section class="section section-1">
            <div class="ocean-banner">
                <img src="../img/Climate-change.jpg" alt="氣候變遷背景">
            </div>
            <h1>氣候行動</h1>
            <div class="scroll-down-arrow"><span>⇩</span></div>
        </section>

        <!-- 第二區塊：關於海洋永續 -->
        <section class="section section-2">
            <div class="intro-section">
                <h2>關於氣候行動</h2>
                <p>氣候變遷是當今人類面臨的最大環境挑戰之一，它影響著地球上的每個生態系統和人類社會。在永續小站，我們致力於提供氣候變遷的科學知識，分享減緩與適應策略，鼓勵每個人採取行動，共同守護我們的地球家園。</p>
            </div>
        </section>

        <!-- 第三區塊：最新貼文（動態輪播） -->
        <section class="section section-3">
            <h3>最新貼文</h3>
            <div class="container">
                <!-- 滾動文章區域 -->
                <div class="article-slider">
                    <div class="article-content">
                        <?php
                        require_once 'db_connect.php';
                        // 查詢SDG13分類的文章，按創建時間排序
                        $sql = "SELECT * FROM article WHERE Category = 'sdg13' ORDER BY created_at DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="article-card">';
                                echo '<h4>' . htmlspecialchars($row['Title']) . '</h4>';
                                if ($row['ImageURL']) {
                                    echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片">';
                                } else {
                                    echo '<img src="../img/Climate-change.jpg" alt="預設圖片">';
                                }
                                echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
                                echo '<a href="article.php?id=' . $row['ArticleID'] . '" class="read-more">閱讀更多</a>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="article-card">';
                            echo '<h4>暫無相關文章</h4>';
                            echo '<p>目前沒有氣候行動相關的文章。</p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <!-- 左右按鈕 -->
                    <button class="slider-btn2 prev-btn"><</button>
                    <button class="slider-btn2 next-btn">></button>
                </div>
            </div>
        </section>
        <!-- 第四區塊：搜尋文章 -->
        <section class="section section-4">
            <h3>搜尋氣候文章</h3>
            <div class="search-container">
                <form id="search-form">
                    <input type="text" name="search" id="search-input" placeholder="輸入關鍵字搜尋..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">搜尋</button>
                </form>
            </div>
            <div class="posts-grid" id="posts-grid">
                <!-- 文章將通過 AJAX 動態加載 -->
            </div>
            <div class="pagination">
                <button class="pagination-btn prev-btn" id="prev-btn" disabled>上一頁</button>
                <div class="page-input-container">
                    <input type="number" id="page-input" min="1" value="1" class="page-input">
                    <span>/ <span id="total-pages">1</span> 頁</span>
                </div>
                <button class="pagination-btn next-btn" id="next-btn">下一頁</button>
            </div>
        </section>
    </div>
    <script>
        $(document).ready(function() {
            let navbar = $('.navbar');
            let currentPage = 1;
            let totalPages = 1;
            let searchQuery = $('#search-input').val();
            // 監聽滑鼠移動事件
            $(document).mousemove(function(e) {
                if (e.clientY <= 100) {
                    navbar.css('transform', 'translateY(100px)');
                } else {
                    navbar.css('transform', 'translateY(-100px)');
                }
            });
            // 初始化Slick輪播
            $('.article-content').slick({
                infinite: true,
                slidesToShow: 3,
                slidesToScroll: 1,
                prevArrow: $('.slider-btn2.prev-btn'),
                nextArrow: $('.slider-btn2.next-btn'),
                dots: false,
                autoplay: true,
                autoplaySpeed: 3000,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
            // 載入文章函數
            function loadArticles(page, search) {
                $('#posts-grid').html('<p>載入中...</p>');
                $.ajax({
                    url: 'fetch_climate.php',
                    method: 'GET',
                    data: { page: page, search: search },
                    dataType: 'json',
                    success: function(response) {
                        const postsGrid = $('#posts-grid');
                        postsGrid.empty();
                        if (response.articles.length > 0) {
                            response.articles.forEach(article => {
                                postsGrid.append(`
                                    <div class="post-card">
                                        <img src="${article.ImageURL}" alt="文章圖片">
                                        <h4>${article.Title}</h4>
                                        <p>${article.Description}</p>
                                        <a href="article.php?id=${article.ArticleID}" class="read-more">閱讀更多</a>
                                    </div>
                                `);
                            });
                        } else {
                            postsGrid.append(`
                                <div class="post-card">
                                    <h4>暫無相關文章</h4>
                                    <p>沒有找到符合條件的文章。</p>
                                </div>
                            `);
                        }
                        totalPages = response.totalPages;
                        $('#total-pages').text(totalPages);
                        $('#page-input').val(page);
                        $('#page-input').attr('max', totalPages);
                          // 更新按鈕狀態
                        $('#prev-btn').prop('disabled', page === 1);
                        $('#next-btn').prop('disabled', page >= totalPages);
                    },
                    error: function() {
                        $('#posts-grid').html('<p>載入文章失敗，請稍後重試。</p>');
                    }
                });
            }
            // 初始載入第一頁
            loadArticles(currentPage, searchQuery);
            // 搜尋表單提交
            $('#search-form').on('submit', function(e) {
                e.preventDefault();
                searchQuery = $('#search-input').val();
                currentPage = 1;
                loadArticles(currentPage, searchQuery);
            });
            // 分頁按鈕點擊
            $('#prev-btn').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    loadArticles(currentPage, searchQuery);
                }
            });
            $('#next-btn').on('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadArticles(currentPage, searchQuery);
                }
            });
            // 監聽頁碼輸入框的Enter鍵事件
            $('#page-input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    let pageNum = parseInt($(this).val());
                    if (pageNum < 1) { pageNum = 1; }
                    else if (pageNum > totalPages) { pageNum = totalPages; }
                    if (pageNum !== currentPage) {
                        currentPage = pageNum;
                        loadArticles(currentPage, searchQuery);
                    }
                }
            });
            // 當輸入框失去焦點時也進行頁面跳轉
            $('#page-input').on('blur', function() {
                let pageNum = parseInt($(this).val());
                if (pageNum < 1) { pageNum = 1; $(this).val(1); }
                else if (pageNum > totalPages) { pageNum = totalPages; $(this).val(totalPages); }
                if (pageNum !== currentPage) {
                    currentPage = pageNum;
                    loadArticles(currentPage, searchQuery);
                }
            });
        });
    </script>
</body>
</html>