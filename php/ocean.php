<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>海洋永續 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav2.css">
    <link rel="stylesheet" href="../css/ocean.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* 為每個 info-card-front 設置特定的背景圖片 */
        .section-3 .info-card:nth-child(1) .info-card-front {
            background-image: url('../img/ocean1.jpg');
        }
        .section-3 .info-card:nth-child(2) .info-card-front {
            background-image: url('../img/ocean1.jpg');
        }
        .section-3 .info-card:nth-child(3) .info-card-front {
            background-image: url('../img/ocean1.jpg');
        }
        .section-3 .info-card:nth-child(4) .info-card-front {
            background-image: url('../img/ocean1.jpg');
        }
    </style>
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>
    <div class="page-container">
        <!-- 第一區塊：背景影片與標題 -->
        <section class="section section-1">
            <div class="ocean-banner">
                <video autoplay loop muted playsinline>
                    <source src="../img/shore.mp4" type="video/mp4">
                    您的瀏覽器不支援影片播放
                </video>
            </div>
            <h1>海洋永續</h1>
            <p class="intro-text">海洋永續是保護海洋生態系統與資源的關鍵環境議題。我們致力於守護珊瑚礁、海洋生物多樣性與潔淨水域，推動永續發展，為未來保留藍色星球的生命力。</p>
            <div class="scroll-down-arrow"><span>⇩</span></div>
        </section>
        <!-- 第二區塊（原第三區塊）：最新貼文 + 翻轉卡片 -->
        <section class="section section-3">
            <div class="info-section">
                <h3>關於海洋永續</h3>
                <div class="ocean-info-grid">
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <h3>海洋生態系統</h3>
                            </div>
                            <div class="info-card-back">
                                <div class="info-content">
                                    <h3>海洋生態系統</h3>
                                    <p>海洋覆蓋地球70%以上的面積，包含珊瑚礁、紅樹林與深海生態，是全球生物多樣性的核心。這些系統提供食物、氧氣並調節氣候。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <h3>海洋污染</h3>
                            </div>
                            <div class="info-card-back">
                                <div class="info-content">
                                    <h3>海洋污染</h3>
                                    <p>塑膠垃圾、化學物質與油污嚴重威脅海洋健康，破壞食物鏈並危害海洋生物。減少污染是保護海洋的迫切任務。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <h3>氣候變遷</h3>
                            </div>
                            <div class="info-card-back">
                                <div class="info-content">
                                    <h3>氣候變遷影響</h3>
                                    <p>氣候變遷導致海平面上升、海洋酸化與珊瑚白化，威脅海洋生態與沿海社區。減緩氣候影響是海洋永續的關鍵。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <h3>永續行動</h3>
                            </div>
                            <div class="info-card-back">
                                <div class="info-content">
                                    <h3>永續行動</h3>
                                    <p>永續小站推廣海洋保護教育，組織淨灘、珊瑚復育與漁業管理計畫，透過行動與政策實現海洋的永續未來。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="articles-section">
                <h3>最新貼文</h3>
                <div class="container">
                    <div class="article-slider">
                        <div class="article-content">
                            <?php
                            require_once 'db_connect.php';
                            $sql = "SELECT * FROM article WHERE Category = 'sdg14' ORDER BY created_at DESC";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<div class="article-card">';
                                    if ($row['ImageURL']) {
                                        echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片">';
                                    } else {
                                        echo '<img src="../img/ocean.jpg" alt="預設圖片">';
                                    }
                                    echo '<div class="land-card-content">';
                                    echo '<h4 class="land-card-title">' . htmlspecialchars($row['Title']) . '</h4>';
                                    echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
                                    echo '<a href="article.php?id=' . $row['ArticleID'] . '" class="land-card-button read-more">閱讀更多</a>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="article-card">';
                                echo '<div class="land-card-content">';
                                echo '<h4 class="land-card-title">暫無相關文章</h4>';
                                echo '<p>目前沒有海洋永續相關的文章。</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        <button class="slider-btn2 prev-btn"><</button>
                        <button class="slider-btn2 next-btn">></button>
                    </div>
                </div>
            </div>
        </section>
        <!-- 第三區塊（原第四區塊）：搜尋文章 -->
        <section class="section section-4">
            <video autoplay loop muted playsinline>
                <source src="../img/shore2.mp4" type="video/mp4">
                您的瀏覽器不支援影片播放
            </video>
            <h3>搜尋海洋文章</h3>
            <div class="search-container">
                <form id="search-form">
                    <input type="text" name="search" id="search-input" placeholder="輸入關鍵字搜尋..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">搜尋</button>
                </form>
            </div>
            <div class="posts-grid" id="posts-grid"></div>
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
            $(document).mousemove(function(e) {
                if (e.clientY <= 100) {
                    navbar.css('transform', 'translateY(100px)');
                } else {
                    navbar.css('transform', 'translateY(-100px)');
                }
            });
            $('.article-content').slick({
                infinite: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                variableWidth: true,
                prevArrow: $('.prev-btn'),
                nextArrow: $('.next-btn'),
                responsive: [{
                    breakpoint: 1400,
                    settings: {
                        slidesToShow: 1
                    }
                }, {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 1
                    }
                }, {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1
                    }
                }]
            });
            function loadArticles(page, search) {
                $('#posts-grid').html('<p>載入中...</p>');
                $.ajax({
                    url: 'fetch_ocean.php',
                    method: 'GET',
                    data: {
                        page: page,
                        search: search
                    },
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
                        $('#prev-btn').prop('disabled', page === 1);
                        $('#next-btn').prop('disabled', page >= totalPages);
                    },
                    error: function() {
                        $('#posts-grid').html('<p>載入文章失敗，請稍後重試。</p>');
                    }
                });
            }
            loadArticles(currentPage, searchQuery);
            $('#search-form').on('submit', function(e) {
                e.preventDefault();
                searchQuery = $('#search-input').val();
                currentPage = 1;
                loadArticles(currentPage, searchQuery);
            });
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
            $('#page-input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    let pageNum = parseInt($(this).val());
                    if (pageNum < 1) pageNum = 1;
                    else if (pageNum > totalPages) pageNum = totalPages;
                    if (pageNum !== currentPage) {
                        currentPage = pageNum;
                        loadArticles(currentPage, searchQuery);
                    }
                }
            });
            $('#page-input').on('blur', function() {
                let pageNum = parseInt($(this).val());
                if (pageNum < 1) {
                    pageNum = 1;
                    $(this).val(1);
                } else if (pageNum > totalPages) {
                    pageNum = totalPages;
                    $(this).val(totalPages);
                }
                if (pageNum !== currentPage) {
                    currentPage = pageNum;
                    loadArticles(currentPage, searchQuery);
                }
            });
        });
    </script>
</body>
</html>