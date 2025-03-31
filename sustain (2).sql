-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-03-30 17:58:00
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `sustain`
--
CREATE DATABASE IF NOT EXISTS `sustain` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sustain`;

-- --------------------------------------------------------

--
-- 表的结构 `achievement`
--
-- 创建时间： 2025-03-28 16:54:29
--

DROP TABLE IF EXISTS `achievement`;
CREATE TABLE `achievement` (
  `UserID` int(11) NOT NULL COMMENT 'FK to User (使用者編號)',
  `TotalPoints` int(11) NOT NULL DEFAULT 0 COMMENT '總點數',
  `ArticlesViewed` int(11) DEFAULT 0 COMMENT '瀏覽文章數',
  `ChoiceQuestionsCorrect` int(11) DEFAULT 0 COMMENT '選擇題答對數',
  `TFQuestionsCorrect` int(11) DEFAULT 0 COMMENT '是非題答對數',
  `FillinQuestionsCorrect` int(11) DEFAULT 0 COMMENT '填充題答對數'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `achievement`
--

TRUNCATE TABLE `achievement`;
-- --------------------------------------------------------

--
-- 表的结构 `article`
--
-- 创建时间： 2025-03-30 15:49:42
-- 最后更新： 2025-03-30 15:56:59
--

DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `ArticleID` int(11) NOT NULL COMMENT '文章編號 (PK)',
  `Title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '標題',
  `Category` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分類',
  `ImageURL` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '圖片網址',
  `Description` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '簡介',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `article`
--

TRUNCATE TABLE `article`;
-- --------------------------------------------------------

--
-- 表的结构 `articleimage`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `articleimage`;
CREATE TABLE `articleimage` (
  `ImageID` int(11) NOT NULL COMMENT '圖片編號 (PK)',
  `ArticleID` int(11) NOT NULL COMMENT '文章編號 (FK to Article)',
  `ImageURL` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '圖片連結'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `articleimage`
--

TRUNCATE TABLE `articleimage`;
-- --------------------------------------------------------

--
-- 表的结构 `choicequiz`
--
-- 创建时间： 2025-03-28 16:46:27
--

DROP TABLE IF EXISTS `choicequiz`;
CREATE TABLE `choicequiz` (
  `choiceID` int(11) NOT NULL COMMENT '選擇題編號 (PK)',
  `QuestionText` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '問題文字',
  `OptionA` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項A',
  `OptionB` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項B',
  `OptionC` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項C',
  `OptionD` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項D',
  `CorrectAnswer` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '正確答案 (A/B/C/D)',
  `Category` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分類',
  `UserID` int(11) NOT NULL COMMENT '出題者 (FK to User)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `choicequiz`
--

TRUNCATE TABLE `choicequiz`;
-- --------------------------------------------------------

--
-- 表的结构 `choicequizstagingarea`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `choicequizstagingarea`;
CREATE TABLE `choicequizstagingarea` (
  `UserID` int(11) NOT NULL COMMENT 'FK to User',
  `QuestionText` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '問題文字',
  `OptionA` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項A',
  `OptionB` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項B',
  `OptionC` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項C',
  `OptionD` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項D',
  `CorrectAnswer` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '正確答案 (A/B/C/D)',
  `Category` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分類'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `choicequizstagingarea`
--

TRUNCATE TABLE `choicequizstagingarea`;
-- --------------------------------------------------------

--
-- 表的结构 `choicerec`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `choicerec`;
CREATE TABLE `choicerec` (
  `choiceID` int(11) NOT NULL COMMENT 'FK to ChoiceQuiz',
  `UserID` int(11) NOT NULL COMMENT 'FK to User',
  `UserAnswer` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '使用者答案 (A/B/C/D)',
  `FinishTime` datetime DEFAULT current_timestamp() COMMENT '完成時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `choicerec`
--

TRUNCATE TABLE `choicerec`;
-- --------------------------------------------------------

--
-- 表的结构 `commentarea`
--
-- 创建时间： 2025-03-21 15:40:02
--

DROP TABLE IF EXISTS `commentarea`;
CREATE TABLE `commentarea` (
  `CommentID` int(11) NOT NULL COMMENT '留言編號 (PK)',
  `PostID` int(11) NOT NULL COMMENT '貼文編號 (FK to CommunityPost)',
  `UserID` int(11) NOT NULL COMMENT '使用者編號 (FK to User)',
  `Content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '留言內容',
  `CommentTime` datetime DEFAULT current_timestamp() COMMENT '留言時間',
  `Status` enum('PENDING','APPROVED','DELETED') DEFAULT 'PENDING' COMMENT '留言狀態 (待審核/已通過/已刪除)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `commentarea`
--

TRUNCATE TABLE `commentarea`;
-- --------------------------------------------------------

--
-- 表的结构 `communitypost`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `communitypost`;
CREATE TABLE `communitypost` (
  `PostID` int(11) NOT NULL COMMENT '貼文編號 (PK)',
  `Content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '貼文內容',
  `ImageURL` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '圖片網址',
  `PostDate` datetime DEFAULT current_timestamp() COMMENT '發文日期',
  `Category` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分類',
  `UserID` int(11) NOT NULL COMMENT 'FK to User'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `communitypost`
--

TRUNCATE TABLE `communitypost`;
-- --------------------------------------------------------

--
-- 表的结构 `fillquiz`
--
-- 创建时间： 2025-03-28 16:47:54
--

DROP TABLE IF EXISTS `fillquiz`;
CREATE TABLE `fillquiz` (
  `fillID` int(11) NOT NULL COMMENT '填充題編號 (PK)',
  `QuestionText` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '問題文字',
  `CorrectAnswer` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '正確答案',
  `Category` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分類',
  `UserID` int(11) NOT NULL COMMENT '出題者 (FK to User)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `fillquiz`
--

TRUNCATE TABLE `fillquiz`;
-- --------------------------------------------------------

--
-- 表的结构 `fillquizstagingarea`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `fillquizstagingarea`;
CREATE TABLE `fillquizstagingarea` (
  `UserID` int(11) NOT NULL COMMENT 'FK to User',
  `QuestionText` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '問題文字',
  `CorrectAnswer` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '正確答案',
  `Category` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分類'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `fillquizstagingarea`
--

TRUNCATE TABLE `fillquizstagingarea`;
-- --------------------------------------------------------

--
-- 表的结构 `fillrec`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `fillrec`;
CREATE TABLE `fillrec` (
  `fillID` int(11) NOT NULL COMMENT 'FK to FillQuiz',
  `UserID` int(11) NOT NULL COMMENT 'FK to User',
  `UserAnswer` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '使用者答案',
  `FinishTime` datetime DEFAULT current_timestamp() COMMENT '完成時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `fillrec`
--

TRUNCATE TABLE `fillrec`;
-- --------------------------------------------------------

--
-- 表的结构 `merchandise`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `merchandise`;
CREATE TABLE `merchandise` (
  `ItemID` int(11) NOT NULL COMMENT '商品編號 (PK)',
  `Name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品名稱',
  `Description` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '商品描述',
  `PointsRequired` int(11) NOT NULL COMMENT '所需點數',
  `Available` tinyint(1) NOT NULL COMMENT '是否可用'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `merchandise`
--

TRUNCATE TABLE `merchandise`;
-- --------------------------------------------------------

--
-- 表的结构 `purchase`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `purchase`;
CREATE TABLE `purchase` (
  `UserID` int(11) NOT NULL COMMENT 'FK to Achievement (使用者編號)',
  `ItemID` int(11) NOT NULL COMMENT 'FK to Merchandise',
  `PurchaseTime` datetime DEFAULT current_timestamp() COMMENT '購買時間',
  `SpentPoints` int(11) NOT NULL DEFAULT 0 COMMENT '消費點數'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `purchase`
--

TRUNCATE TABLE `purchase`;
-- --------------------------------------------------------

--
-- 表的结构 `tfquiz`
--
-- 创建时间： 2025-03-28 16:46:56
--

DROP TABLE IF EXISTS `tfquiz`;
CREATE TABLE `tfquiz` (
  `tfID` int(11) NOT NULL COMMENT '是非題編號 (PK)',
  `QuestionText` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '問題文字',
  `OptionA` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項A',
  `OptionB` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項B',
  `CorrectAnswer` tinyint(1) NOT NULL COMMENT '正確答案 (True/False)',
  `Category` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分類',
  `UserID` int(11) NOT NULL COMMENT '出題者 (FK to User)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `tfquiz`
--

TRUNCATE TABLE `tfquiz`;
-- --------------------------------------------------------

--
-- 表的结构 `tfquizstagingarea`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `tfquizstagingarea`;
CREATE TABLE `tfquizstagingarea` (
  `UserID` int(11) NOT NULL COMMENT 'FK to User',
  `QuestionText` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '問題文字',
  `OptionA` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項A',
  `OptionB` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '選項B',
  `CorrectAnswer` tinyint(1) NOT NULL COMMENT '正確答案 (True/False)',
  `Category` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分類'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `tfquizstagingarea`
--

TRUNCATE TABLE `tfquizstagingarea`;
-- --------------------------------------------------------

--
-- 表的结构 `tfrec`
--
-- 创建时间： 2025-03-21 15:39:49
--

DROP TABLE IF EXISTS `tfrec`;
CREATE TABLE `tfrec` (
  `tfID` int(11) NOT NULL COMMENT 'FK to TFQuiz',
  `UserID` int(11) NOT NULL COMMENT 'FK to User',
  `UserAnswer` tinyint(1) DEFAULT NULL COMMENT '使用者答案 (True/False)',
  `FinishTime` datetime DEFAULT current_timestamp() COMMENT '完成時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `tfrec`
--

TRUNCATE TABLE `tfrec`;
-- --------------------------------------------------------

--
-- 表的结构 `user`
--
-- 创建时间： 2025-03-28 16:42:04
-- 最后更新： 2025-03-30 15:57:02
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `UserID` int(11) NOT NULL COMMENT '使用者編號 (PK)',
  `Username` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '使用者名稱',
  `Email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '電子郵件',
  `Password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密碼 (加密儲存)',
  `JoinDate` datetime DEFAULT current_timestamp() COMMENT '註冊時間',
  `Status` enum('Student','Teacher') NOT NULL DEFAULT 'Student' COMMENT '使用者身份'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 插入之前先把表清空（truncate） `user`
--

TRUNCATE TABLE `user`;
--
-- 转储表的索引
--

--
-- 表的索引 `achievement`
--
ALTER TABLE `achievement`
  ADD PRIMARY KEY (`UserID`);

--
-- 表的索引 `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`ArticleID`),
  ADD KEY `UserID` (`UserID`);

--
-- 表的索引 `articleimage`
--
ALTER TABLE `articleimage`
  ADD PRIMARY KEY (`ImageID`),
  ADD KEY `ArticleID` (`ArticleID`);

--
-- 表的索引 `choicequiz`
--
ALTER TABLE `choicequiz`
  ADD PRIMARY KEY (`choiceID`),
  ADD KEY `UserID` (`UserID`);

--
-- 表的索引 `choicequizstagingarea`
--
ALTER TABLE `choicequizstagingarea`
  ADD PRIMARY KEY (`UserID`);

--
-- 表的索引 `choicerec`
--
ALTER TABLE `choicerec`
  ADD PRIMARY KEY (`choiceID`,`UserID`),
  ADD KEY `UserID` (`UserID`);

--
-- 表的索引 `commentarea`
--
ALTER TABLE `commentarea`
  ADD PRIMARY KEY (`CommentID`),
  ADD KEY `PostID` (`PostID`),
  ADD KEY `UserID` (`UserID`);

--
-- 表的索引 `communitypost`
--
ALTER TABLE `communitypost`
  ADD PRIMARY KEY (`PostID`),
  ADD KEY `UserID` (`UserID`);

--
-- 表的索引 `fillquiz`
--
ALTER TABLE `fillquiz`
  ADD PRIMARY KEY (`fillID`),
  ADD KEY `UserID` (`UserID`);

--
-- 表的索引 `fillrec`
--
ALTER TABLE `fillrec`
  ADD PRIMARY KEY (`fillID`,`UserID`),
  ADD KEY `UserID` (`UserID`);

--
-- 表的索引 `merchandise`
--
ALTER TABLE `merchandise`
  ADD PRIMARY KEY (`ItemID`);

--
-- 表的索引 `purchase`
--
ALTER TABLE `purchase`
  ADD PRIMARY KEY (`UserID`,`ItemID`),
  ADD KEY `ItemID` (`ItemID`);

--
-- 表的索引 `tfquiz`
--
ALTER TABLE `tfquiz`
  ADD PRIMARY KEY (`tfID`),
  ADD KEY `UserID` (`UserID`);

--
-- 表的索引 `tfrec`
--
ALTER TABLE `tfrec`
  ADD PRIMARY KEY (`tfID`,`UserID`),
  ADD KEY `UserID` (`UserID`);

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `article`
--
ALTER TABLE `article`
  MODIFY `ArticleID` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章編號 (PK)', AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `articleimage`
--
ALTER TABLE `articleimage`
  MODIFY `ImageID` int(11) NOT NULL AUTO_INCREMENT COMMENT '圖片編號 (PK)';

--
-- 使用表AUTO_INCREMENT `choicequiz`
--
ALTER TABLE `choicequiz`
  MODIFY `choiceID` int(11) NOT NULL AUTO_INCREMENT COMMENT '選擇題編號 (PK)';

--
-- 使用表AUTO_INCREMENT `commentarea`
--
ALTER TABLE `commentarea`
  MODIFY `CommentID` int(11) NOT NULL AUTO_INCREMENT COMMENT '留言編號 (PK)';

--
-- 使用表AUTO_INCREMENT `communitypost`
--
ALTER TABLE `communitypost`
  MODIFY `PostID` int(11) NOT NULL AUTO_INCREMENT COMMENT '貼文編號 (PK)';

--
-- 使用表AUTO_INCREMENT `fillquiz`
--
ALTER TABLE `fillquiz`
  MODIFY `fillID` int(11) NOT NULL AUTO_INCREMENT COMMENT '填充題編號 (PK)';

--
-- 使用表AUTO_INCREMENT `merchandise`
--
ALTER TABLE `merchandise`
  MODIFY `ItemID` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品編號 (PK)';

--
-- 使用表AUTO_INCREMENT `tfquiz`
--
ALTER TABLE `tfquiz`
  MODIFY `tfID` int(11) NOT NULL AUTO_INCREMENT COMMENT '是非題編號 (PK)';

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT COMMENT '使用者編號 (PK)', AUTO_INCREMENT=2;

--
-- 限制导出的表
--

--
-- 限制表 `achievement`
--
ALTER TABLE `achievement`
  ADD CONSTRAINT `achievement_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- 限制表 `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- 限制表 `articleimage`
--
ALTER TABLE `articleimage`
  ADD CONSTRAINT `articleimage_ibfk_1` FOREIGN KEY (`ArticleID`) REFERENCES `article` (`ArticleID`);

--
-- 限制表 `choicequiz`
--
ALTER TABLE `choicequiz`
  ADD CONSTRAINT `choicequiz_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- 限制表 `choicerec`
--
ALTER TABLE `choicerec`
  ADD CONSTRAINT `choicerec_ibfk_1` FOREIGN KEY (`choiceID`) REFERENCES `choicequiz` (`choiceID`),
  ADD CONSTRAINT `choicerec_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- 限制表 `commentarea`
--
ALTER TABLE `commentarea`
  ADD CONSTRAINT `commentarea_ibfk_1` FOREIGN KEY (`PostID`) REFERENCES `communitypost` (`PostID`),
  ADD CONSTRAINT `commentarea_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- 限制表 `communitypost`
--
ALTER TABLE `communitypost`
  ADD CONSTRAINT `communitypost_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- 限制表 `fillquiz`
--
ALTER TABLE `fillquiz`
  ADD CONSTRAINT `fillquiz_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- 限制表 `fillrec`
--
ALTER TABLE `fillrec`
  ADD CONSTRAINT `fillrec_ibfk_1` FOREIGN KEY (`fillID`) REFERENCES `fillquiz` (`fillID`),
  ADD CONSTRAINT `fillrec_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- 限制表 `purchase`
--
ALTER TABLE `purchase`
  ADD CONSTRAINT `purchase_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `purchase_ibfk_2` FOREIGN KEY (`ItemID`) REFERENCES `merchandise` (`ItemID`);

--
-- 限制表 `tfquiz`
--
ALTER TABLE `tfquiz`
  ADD CONSTRAINT `tfquiz_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- 限制表 `tfrec`
--
ALTER TABLE `tfrec`
  ADD CONSTRAINT `tfrec_ibfk_1` FOREIGN KEY (`tfID`) REFERENCES `tfquiz` (`tfID`),
  ADD CONSTRAINT `tfrec_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
