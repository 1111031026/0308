<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站 - 查看所有題目</title>
    <link rel="stylesheet" href="../css/view-all-qusetion.css">
</head>
<body>
    <header>
        <?php
        include "nav.html";
        ?>
    </header>


    <!-- 題目表格 -->
    <div class="table-container">
        <table class="question-table">
            <thead>
                <tr>
                    <th>題目編號</th>
                    <th>內文</th>
                    <th>類型</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <!-- 假資料模擬資料庫讀取 -->
                <tr>
                    <td>001</td>
                    <td>------</td>
                    <td>選擇</td>
                    <td><button class="edit-btn" onclick="window.location.href='2.html'">返回編輯</button></td>
                </tr>
                <tr>
                    <td>002</td>
                    <td>------</td>
                    <td>填空</td>
                    <td><button class="edit-btn" onclick="window.location.href='2.html'">返回編輯</button></td>
                </tr>
                <tr>
                    <td>003</td>
                    <td>------</td>
                    <td>填空</td>
                    <td><button class="edit-btn" onclick="window.location.href='2.html'">返回編輯</button></td>
                </tr>
                <tr>
                    <td>004</td>
                    <td>------</td>
                    <td>是非</td>
                    <td><button class="edit-btn" onclick="window.location.href='2.html'">返回編輯</button></td>
                </tr>
                <tr>
                    <td>005</td>
                    <td>------</td>
                    <td>選擇</td>
                    <td><button class="edit-btn" onclick="window.location.href='2.html'">返回編輯</button></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 控制按鈕 -->
    <div class="control-buttons">
        <button class="control-btn add-new" onclick="window.location.href='2.php'">增加新的題目</button>
        <button class="control-btn submit-all" onclick="window.location.href='1.php'">確認送出所有題目</button>
    </div>
</body>
</html>