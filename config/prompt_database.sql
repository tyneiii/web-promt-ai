-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 14, 2025 lúc 05:31 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `prompt_database`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `account`
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
  `token` varchar(255) DEFAULT NULL,
  `create_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `account`
--

INSERT INTO `account` (`account_id`, `username`, `email`, `password`, `fullname`, `description`, `avatar`, `role_id`, `token`, `create_at`) VALUES
(2, 'user1', 'user1@example.com', 'pass123', 'User One', '', '', 1, '', NULL),
(3, 'user2', 'user2@example.com', 'pass123', 'User Two', '', '', 1, '', NULL),
(5, 'thuhoang', 'a@gmail.com', '123', NULL, NULL, NULL, 2, '', NULL),
(7, 'nhi', 'nhi@gmail.com', '$2y$10$P4svZN1uXSQ2IG5c/0dwr.KBZKz4IQXH7oLRVfujEdIjoMKgWcZia', NULL, NULL, NULL, 2, '6a5eefce0ea941d23189c6f94766e54ebd8aebaaf84c286b48', '2025-11-13 11:45:01'),
(8, 'nhung', 'nhung@gmail.com', '$2y$10$4T4YZjZiPK1xTMKaohcC1.I7axRIa5EvLSX9qHCuWjwOK.2N8r.ea', NULL, NULL, NULL, 2, '27d4356bfa38f56037025b78904c7b2236a729dc956fec0553', '2025-11-13 12:02:39'),
(9, 'hai trieu', 'haitrieu@gmail.com', '$2y$10$G04ITs2sfJfFb5WtXQxZseaEDVb1Mzf2sTJ2Hd4bqAbH5QrElD2s.', NULL, NULL, NULL, 2, '59ce1d4091acff788e190097eddc7f7344d6892b31e8b543fb', '2025-11-13 12:56:01'),
(12, 'long', 'longhoang@gmail.com', '$2y$10$mE8LbBjOrqeLTvZ.C5ILqeuXT4ln7zJJFRjY8m./eagCgwMeez.AW', NULL, NULL, NULL, 2, 'a88aba0cca1e16ef32efb61db5dcbd7373a1503a1963fbd766', '2025-11-13 17:40:51'),
(21, 'mimichan', 'hoanganhthu271004@gmail.com', '$2y$10$Umx3EMJ735BxwUtqPtMDcu11YPO78I0rdr5qbq1W22.4a.zyb03Uu', NULL, NULL, NULL, 2, '59ed606f3c45e4610da9989a674630b79015d57aca2e10e113ecc2a91c9947c66c56eb0e327d0bffa6c041948a92d5e6561e', '2025-11-13 21:34:52'),
(22, 'postman', 'thu.hta.64cntt@ntu.edu.vn', '$2y$10$cNXo6DpHlxfJEa.OW0k6...ZTcghtymsjSqWzlOJDyMUIrej4TpLi', NULL, NULL, NULL, 2, NULL, '2025-11-13 21:41:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comment`
--

CREATE TABLE `comment` (
  `comment_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `comment`
--

INSERT INTO `comment` (`comment_id`, `prompt_id`, `account_id`, `content`, `created_at`) VALUES
(1, 5, 2, 'Rất hữu ích, cảm ơn tác giả!', '0000-00-00 00:00:00'),
(2, 6, 3, 'Mẹo này hay, mình sẽ thử!', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `follow`
--

CREATE TABLE `follow` (
  `follow_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `follow`
--

INSERT INTO `follow` (`follow_id`, `follower_id`, `following_id`) VALUES
(2, 2, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `love`
--

CREATE TABLE `love` (
  `love_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `love_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `love`
--

INSERT INTO `love` (`love_id`, `prompt_id`, `account_id`, `status`, `love_at`) VALUES
(2, 5, 3, '', NULL),
(25, 4, 2, 'OPEN', '2025-11-13'),
(28, 5, 2, 'OPEN', '2025-11-13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notification`
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
-- Đang đổ dữ liệu cho bảng `notification`
--

INSERT INTO `notification` (`notification_id`, `reciever_id`, `sender_id`, `prompt_id`, `message`, `created_at`) VALUES
(7, 3, 2, 5, 'Bạn có bình luận mới từ user2!', '0000-00-00 00:00:00'),
(8, 2, 3, 6, 'Bạn được thích bởi user1!', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `paymenthistory`
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
-- Cấu trúc bảng cho bảng `prompt`
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
-- Đang đổ dữ liệu cho bảng `prompt`
--

INSERT INTO `prompt` (`prompt_id`, `account_id`, `title`, `short_description`, `status`, `image`, `love_count`, `comment_count`, `save_count`, `payable_love_count`, `create_at`) VALUES
(4, 2, '', 'Hướng dẫn du lịch Đà Lạt', 'public', 'images/dalat.jpg', 1, 0, 0, 0, '0000-00-00 00:00:00'),
(5, 3, '', 'Mẹo học lập trình PHP', 'public', 'images/php.jpg', 3, 0, 0, 0, '0000-00-00 00:00:00'),
(6, 2, '', 'Công thức nấu ăn Việt Nam', 'public', 'images/cooking.jpg', 1, 0, 0, 0, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promptdetail`
--

CREATE TABLE `promptdetail` (
  `detail_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_order` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `promptdetail`
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
-- Cấu trúc bảng cho bảng `prompttag`
--

CREATE TABLE `prompttag` (
  `prompt_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `prompttag`
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
-- Cấu trúc bảng cho bảng `report`
--

CREATE TABLE `report` (
  `report_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `report`
--

INSERT INTO `report` (`report_id`, `prompt_id`, `account_id`, `reason`, `created_at`) VALUES
(3, 4, 3, 'Nội dung không phù hợp', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `revenuemetrics`
--

CREATE TABLE `revenuemetrics` (
  `metric_id` int(11) NOT NULL,
  `current_month_clicks` int(11) NOT NULL DEFAULT 0,
  `last_month_revenue` decimal(10,0) NOT NULL,
  `update_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `role`
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'User');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `save`
--

CREATE TABLE `save` (
  `save_id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `save`
--

INSERT INTO `save` (`save_id`, `prompt_id`, `account_id`) VALUES
(1, 4, 2),
(2, 5, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tag`
--

CREATE TABLE `tag` (
  `tag_id` int(11) NOT NULL,
  `tag_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tag`
--

INSERT INTO `tag` (`tag_id`, `tag_name`) VALUES
(3, 'ẩm thực'),
(1, 'du lịch'),
(2, 'lập trình'),
(4, 'PHP'),
(5, 'Việt Nam');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `userpayoutinfo`
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
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `_fk_account_role` (`role_id`),
  ADD KEY `username` (`username`);

--
-- Chỉ mục cho bảng `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `_fk_comment_account` (`account_id`),
  ADD KEY `_fk_comment_prompt` (`prompt_id`) USING BTREE;

--
-- Chỉ mục cho bảng `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `unique_follower_following` (`follower_id`,`following_id`),
  ADD KEY `_fk_following_account` (`following_id`);

--
-- Chỉ mục cho bảng `love`
--
ALTER TABLE `love`
  ADD PRIMARY KEY (`love_id`),
  ADD UNIQUE KEY `unique_love_prompt_account` (`prompt_id`,`account_id`) USING BTREE,
  ADD KEY `_fk_love_account` (`account_id`);

--
-- Chỉ mục cho bảng `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `_fk_sender_account` (`reciever_id`);

--
-- Chỉ mục cho bảng `paymenthistory`
--
ALTER TABLE `paymenthistory`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `_fk_paymenthistory_account` (`account_id`),
  ADD KEY `_fk_paymenthistory_promt` (`prompt_id`);

--
-- Chỉ mục cho bảng `prompt`
--
ALTER TABLE `prompt`
  ADD PRIMARY KEY (`prompt_id`),
  ADD KEY `index_status` (`status`),
  ADD KEY `_fk_prompt_account` (`account_id`) USING BTREE;

--
-- Chỉ mục cho bảng `promptdetail`
--
ALTER TABLE `promptdetail`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `_fk_promptdetail_prompt_` (`prompt_id`) USING BTREE;

--
-- Chỉ mục cho bảng `prompttag`
--
ALTER TABLE `prompttag`
  ADD PRIMARY KEY (`prompt_id`,`tag_id`),
  ADD KEY `_fk_prompttag_tag` (`tag_id`) USING BTREE;

--
-- Chỉ mục cho bảng `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `_fk_report_account` (`account_id`),
  ADD KEY `_fk_report_prompt` (`prompt_id`) USING BTREE;

--
-- Chỉ mục cho bảng `revenuemetrics`
--
ALTER TABLE `revenuemetrics`
  ADD PRIMARY KEY (`metric_id`);

--
-- Chỉ mục cho bảng `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Chỉ mục cho bảng `save`
--
ALTER TABLE `save`
  ADD PRIMARY KEY (`save_id`),
  ADD UNIQUE KEY `unique_save_prompt_account` (`prompt_id`,`account_id`) USING BTREE,
  ADD KEY `_fk_save_account` (`account_id`);

--
-- Chỉ mục cho bảng `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `unique_tagname` (`tag_name`);

--
-- Chỉ mục cho bảng `userpayoutinfo`
--
ALTER TABLE `userpayoutinfo`
  ADD PRIMARY KEY (`account_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `account`
--
ALTER TABLE `account`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `follow`
--
ALTER TABLE `follow`
  MODIFY `follow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `love`
--
ALTER TABLE `love`
  MODIFY `love_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT cho bảng `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `paymenthistory`
--
ALTER TABLE `paymenthistory`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `prompt`
--
ALTER TABLE `prompt`
  MODIFY `prompt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `promptdetail`
--
ALTER TABLE `promptdetail`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `report`
--
ALTER TABLE `report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `revenuemetrics`
--
ALTER TABLE `revenuemetrics`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `save`
--
ALTER TABLE `save`
  MODIFY `save_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `tag`
--
ALTER TABLE `tag`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `userpayoutinfo`
--
ALTER TABLE `userpayoutinfo`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `account`
--
ALTER TABLE `account`
  ADD CONSTRAINT `_fk_account_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `_fk_comment_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_comment_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `_fk_follower_account` FOREIGN KEY (`follower_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_following_account` FOREIGN KEY (`following_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `love`
--
ALTER TABLE `love`
  ADD CONSTRAINT `_fk_love_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_love_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `_fk_reciever_account` FOREIGN KEY (`reciever_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_sender_account` FOREIGN KEY (`reciever_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `paymenthistory`
--
ALTER TABLE `paymenthistory`
  ADD CONSTRAINT `_fk_paymenthistory_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_paymenthistory_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `prompt`
--
ALTER TABLE `prompt`
  ADD CONSTRAINT `_fk_promt_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `promptdetail`
--
ALTER TABLE `promptdetail`
  ADD CONSTRAINT `_fk_promtdetail_promt_` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `prompttag`
--
ALTER TABLE `prompttag`
  ADD CONSTRAINT `_fk_promttag_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_promttag_tag` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `_fk_report_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_report_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `save`
--
ALTER TABLE `save`
  ADD CONSTRAINT `_fk_save_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_fk_save_promt` FOREIGN KEY (`prompt_id`) REFERENCES `prompt` (`prompt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `userpayoutinfo`
--
ALTER TABLE `userpayoutinfo`
  ADD CONSTRAINT `_fk_userpayoutinfo_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
