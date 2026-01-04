-- ============================================
-- 每月排名獎勵功能 - 資料表建立 SQL
-- ============================================
-- 說明：
-- 1. 記錄每月各地區前三名學生的獎勵
-- 2. 學生可以自己領取獎勵點數
-- 3. 請在 phpMyAdmin 或 MySQL 中執行此 SQL
-- ============================================

CREATE TABLE IF NOT EXISTS `monthly_ranking_rewards` (
  `RewardID` int(11) NOT NULL AUTO_INCREMENT COMMENT '獎勵編號 (PK)',
  `UserID` int(11) NOT NULL COMMENT '學生編號 (FK to User)',
  `Region` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '地區',
  `Month` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '月份 (格式: YYYY-MM)',
  `Rank` tinyint(4) NOT NULL COMMENT '排名 (1-3)',
  `PointsAwarded` int(11) NOT NULL DEFAULT 0 COMMENT '獲得的點數',
  `AwardedDate` datetime DEFAULT NULL COMMENT '領取日期',
  `Status` enum('PENDING','CLAIMED') DEFAULT 'PENDING' COMMENT '狀態 (待領取/已領取)',
  `CreatedDate` datetime DEFAULT current_timestamp() COMMENT '建立日期',
  PRIMARY KEY (`RewardID`),
  KEY `UserID` (`UserID`),
  KEY `Region` (`Region`),
  KEY `Month` (`Month`),
  KEY `Status` (`Status`),
  CONSTRAINT `monthly_ranking_rewards_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='每月排名獎勵表';

-- 在 system_settings 表中新增獎勵點數設定（如果還沒有）
-- 這些設定會在 system_settings.php 中自動建立，這裡只是參考
-- points_monthly_rank_1: 第一名獎勵點數（預設 100）
-- points_monthly_rank_2: 第二名獎勵點數（預設 50）
-- points_monthly_rank_3: 第三名獎勵點數（預設 30）

