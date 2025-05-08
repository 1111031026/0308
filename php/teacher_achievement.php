<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true || !isset($_SESSION['user_id'])) {
    // 如果未登入，重定向到登入頁面或顯示錯誤訊息
    header('Location: user_login.php'); // 假設登入頁面是 user_login.php
    exit;
}

$userID = $_SESSION['user_id'];
$totalPoints = 0;
$sdg13Count = 0;
$sdg14Count = 0;
$sdg15Count = 0;

// 從 teacher_achievement 資料表讀取總積分
$stmt = $conn->prepare("SELECT TotalPoints FROM teacher_achievement WHERE UserID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $totalPoints = $row['TotalPoints'] ?? 0;
} else {
    error_log("Teacher achievement record not found for UserID: " . $userID);
    // 可以考慮插入記錄，但註冊時應已處理
}
$stmt->close();

// 從 article 資料表計算各 SDG 分類的文章數量
$categories = ['sdg13', 'sdg14', 'sdg15'];
$articleCounts = ['sdg13' => 0, 'sdg14' => 0, 'sdg15' => 0];

$sql = "SELECT Category, COUNT(*) as count FROM article WHERE UserID = ? AND Category IN (?, ?, ?) GROUP BY Category";
$stmtArticles = $conn->prepare($sql);
$stmtArticles->bind_param("isss", $userID, $categories[0], $categories[1], $categories[2]);
$stmtArticles->execute();
$resultArticles = $stmtArticles->get_result();

while ($rowArticle = $resultArticles->fetch_assoc()) {
    if (isset($articleCounts[$rowArticle['Category']])) {
        $articleCounts[$rowArticle['Category']] = $rowArticle['count'];
    }
}
$stmtArticles->close();

$sdg13Count = $articleCounts['sdg13'];
$sdg14Count = $articleCounts['sdg14'];
$sdg15Count = $articleCounts['sdg15'];

// Prepare update statement for teacher_achievement to update article counts only
$updateSql = "UPDATE teacher_achievement SET SDG13ArticlesPublished = ?, SDG14ArticlesPublished = ?, SDG15ArticlesPublished = ? WHERE UserID = ?";
$stmtUpdate = $conn->prepare($updateSql);
if ($stmtUpdate === false) {
    error_log("Prepare update teacher_achievement article counts failed for UserID " . $userID . ": " . $conn->error);
    // Keep the initially fetched totalPoints if prepare fails
} else {
    // Bind parameters for article counts and UserID
    $stmtUpdate->bind_param("iiii", $sdg13Count, $sdg14Count, $sdg15Count, $userID);
    if (!$stmtUpdate->execute()) {
        error_log("Execute update teacher_achievement article counts failed for UserID " . $userID . ": " . $stmtUpdate->error);
        // Keep the initially fetched totalPoints if execute fails
    } else {
        // Article counts updated successfully. TotalPoints remains unchanged from the initial fetch.
    }
    $stmtUpdate->close();
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>教師成就 - 永續小站</title> 
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/achievement.css"> 
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script> 
    <script src="https://code.highcharts.com/modules/exporting.js"></script> 
    <script src="https://code.highcharts.com/modules/export-data.js"></script> 
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="achievement-container">
        <h1>教師成就</h1> 

        <div class="total-points-section">
            <h2>總積分</h2>
            <p class="points-display"><?php echo htmlspecialchars($totalPoints); ?> 分</p>
        </div>

        <div class="stats-section">
            <h2>發布文章統計</h2> 
            <!-- 移除學生統計項目 -->
            <div id="container" class="chart-container"></div> 
        </div>

    </div>

    <script>
    // Data retrieved from PHP
    const sdg13Data = <?php echo $sdg13Count; ?>;
    const sdg14Data = <?php echo $sdg14Count; ?>;
    const sdg15Data = <?php echo $sdg15Count; ?>;

    // Create the chart using user-provided code structure
    Highcharts.chart('container', {
        chart: {
            type: 'column',
            backgroundColor: 'transparent' // 使背景透明
        },
        title: {
            text: 'SDG 文章發布數量統計',
            style: {
                color: '#333' // 標題顏色
            }
        },
        subtitle: {
            text: '顯示您在各 SDG 分類中發布的文章數量'
        },
        accessibility: {
            announceNewData: {
                enabled: true
            }
        },
        xAxis: {
            type: 'category',
            labels: {
                style: {
                    color: '#555'
                }
            }
        },
        yAxis: {
            title: {
                text: '文章數量',
                style: {
                    color: '#555'
                }
            },
            labels: {
                style: {
                    color: '#555'
                }
            },
            min: 0 // 確保 Y 軸從 0 開始
        },
        legend: {
            enabled: false
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y} 篇' // 修改格式以顯示篇數
                }
            }
        },

        tooltip: {
            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y} 篇</b><br/>' // 修改提示框格式
        },

        series: [
            {
                name: 'SDG 文章',
                colorByPoint: true,
                data: [
                    {
                        name: 'SDG 13 氣候行動',
                        y: sdg13Data,
                        // drilldown: 'SDG13' // 如果需要下鑽功能，可以取消註釋
                    },
                    {
                        name: 'SDG 14 海洋生態',
                        y: sdg14Data,
                        // drilldown: 'SDG14'
                    },
                    {
                        name: 'SDG 15 陸地生態',
                        y: sdg15Data,
                        // drilldown: 'SDG15'
                    }
                ]
            }
        ],
        // 如果需要下鑽功能，可以在此處定義 drilldown series
        // drilldown: {
        //     breadcrumbs: {
        //         position: {
        //             align: 'right'
        //         }
        //     },
        //     series: [
        //         {
        //             name: 'SDG 13',
        //             id: 'SDG13',
        //             data: [
        //                 // 這裡可以放 SDG13 相關的更細數據
        //             ]
        //         },
        //         // ... 其他 SDG 的下鑽數據
        //     ]
        // },
        credits: {
            enabled: false // 隱藏 Highcharts logo
        }
    });
    </script>
</body>
</html>