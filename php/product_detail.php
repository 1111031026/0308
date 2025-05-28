<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true) {
    header("Location: user_login.php");
    exit();
}

// 獲取商品ID
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id <= 0) {
    header("Location: shop.php");
    exit();
}

// 獲取商品詳細信息
$sql = "SELECT * FROM merchandise WHERE ItemID = ?"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: shop.php");
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

// 獲取用戶ID和點數
$user_id = $_SESSION['user_id'] ?? 0;
$points = 0;
$already_purchased = false;

if ($user_id > 0) {
    $role = $_SESSION['role'] ?? 'Student';
    $points_table = ($role === 'Teacher') ? 'teacher_achievement' : 'achievement';
    $sql = "SELECT TotalPoints FROM $points_table WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $points = $user['TotalPoints'] ?? 0;
    }
    $stmt->close();

    // 檢查用戶是否已經購買過該商品
    $sql = "SELECT * FROM purchase WHERE UserID = ? AND ItemID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $already_purchased = ($result->num_rows > 0);
    $stmt->close();
}

// 設置分類中文名稱
$category_name = '';
if ($product['Category'] == 'head') echo '頭像';
else if ($product['Category'] == 'background') echo '個人檔案背景';
else if ($product['Category'] == 'wallpaper') echo '桌布';
else $category_name = $product['Category'];
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['Name']); ?> - 購買</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/product_detail.css">
    <link rel="stylesheet" href="../css/nav3.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
    <!-- 添加 jQuery 和 elevateZoom-plus -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/igorlino/elevatezoom-plus@1.2.3/src/jquery.ez-plus.js"></script>
    <!-- 添加 Swiper JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
</head>

<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="product-detail-container">
        <div class="product-detail">
            <div class="product-images">
                <!-- 主圖輪播 -->
                <div class="swiper swiper-main">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img id="main-img" src="../<?php echo htmlspecialchars($product['ImageURL']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>" data-zoom-image="../<?php echo htmlspecialchars($product['ImageURL']); ?>">
                        </div>
                        <?php if (!empty($product['PreviewURL']) && $product['PreviewURL'] != 'NULL' && $product['PreviewURL'] != 'null'): ?>
                            <div class="swiper-slide">
                                <img src="../<?php echo htmlspecialchars($product['PreviewURL']); ?>" alt="預覽效果">
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['PreviewURL2']) && $product['PreviewURL2'] != 'NULL' && $product['PreviewURL2'] != 'null'): ?>
                            <div class="swiper-slide">
                                <img src="../<?php echo htmlspecialchars($product['PreviewURL2']); ?>" alt="預覽效果2">
                            </div>
                        <?php endif; ?>
                        <?php
                        // 如果有其他圖片，可以從資料庫中獲取並顯示
                        // 修改為從 merchandise 表獲取圖片
                        $sql = "SELECT ImageURL FROM merchandise WHERE ItemID = ?";
                        $stmt = $conn->prepare($sql);
                        if ($stmt) {
                            $stmt->bind_param("i", $item_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="swiper-slide">';
                                echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="' . htmlspecialchars($product['Name']) . '">';
                                echo '</div>';
                            }
                            $stmt->close();
                        }
                        ?>
                    </div>
                </div>

                <!-- 縮略圖輪播 -->
                <div class="swiper swiper-thumbs">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img src="../<?php echo htmlspecialchars($product['ImageURL']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">
                        </div>
                        <?php if (!empty($product['PreviewURL']) && $product['PreviewURL'] != 'NULL' && $product['PreviewURL'] != 'null'): ?>
                            <div class="swiper-slide">
                                <img src="../<?php echo htmlspecialchars($product['PreviewURL']); ?>" alt="預覽效果">
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['PreviewURL2']) && $product['PreviewURL2'] != 'NULL' && $product['PreviewURL2'] != 'null'): ?>
                            <div class="swiper-slide">
                                <img src="../<?php echo htmlspecialchars($product['PreviewURL2']); ?>" alt="預覽效果2">
                            </div>
                        <?php endif; ?>

                        <?php
                        // 一樣顯示其他圖片的縮略圖
                        // 修改為從 merchandise 表獲取圖片
                        $sql = "SELECT ImageURL FROM merchandise WHERE ItemID = ?";
                        $stmt = $conn->prepare($sql);
                        if ($stmt) {
                            $stmt->bind_param("i", $item_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="swiper-slide">';
                                echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="' . htmlspecialchars($product['Name']) . '">';
                                echo '</div>';
                            }
                            $stmt->close();
                        }
                        ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>

            <div class="product-info-detail">
                <h1><?php echo htmlspecialchars($product['Name']); ?></h1>
                <p class="product-category">分類:<?php echo $category_name; ?></p>
                <p class="product-description">描述:<?php echo htmlspecialchars($product['Description']); ?></p>
                <p class="product-points-required">所需點數: <span><?php echo $product['PointsRequired']; ?></span></p>
                <p class="user-points">您目前的點數: <span><?php echo $points; ?></span></p>
                <?php if ($already_purchased): ?>
                    <div class="already-purchased">您已購買此商品</div>
                <?php elseif ($points >= $product['PointsRequired']): ?>
                    <button class="buy-button" onclick="confirmPurchase(<?php echo $product['ItemID']; ?>)">購買商品</button>
                <?php else: ?>
                    <button class="buy-button disabled" disabled>點數不足</button>
                <?php endif; ?>

                <a href="shop.php" class="back-button">返回商店</a>
            </div>
        </div>
    </div>

    <!-- 購買確認彈窗 -->
    <div id="purchase-modal" class="modal">
        <div class="modal-content">
            <h2>確認購買</h2>
            <p>您確定要購買 <span id="product-name"><?php echo htmlspecialchars($product['Name']); ?></span> 嗎？</p>
            <p>將消耗 <span id="points-required"><?php echo $product['PointsRequired']; ?></span> 點數</p>
            <div class="modal-buttons">
                <button onclick="processPurchase(<?php echo $product['ItemID']; ?>)">確認</button>
                <button onclick="closeModal()">取消</button>
            </div>
        </div>
    </div>

    <!-- 購買成功彈窗 -->
    <div id="success-modal" class="modal">
        <div class="modal-content success">
            <div class="checkmark-container">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" />
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                </svg>
            </div>
            <h2>購買成功！</h2>
            <p>您已成功購買 <span id="success-product-name"></span></p>
            <?php if ($product['Category'] == 'wallpaper'): ?>
                <a href="../<?php echo htmlspecialchars($product['ImageURL']); ?>" download class="download-button">下載桌布</a>
            <?php endif; ?>
            <button onclick="closeModal()">關閉</button>
        </div>
    </div>

    <!-- 添加 Swiper 初始化和購買功能的 JavaScript -->
    <script>
        $(document).ready(function() {
            const swiperThumbs = new Swiper('.swiper-thumbs', {
                slidesPerView: 3,
                spaceBetween: 10,
                navigation: {
                    nextEl: '.swiper-thumbs .swiper-button-next',
                    prevEl: '.swiper-thumbs .swiper-button-prev',
                },
                loop: true,
                watchSlidesProgress: true,
            });

            const swiperMain = new Swiper('.swiper-main', {
                navigation: {
                    nextEl: '.swiper-main .swiper-button-next',
                    prevEl: '.swiper-main .swiper-button-prev',
                },
                loop: true,
                thumbs: {
                    swiper: swiperThumbs
                },
                on: {
                    init: function() {
                        initZoom();
                    },
                    slideChangeTransitionEnd: function() {
                        initZoom();
                    }
                }
            });

            function initZoom() {
                $('.zoomContainer').remove();
                const activeSlide = $('.swiper-main .swiper-slide-active').not('.swiper-slide-duplicate');
                const $img = activeSlide.find('img').first();

                if (!$img.length) {
                    console.warn('No image found in active slide');
                    return;
                }
                if (typeof $.fn.ezPlus !== 'function') {
                    console.warn('ezPlus plugin is not loaded');
                    return;
                }

                $img.attr('id', 'zoom-img');
                $img.attr('data-zoom-image', $img.attr('src'));

                $('#zoom-img').ezPlus({
                    zoomType: 'lens',  // 改為窗口模式window
                    lensShape: 'round',
                    lensSize: 100,
                    borderSize: 2,
                    borderColour: '#888',
                    zoomWindowWidth: 300,  // 設置放大窗口寬度
                    zoomWindowHeight: 300, // 設置放大窗口高度
                    zoomLevel: 0.8,        // 設置放大倍數
                    cursor: 'crosshair',
                    responsive: true
                });
            }

            window.confirmPurchase = function(itemId) {
                document.getElementById('purchase-modal').style.display = 'flex';
            };

            window.closeModal = function() {
                document.getElementById('purchase-modal').style.display = 'none';
            };

            window.closeSuccessModal = function() {
                document.getElementById('success-modal').style.display = 'none';
                window.location.reload();
            };

            // 為成功彈窗的關閉按鈕添加點擊事件
            document.querySelector('#success-modal button').addEventListener('click', closeSuccessModal);

            // 打勾動畫說明：
            // 使用 SVG 繪製圓圈和打勾標記
            // .checkmark-circle 和 .checkmark-check 使用 CSS 動畫
            // stroke-dashoffset 和 stroke-dasharray 控制繪製進度
            // 實現圓圈和打勾逐漸出現的效果

            window.processPurchase = function(itemId) {
                fetch('process_purchase.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'item_id=' + itemId
                    })
                    .then(response => response.json())
                    .then(data => {
                        closeModal();
                        if (data.success) {
                            document.getElementById('success-product-name').textContent = document.getElementById('product-name').textContent;
                            document.getElementById('success-modal').style.display = 'flex';
                        } else {
                            alert('購買失敗: ' + data.message);
                        }
                    })
                    .catch(error => {
                        closeModal();
                        alert('發生錯誤: ' + error);
                    });
            };

            window.onclick = function(event) {
                const purchaseModal = document.getElementById('purchase-modal');
                const successModal = document.getElementById('success-modal');
                if (event.target === purchaseModal) {
                    purchaseModal.style.display = 'none';
                }
                if (event.target === successModal) {
                    successModal.style.display = 'none';
                }
            };
        });
    </script>

</body>

</html>