<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true || !isset($_SESSION['user_id'])) {
    // 如果未登入，重定向到登入頁面或顯示錯誤訊息
    header('Location: user_login.php'); // 假設登入頁面是 user_login.php
    exit;
}

$userID = $_SESSION['user_id'];
$totalPoints = 0;
$articlesViewed = 0;
$choiceCorrect = 0;
$tfCorrect = 0;
$fillinCorrect = 0;

// 從 achievement 資料表讀取數據
$stmt = $conn->prepare("SELECT TotalPoints, ArticlesViewed, ChoiceQuestionsCorrect, TFQuestionsCorrect, FillinQuestionsCorrect FROM achievement WHERE UserID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $totalPoints = $row['TotalPoints'] ?? 0;
    $articlesViewed = $row['ArticlesViewed'] ?? 0;
    $choiceCorrect = $row['ChoiceQuestionsCorrect'] ?? 0;
    $tfCorrect = $row['TFQuestionsCorrect'] ?? 0;
    $fillinCorrect = $row['FillinQuestionsCorrect'] ?? 0;
} else {
    // 如果找不到用戶記錄（理論上不應發生，因為註冊時會創建），則全部設為 0
    // 或者可以考慮在此處插入一條初始記錄，但最好是在註冊流程中處理
    error_log("Achievement record not found for UserID: " . $userID);
}
$stmt->close();

// --- New code start ---
// Get weekly article view counts
$weeklyViews = array_fill(0, 7, 0); // Initialize array for Mon-Sun with 0s
$today = new DateTime();
$dayOfWeek = $today->format('N'); // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)

// Calculate the start of the week (Monday)
$startOfWeek = clone $today;
$startOfWeek->modify('-' . ($dayOfWeek - 1) . ' days');
$startOfWeek->setTime(0, 0, 0);

// Calculate the end of the week (Sunday)
$endOfWeek = clone $startOfWeek;
$endOfWeek->modify('+6 days');
$endOfWeek->setTime(23, 59, 59);

// Prepare SQL query to get views for the current week
$viewStmt = $conn->prepare("SELECT ViewTimestamp FROM user_article_views WHERE UserID = ? AND ViewTimestamp BETWEEN ? AND ?");
$startOfWeekStr = $startOfWeek->format('Y-m-d H:i:s');
$endOfWeekStr = $endOfWeek->format('Y-m-d H:i:s');
$viewStmt->bind_param("iss", $userID, $startOfWeekStr, $endOfWeekStr);
$viewStmt->execute();
$viewResult = $viewStmt->get_result();

while ($viewRow = $viewResult->fetch_assoc()) {
    $viewTimestamp = new DateTime($viewRow['ViewTimestamp']);
    $viewDayOfWeek = (int)$viewTimestamp->format('N'); // 1 (for Monday) through 7 (for Sunday)
    if ($viewDayOfWeek >= 1 && $viewDayOfWeek <= 7) {
        $weeklyViews[$viewDayOfWeek - 1]++; // Increment count for the corresponding day (index 0 for Monday)
    }
}
$viewStmt->close();
// --- New code end ---

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的成就 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav.css"> 
    <link rel="stylesheet" href="../css/achievement.css"> 
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="achievement-container">
        <h1>我的成就</h1>

        <div class="total-points-section">
            <h2>總積分</h2>
            <p class="points-display"><?php echo htmlspecialchars($totalPoints); ?> 分</p>
        </div>

        <div class="stats-section">
            <h2>成就統計</h2>
            <div class="stat-item">
                <h3>瀏覽文章數</h3>
                <p><?php echo htmlspecialchars($articlesViewed); ?></p>
            </div>
            <div id="pie-chart-container" class="chart-container"></div>
            <!-- 新增折線圖容器 -->
            <div id="articles-viewed-chart-container" class="chart-container" style="margin-top: 30px;"></div> 
        </div>

    </div>

    <script>
        Highcharts.chart('pie-chart-container', {
            chart: {
                type: 'pie',
                backgroundColor: 'transparent' // 使背景透明
            },
            title: {
                text: '答題正確率分佈',
                style: {
                    color: '#333' // 標題顏色
                }
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y} 題</b> ({point.percentage:.1f}%)'
            },
            accessibility: {
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: [
                        {
                            enabled: true,
                            distance: 20,
                            format: '{point.name}: {point.y} 題',
                            style: {
                                color: '#555' // 標籤顏色
                            }
                        }, 
                        {
                            enabled: true,
                            distance: -40,
                            format: '{point.percentage:.1f}%',
                            style: {
                                fontSize: '1em',
                                textOutline: 'none',
                                opacity: 0.9,
                                color: '#fff' // 百分比文字顏色
                            },
                            filter: {
                                operator: '>',
                                property: 'percentage',
                                value: 5 // 只有大於 5% 的顯示百分比
                            }
                        }
                    ],
                    showInLegend: true // 顯示圖例
                }
            },
            series: [{
                name: '數量',
                colorByPoint: true,
                data: [
                    // 移除瀏覽文章數
                    // {
                    //     name: '瀏覽文章數',
                    //     y: <?php echo $articlesViewed; ?>,
                    //     color: '#f45b5b' // 瀏覽文章數顏色
                    // },
                    {
                        name: '選擇題答對',
                        y: <?php echo $choiceCorrect; ?>, // 使用從 achievement 表讀取的數據
                        color: '#7cb5ec' // 選擇題顏色
                    },
                    {
                        name: '是非題答對',
                        y: <?php echo $tfCorrect; ?>, // 使用從 achievement 表讀取的數據
                        color: '#90ed7d' // 是非題顏色
                    },
                    {
                        name: '填充題答對',
                        y: <?php echo $fillinCorrect; ?>, // 使用從 achievement 表讀取的數據
                        color: '#f7a35c' // 填充題顏色
                    }
                ]
            }],
            credits: {
                enabled: false // 隱藏 Highcharts logo
            }
        });

        // 新增瀏覽文章數折線圖
        Highcharts.chart('articles-viewed-chart-container', {
            chart: {
                type: 'line', // Ensure it's a line chart
                backgroundColor: 'transparent'
            },
            title: {
                text: '本週瀏覽文章數趨勢' // Update title
            },
            xAxis: {
                // Change categories to days of the week
                categories: ['一', '二', '三', '四', '五', '六', '日']
            },
            yAxis: {
                title: {
                    text: '數量'
                },
                min: 0, // Set Y-axis min
                max: 20, // Set Y-axis max
                tickInterval: 5 // Set Y-axis tick interval
            },
            tooltip: {
                // Update tooltip format if needed, maybe show day name?
                headerFormat: '<b>星期{point.key}</b><br/>',
                pointFormat: '瀏覽: {point.y} 篇'
            },
            series: [{
                name: '瀏覽文章數',
                // Use the weekly data from PHP
                data: <?php echo json_encode($weeklyViews); ?>
            }],
            credits: {
                enabled: false
            },
            legend: {
                enabled: false // Keep legend hidden for single series
            },
            plotOptions: { // Ensure lines connect points
                line: {
                    marker: {
                        enabled: true // Show markers on points
                    }
                }
            }
        });
    </script>

</body>
</html>