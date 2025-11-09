-- ============================================
-- 附近討論功能 - 資料表建立 SQL
-- ============================================
-- 說明：
-- 1. 此資料表用於儲存附近討論的貼文
-- 2. 與原本的 communitypost 表分開，不影響原有論壇功能
-- 3. 請在 phpMyAdmin 或 MySQL 中執行此 SQL
-- ============================================

CREATE TABLE IF NOT EXISTS `nearby_post` (
  `PostID` int(11) NOT NULL AUTO_INCREMENT COMMENT '貼文編號 (PK)',
  `Title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '標題',
  `Content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '貼文內容',
  `UserID` int(11) NOT NULL COMMENT '使用者編號 (FK to User)',
  `Latitude` decimal(10, 8) DEFAULT NULL COMMENT '緯度',
  `Longitude` decimal(11, 8) DEFAULT NULL COMMENT '經度',
  `LocationName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '地點名稱',
  `ImageURL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '圖片網址',
  `PostDate` datetime DEFAULT current_timestamp() COMMENT '發文日期',
  PRIMARY KEY (`PostID`),
  KEY `UserID` (`UserID`),
  KEY `LocationIndex` (`Latitude`, `Longitude`),
  CONSTRAINT `nearby_post_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='附近討論貼文表';

-- ============================================
-- 附近討論評論表
-- ============================================
-- 說明：
-- 1. 此資料表用於儲存附近討論的留言/評論
-- 2. 與 commentarea 表分開，避免 PostID 衝突
-- 3. PostID 關聯到 nearby_post 表的 PostID
-- ============================================

CREATE TABLE IF NOT EXISTS `nearby_comment` (
  `CommentID` int(11) NOT NULL AUTO_INCREMENT COMMENT '留言編號 (PK)',
  `PostID` int(11) NOT NULL COMMENT '貼文編號 (FK to nearby_post)',
  `UserID` int(11) NOT NULL COMMENT '使用者編號 (FK to User)',
  `Content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '留言內容',
  `CommentTime` datetime DEFAULT current_timestamp() COMMENT '留言時間',
  `Status` enum('PENDING','APPROVED','DELETED') DEFAULT 'PENDING' COMMENT '留言狀態 (待審核/已通過/已刪除)',
  PRIMARY KEY (`CommentID`),
  KEY `PostID` (`PostID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `nearby_comment_ibfk_1` FOREIGN KEY (`PostID`) REFERENCES `nearby_post` (`PostID`) ON DELETE CASCADE,
  CONSTRAINT `nearby_comment_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='附近討論留言表';

