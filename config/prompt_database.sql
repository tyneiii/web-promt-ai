-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 08:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prompt_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `account_id` int(11) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL,
  `fullname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`account_id`, `username`, `email`, `password`, `fullname`, `description`, `avatar`, `role_id`, `phone`) VALUES
(2, 'user1', 'user1@example.com', 'pass123', 'User One', '', '', 1, ''),
(3, 'user2', 'user2@example.com', 'pass123', 'User Two', '', '', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `comment_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`comment_id`, `prompt_id`, `account_id`, `content`, `created_at`) VALUES
(1, 5, 2, 'Rất hữu ích, cảm ơn tác giả!', '0000-00-00 00:00:00'),
(2, 6, 3, 'Mẹo này hay, mình sẽ thử!', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `follow`
--

CREATE TABLE `follow` (
  `follow_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follow`
--

INSERT INTO `follow` (`follow_id`, `follower_id`, `following_id`) VALUES
(2, 2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `love`
--

CREATE TABLE `love` (
  `love_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `love_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `love`
--

INSERT INTO `love` (`love_id`, `prompt_id`, `account_id`, `status`, `love_at`) VALUES
(1, 4, 2, '', NULL),
(2, 5, 3, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `reciever_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `reciever_id`, `sender_id`, `prompt_id`, `message`, `created_at`) VALUES
(7, 3, 2, 5, 'Bạn có bình luận mới từ user2!', '0000-00-00 00:00:00'),
(8, 2, 3, 6, 'Bạn được thích bởi user1!', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `paymenthistory`
--

CREATE TABLE `paymenthistory` (
  `payment_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `love_count_paid` int(11) NOT NULL,
  `amount_paid` decimal(10,0) NOT NULL,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prompt`
--

CREATE TABLE `prompt` (
  `prompt_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_description` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `love_count` int(11) NOT NULL DEFAULT 0,
  `comment_count` int(11) NOT NULL DEFAULT 0,
  `save_count` int(11) NOT NULL DEFAULT 0,
  `payable_love_count` int(11) NOT NULL DEFAULT 0,
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prompt`
--

INSERT INTO `prompt` (`prompt_id`, `account_id`, `title`, `short_description`, `status`, `image`, `love_count`, `comment_count`, `save_count`, `payable_love_count`, `create_at`) VALUES
(4, 2, '', 'Hướng dẫn du lịch Đà Lạt', 'public', 'images/dalat.jpg', 0, 0, 0, 0, '0000-00-00 00:00:00'),
(5, 3, '', 'Mẹo học lập trình PHP', 'public', 'images/php.jpg', 0, 0, 0, 0, '0000-00-00 00:00:00'),
(6, 2, '', 'Công thức nấu ăn Việt Nam', 'public', 'images/cooking.jpg', 0, 0, 0, 0, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `promptdetail`
--

CREATE TABLE `promptdetail` (
  `detail_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_order` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promptdetail`
--

INSERT INTO `promptdetail` (`detail_id`, `prompt_id`, `content`, `component_order`, `created_at`) VALUES
(1, 4, 'Đà Lạt là thành phố ngàn hoa, nơi lý tưởng để du lịch...', 1, '0000-00-00 00:00:00'),
(2, 5, 'Các địa điểm nổi tiếng: Hồ Xuân Hương, Thung Lũng Tình Yêu...', 2, '0000-00-00 00:00:00'),
(3, 6, 'PHP là ngôn ngữ lập trình web phổ biến...', 1, '0000-00-00 00:00:00'),
(4, 4, 'Mẹo: Học qua dự án thực tế như web-prompt-ai...', 2, '0000-00-00 00:00:00'),
(5, 5, 'Các món ăn truyền thống: Phở, Bánh mì...', 1, '0000-00-00 00:00:00'),
(6, 6, 'Cách nấu phở đơn giản tại nhà...', 2, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `prompttag`
--

CREATE TABLE `prompttag` (
  `prompt_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prompttag`
--

INSERT INTO `prompttag` (`prompt_id`, `tag_id`) VALUES
(4, 1),
(4, 5),
(5, 2),
(5, 4),
(6, 3),
(6, 5);

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `report_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`report_id`, `prompt_id`, `account_id`, `reason`, `created_at`) VALUES
(3, 4, 3, 'Nội dung không phù hợp', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `revenuemetrics`
--

CREATE TABLE `revenuemetrics` (
  `metric_id` int(11) NOT NULL,
  `current_month_clicks` int(11) NOT NULL DEFAULT 0,
  `last_month_revenue` decimal(10,0) NOT NULL,
  `update_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `save`
--

CREATE TABLE `save` (
  `save_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `save`
--

INSERT INTO `save` (`save_id`, `prompt_id`, `account_id`) VALUES
(1, 4, 2),
(2, 5, 3);

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE `tag` (
  `tag_id` int(11) NOT NULL,
  `tag_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tag`
--

INSERT INTO `tag` (`tag_id`, `tag_name`) VALUES
(3, 'ẩm thực'),
(1, 'du lịch'),
(2, 'lập trình'),
(4, 'PHP'),
(5, 'Việt Nam');

-- --------------------------------------------------------

--
-- Table structure for table `userpayoutinfo`
--

CREATE TABLE `userpayoutinfo` (
  `account_id` int(11) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `bank_account_name` varchar(255) NOT NULL,
  `bank_account_number` varchar(50) NOT NULL,
  `bank_branch` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `_fk_account_role` (`role_id`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `_fk_comment_account` (`account_id`),
  ADD KEY `_fk_comment_prompt` (`prompt_id`) USING BTREE;

--
-- Indexes for table `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `unique_follower_following` (`follower_id`,`following_id`),
  ADD KEY `_fk_following_account` (`following_id`);

--
-- Indexes for table `love`
--
ALTER TABLE `love`
  ADD PRIMARY KEY (`love_id`),
  ADD UNIQUE KEY `unique_love_prompt_account` (`prompt_id`,`account_id`) USING BTREE,
  ADD KEY `_fk_love_account` (`account_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `_fk_sender_account` (`reciever_id`);

--
-- Indexes for table `paymenthistory`
--
ALTER TABLE `paymenthistory`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `_fk_paymenthistory_account` (`account_id`),
  ADD KEY `_fk_paymenthistory_promt` (`prompt_id`);

--
-- Indexes for table `prompt`
--
ALTER TABLE `prompt`
  ADD PRIMARY KEY (`prompt_id`),
  ADD KEY `index_status` (`status`),
  ADD KEY `_fk_prompt_account` (`account_id`) USING BTREE;

--
-- Indexes for table `promptdetail`
--
ALTER TABLE `promptdetail`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `_fk_promptdetail_prompt_` (`prompt_id`) USING BTREE;

--
-- Indexes for table `prompttag`
--
ALTER TABLE `prompttag`
  ADD PRIMARY KEY (`prompt_id`,`tag_id`),
  ADD KEY `_fk_prompttag_tag` (`tag_id`) USING BTREE;

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `_fk_report_account` (`account_id`),
  ADD KEY `_fk_report_prompt` (`prompt_id`) USING BTREE;

--
-- Indexes for table `revenuemetrics`
--
ALTER TABLE `revenuemetrics`
  ADD PRIMARY KEY (`metric_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `save`
--
ALTER TABLE `save`
  ADD PRIMARY KEY (`save_id`),
  ADD UNIQUE KEY `unique_save_prompt_account` (`prompt_id`,`account_id`) USING BTREE,
  ADD KEY `_fk_save_account` (`account_id`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `unique_tagname` (`tag_name`);

--
-- Indexes for table `userpayoutinfo`
--
ALTER TABLE `userpayoutinfo`
  ADD PRIMARY KEY (`account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `follow`
--
ALTER TABLE `follow`
  MODIFY `follow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `love`
--
ALTER TABLE `love`
  MODIFY `love_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `paymenthistory`
--
ALTER TABLE `paymenthistory`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prompt`
--
ALTER TABLE `prompt`
  MODIFY `prompt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `promptdetail`
--
ALTER TABLE `promptdetail`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `revenuemetrics`
--
ALTER TABLE `revenuemetrics`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `save`
--
ALTER TABLE `save`
  MODIFY `save_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `userpayoutinfo`
--
ALTER TABLE `userpayoutinfo`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account`
--
ALTER TABLE `account`
  ADD CONSTRAINT `_fk_account_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `_fk_comment_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_comment_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `_fk_follower_account` FOREIGN KEY (`follower_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_following_account` FOREIGN KEY (`following_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `love`
--
ALTER TABLE `love`
  ADD CONSTRAINT `_fk_love_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_love_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `_fk_reciever_account` FOREIGN KEY (`reciever_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_sender_account` FOREIGN KEY (`reciever_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `paymenthistory`
--
ALTER TABLE `paymenthistory`
  ADD CONSTRAINT `_fk_paymenthistory_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_paymenthistory_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `prompt`
--
ALTER TABLE `prompt`
  ADD CONSTRAINT `_fk_promt_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `promptdetail`
--
ALTER TABLE `promptdetail`
  ADD CONSTRAINT `_fk_promtdetail_promt_` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `prompttag`
--
ALTER TABLE `prompttag`
  ADD CONSTRAINT `_fk_promttag_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_promttag_tag` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `_fk_report_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_report_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `save`
--
ALTER TABLE `save`
  ADD CONSTRAINT `_fk_save_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_save_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `userpayoutinfo`
--
ALTER TABLE `userpayoutinfo`
  ADD CONSTRAINT `_fk_userpayoutinfo_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
