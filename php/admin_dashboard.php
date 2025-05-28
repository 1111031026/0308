<?php
session_start();

// 檢查是否為管理員登入
if (!isset($_SESSION['login_session']) || $_SESSION['role'] !== 'Admin') {
    header("Location: user_login.php");
    exit();
}

// 包含數據庫連接
include 'db_connect.php';

// 獲取統計數據
$stats = array();

// 用戶總數
$result = $conn->query("SELECT COUNT(*) as total FROM user");
$stats['total_users'] = $result->fetch_assoc()['total'];

// 商品總數
$result = $conn->query("SELECT COUNT(*) as total FROM merchandise");
$stats['total_products'] = $result->fetch_assoc()['total'];

// 討論區文章總數
$result = $conn->query("SELECT COUNT(*) as total FROM article");
$stats['total_posts'] = $result->fetch_assoc()['total'];

// 獲取文章分類數據
$category_stats = $conn->query("
    SELECT Category, COUNT(*) as count 
    FROM article 
    GROUP BY Category 
    ORDER BY count DESC
");

// 獲取最近30天的文章發布趨勢
$article_trend = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM article 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
");

// 獲取最活躍用戶
$active_users = $conn->query("
    SELECT u.Username, COUNT(a.ArticleID) as article_count
    FROM user u
    JOIN article a ON u.UserID = a.UserID
    GROUP BY u.UserID, u.Username
    ORDER BY article_count DESC
    LIMIT 10
");

// 準備圖表數據
$category_labels = [];
$category_data = [];
while ($row = $category_stats->fetch_assoc()) {
    $category_labels[] = $row['Category'];
    $category_data[] = $row['count'];
}

$trend_dates = [];
$trend_counts = [];
while ($row = $article_trend->fetch_assoc()) {
    $trend_dates[] = $row['date'];
    $trend_counts[] = $row['count'];
}

$active_usernames = [];
$active_user_counts = [];
while ($row = $active_users->fetch_assoc()) {
    $active_usernames[] = $row['Username'];
    $active_user_counts[] = $row['article_count'];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員儀表板</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <h1>管理員儀表板</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>總用戶數</h3>
                <p class="stat-number"><?php echo $stats['total_users']; ?></p>
                <a href="manage_users.php" class="card-link">管理用戶 →</a>
            </div>
            
            <div class="stat-card">
                <h3>商品總數</h3>
                <p class="stat-number"><?php echo $stats['total_products']; ?></p>
                <a href="merchandise_manage.php" class="card-link">管理商品 →</a>
            </div>
            
            <div class="stat-card">
                <h3>文章總數</h3>
                <p class="stat-number"><?php echo $stats['total_posts']; ?></p>
                <a href="manage_article.php" class="card-link">管理文章 →</a>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-section">
                <h2>文章分類分布</h2>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <div class="chart-section">
                <h2>最近30天文章發布趨勢</h2>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <div class="chart-section">
                <h2>最活躍用戶貢獻</h2>
                <div class="chart-container">
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h2>快速操作</h2>
            <div class="action-buttons">
                <a href="system_settings.php" class="action-button">系統設置</a>
                <a href="logout.php" class="action-button logout-btn">登出</a>
            </div>
        </div>
    </div>

    <style>
    .charts-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin: 20px 0;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .chart-section {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        width: 100%;
    }
    
    .chart-section h2 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #2c3e50;
        font-size: 1.2em;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    /* 調整圓餅圖的高度，因為它不需要那麼高 */
    #categoryChart {
        height:300px !important;
    }

    /* 讓趨勢圖和柱狀圖更高一些，以便更好地顯示數據 */
    #trendChart, #userChart {
        height: 300px !important;
    }

    /* 登出按鈕樣式 */
    .logout-btn {
        background-color: #e74c3c !important;
        color: white !important;
    }
    
    .logout-btn:hover {
        background-color: #c0392b !important;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 文章分類分布圓餅圖
        new Chart(document.getElementById('categoryChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($category_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($category_data); ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // 最近30天文章發布趨勢折線圖
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_dates); ?>,
                datasets: [{
                    label: '文章數量',
                    data: <?php echo json_encode($trend_counts); ?>,
                    borderColor: '#36A2EB',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // 最活躍用戶貢獻柱狀圖
        new Chart(document.getElementById('userChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($active_usernames); ?>,
                datasets: [{
                    label: '文章數量',
                    data: <?php echo json_encode($active_user_counts); ?>,
                    backgroundColor: '#4BC0C0'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // 添加卡片懸停效果
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
    </script>
</body>
</html> 