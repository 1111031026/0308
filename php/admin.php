<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站 - 題目管理</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="admin-container">
        <h1>題目管理系統</h1>
        
        <!-- 選擇題區塊 -->
        <section class="quiz-section">
            <h2>選擇題暫存區</h2>
            <table class="quiz-table">
                <thead>
                    <tr>
                        <th>題目編號</th>
                        <th>題目內容</th>
                        <th>選項A</th>
                        <th>選項B</th>
                        <th>選項C</th>
                        <th>選項D</th>
                        <th>正確答案</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'db_connect.php';
                    if ($conn) {
                        $sql = "SELECT * FROM choicequizstagingarea";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['QuestionID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['QuestionText']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['OptionA']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['OptionB']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['OptionC']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['OptionD']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['CorrectAnswer']) . "</td>";
                                echo "<td><button class='publish-btn' onclick=\"publishQuestion('choice', " . $row['QuestionID'] . ")\">上架</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>目前沒有任何題目</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>資料庫連線失敗</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- 填空題區塊 -->
        <section class="quiz-section">
            <h2>填空題暫存區</h2>
            <table class="quiz-table">
                <thead>
                    <tr>
                        <th>題目編號</th>
                        <th>題目內容</th>
                        <th>正確答案</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($conn) {
                        $sql = "SELECT * FROM fillquizstagingarea";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['QuestionID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['QuestionText']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['CorrectAnswer']) . "</td>";
                                echo "<td><button class='publish-btn' onclick=\"publishQuestion('fill', " . $row['QuestionID'] . ")\">上架</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>目前沒有任何題目</td></tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- 是非題區塊 -->
        <section class="quiz-section">
            <h2>是非題暫存區</h2>
            <table class="quiz-table">
                <thead>
                    <tr>
                        <th>題目編號</th>
                        <th>題目內容</th>
                        <th>正確答案</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($conn) {
                        $sql = "SELECT * FROM tfquizstagingarea";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['QuestionID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['QuestionText']) . "</td>";
                                echo "<td>" . ($row['CorrectAnswer'] == '1' ? '是' : '否') . "</td>";
                                echo "<td><button class='publish-btn' onclick=\"publishQuestion('tf', " . $row['QuestionID'] . ")\">上架</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>目前沒有任何題目</td></tr>";
                        }
                        $conn->close();
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>

    <script>
        function publishQuestion(type, id) {
            fetch('publish_question.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: type,
                    id: id
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();image.png
            })
            .then(data => {
                if (data.success) {
                    alert('題目上架成功！');
                    location.reload();
                } else {
                    alert('上架失敗：' + (data.message || '未知錯誤'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('上架過程中發生錯誤');
            });
        }
    </script>
</body>
</html>