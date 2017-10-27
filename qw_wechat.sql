
-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2017-10-27 14:06:26
-- 服务器版本： 5.6.36-log
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `qwadmin`
--

-- --------------------------------------------------------

ALTER TABLE `qw_member` ADD `openid` VARCHAR(32) NULL COMMENT '微信授权码' AFTER `t`;

--
-- 表的结构 `qw_smslog`
--

CREATE TABLE `qw_smslog` (
  `id` int(11) NOT NULL,
  `t` int(10) NOT NULL COMMENT '发送时间',
  `phone` varchar(11) NOT NULL COMMENT '手机号码',
  `code` varchar(6) NOT NULL COMMENT '验证码'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信验证码日志';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `qw_smslog`
--
ALTER TABLE `qw_smslog`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `qw_smslog`
--
ALTER TABLE `qw_smslog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;
