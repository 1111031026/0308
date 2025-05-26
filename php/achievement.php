<?php
session_start();
require_once 'db_connect.php';

// 檢查是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true || !isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit;
}

// 獲取要顯示的用戶 ID（從 URL 或當前登入用戶）
$display_user_id = isset($_GET['user_id']) && filter_var($_GET['user_id'], FILTER_VALIDATE_INT) ? (int)$_GET['user_id'] : $_SESSION['user_id'];

// 驗證用戶是否存在
$stmt = $conn->prepare("SELECT Username FROM user WHERE UserID = ?");
$stmt->bind_param("i", $display_user_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->fetch_assoc()) {
    // 如果用戶不存在，重定向或顯示錯誤
    header('Location: luntan.php');
    exit;
}
$stmt->close();

// 查詢成就數據
$totalPoints = 0;
$articlesViewed = 0;
$choiceCorrect = 0;
$tfCorrect = 0;
$fillinCorrect = 0;

$stmt = $conn->prepare("SELECT TotalPoints, ArticlesViewed, ChoiceQuestionsCorrect, TFQuestionsCorrect, FillinQuestionsCorrect FROM achievement WHERE UserID = ?");
$stmt->bind_param("i", $display_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $totalPoints = $row['TotalPoints'] ?? 0;
    $articlesViewed = $row['ArticlesViewed'] ?? 0;
    $choiceCorrect = $row['ChoiceQuestionsCorrect'] ?? 0;
    $tfCorrect = $row['TFQuestionsCorrect'] ?? 0;
    $fillinCorrect = $row['FillinQuestionsCorrect'] ?? 0;
} else {
    error_log("Achievement record not found for UserID: " . $display_user_id);
}
$stmt->close();

// 查詢本週文章瀏覽數據
$weeklyViews = array_fill(0, 7, 0);
$today = new DateTime();
$dayOfWeek = $today->format('N');
$startOfWeek = clone $today;
$startOfWeek->modify('-' . ($dayOfWeek - 1) . ' days');
$startOfWeek->setTime(0, 0, 0);
$endOfWeek = clone $startOfWeek;
$endOfWeek->modify('+6 days');
$endOfWeek->setTime(23, 59, 59);

$viewStmt = $conn->prepare("SELECT ViewTimestamp FROM user_article_views WHERE UserID = ? AND ViewTimestamp BETWEEN ? AND ?");
$startOfWeekStr = $startOfWeek->format('Y-m-d H:i:s');
$endOfWeekStr = $endOfWeek->format('Y-m-d H:i:s');
$viewStmt->bind_param("iss", $display_user_id, $startOfWeekStr, $endOfWeekStr);
$viewStmt->execute();
$viewResult = $viewStmt->get_result();

while ($viewRow = $viewResult->fetch_assoc()) {
    $viewTimestamp = new DateTime($viewRow['ViewTimestamp']);
    $viewDayOfWeek = (int)$viewTimestamp->format('N');
    if ($viewDayOfWeek >= 1 && $viewDayOfWeek <= 7) {
        $weeklyViews[$viewDayOfWeek - 1]++;
    }
}
$viewStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $display_user_id === $_SESSION['user_id'] ? '我的成就' : '用戶成就'; ?> - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/achievement.css">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
</head>

<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="achievement-container">
        <h1><?php echo $display_user_id === $_SESSION['user_id'] ? '我的成就' : '用戶成就'; ?></h1>
        <?php if ($totalPoints == 0 && $articlesViewed == 0 && $choiceCorrect == 0 && $tfCorrect == 0 && $fillinCorrect == 0): ?>
            <p class="no-data">此用戶尚未有成就數據。</p>
        <?php else: ?>
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
                <div id="articles-viewed-chart-container" class="chart-container" style="margin-top: 30px;"></div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        Highcharts.chart('pie-chart-container', {
            chart: {
                type: 'pie',
                backgroundColor: 'transparent'
            },
            title: {
                text: '答題正確率分佈',
                style: {
                    color: '#333'
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
                    dataLabels: [{
                            enabled: true,
                            distance: 20,
                            format: '{point.name}: {point.y} 題',
                            style: {
                                color: '#555'
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
                                color: '#fff'
                            },
                            filter: {
                                operator: '>',
                                property: 'percentage',
                                value: 5
                            }
                        }
                    ],
                    showInLegend: true
                }
            },
            series: [{
                name: '數量',
                colorByPoint: true,
                data: [{
                        name: '選擇題答對',
                        y: <?php echo $choiceCorrect; ?>,
                        color: '#7cb5ec'
                    },
                    {
                        name: '是非題答對',
                        y: <?php echo $tfCorrect; ?>,
                        color: '#90ed7d'
                    },
                    {
                        name: '填充題答對',
                        y: <?php echo $fillinCorrect; ?>,
                        color: '#f7a35c'
                    }
                ]
            }],
            credits: {
                enabled: false
            }
        });

        Highcharts.chart('articles-viewed-chart-container', {
            chart: {
                type: 'line',
                backgroundColor: 'transparent'
            },
            title: {
                text: '本週瀏覽文章數趨勢'
            },
            xAxis: {
                categories: ['一', '二', '三', '四', '五', '六', '日']
            },
            yAxis: {
                title: {
                    text: '數量'
                },
                min: 0,
                max: 20,
                tickInterval: 5
            },
            tooltip: {
                headerFormat: '<b>星期{point.key}</b><br/>',
                pointFormat: '瀏覽: {point.y} 篇'
            },
            series: [{
                name: '瀏覽文章數',
                data: <?php echo json_encode($weeklyViews); ?>
            }],
            credits: {
                enabled: false
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                line: {
                    marker: {
                        enabled: true
                    }
                }
            }
        });
    </script>
</body>

</html>
