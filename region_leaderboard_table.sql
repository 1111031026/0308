-- ============================================
-- 地區排行榜功能 - 資料表建立 SQL
-- ============================================
-- 說明：
-- 1. 在 user 表新增 Region 欄位（地區/縣市）
-- 2. 建立 region_leaderboard 表用於快取地區統計數據
-- 3. 請在 phpMyAdmin 或 MySQL 中執行此 SQL
-- ============================================

-- 1. 在 user 表新增 Region 欄位
ALTER TABLE `user` 
ADD COLUMN `Region` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '地區/縣市（用於地區排行榜）' 
AFTER `Status`;

-- 2. 建立地區排行榜統計表
CREATE TABLE IF NOT EXISTS `region_leaderboard` (
  `Region` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '地區名稱 (PK)',
  `TotalPoints` INT(11) NOT NULL DEFAULT 0 COMMENT '地區總分（該地區所有學生的總點數）',
  `AveragePoints` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '地區平均分（該地區學生的平均點數）',
  `StudentCount` INT(11) NOT NULL DEFAULT 0 COMMENT '參與學生數量',
  `LastUpdated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最後更新時間',
  PRIMARY KEY (`Region`),
  KEY `idx_total_points` (`TotalPoints`),
  KEY `idx_average_points` (`AveragePoints`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='地區排行榜統計表';

-- 3. 初始化台灣22個縣市（可選，預先建立空記錄）
INSERT IGNORE INTO `region_leaderboard` (`Region`, `TotalPoints`, `AveragePoints`, `StudentCount`) VALUES
('台北市', 0, 0.00, 0),
('新北市', 0, 0.00, 0),
('桃園市', 0, 0.00, 0),
('台中市', 0, 0.00, 0),
('台南市', 0, 0.00, 0),
('高雄市', 0, 0.00, 0),
('基隆市', 0, 0.00, 0),
('新竹市', 0, 0.00, 0),
('嘉義市', 0, 0.00, 0),
('新竹縣', 0, 0.00, 0),
('苗栗縣', 0, 0.00, 0),
('彰化縣', 0, 0.00, 0),
('南投縣', 0, 0.00, 0),
('雲林縣', 0, 0.00, 0),
('嘉義縣', 0, 0.00, 0),
('屏東縣', 0, 0.00, 0),
('宜蘭縣', 0, 0.00, 0),
('花蓮縣', 0, 0.00, 0),
('台東縣', 0, 0.00, 0),
('澎湖縣', 0, 0.00, 0),
('金門縣', 0, 0.00, 0),
('連江縣', 0, 0.00, 0);

