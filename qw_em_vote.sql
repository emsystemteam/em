/*
Navicat MySQL Data Transfer

Source Server         : em
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : qwadmin

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-11-27 23:06:04
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for qw_em_vote
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_vote`;
CREATE TABLE `qw_em_vote` (
  `vote_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_name` varchar(255) NOT NULL,
  `vote_pic` varchar(255) DEFAULT NULL COMMENT '投票封面图存储URL',
  `start_time` timestamp NOT NULL,
  `end_time` timestamp NOT NULL,
  `open_vote_result` int(11) NOT NULL COMMENT '1.公开，2不公开，关联字典表',
  `confirm_note` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `household_auth_result` varchar(30) NOT NULL COMMENT '已迁入，待审核，关联字典表，多个用逗号隔开',
  `household_status` varchar(30) NOT NULL COMMENT '关联字典表，多个用逗号隔开',
  `wechat_push_times` int(11) DEFAULT NULL,
  `vote_status` int(11) NOT NULL COMMENT '1.未录入，2未开始，3已发布，4.已结束，关联字典表',
  `order_filed` int(11) DEFAULT NULL COMMENT 'int型，从1开始，越小优先级越高',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `modify_time` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `operator` int(11) DEFAULT NULL,
  PRIMARY KEY (`vote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票主表,保存投票基本信息';

-- ----------------------------
-- Table structure for qw_em_vote_paper
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_vote_paper`;
CREATE TABLE `qw_em_vote_paper` (
  `vote_paper_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_id` int(11) NOT NULL,
  `question_type` int(11) NOT NULL COMMENT '1.单选题，2.多选题，3.填空题，关联字典表',
  `question_titile` varchar(255) NOT NULL,
  `question_desc` varchar(255) DEFAULT NULL,
  `order_filed` int(11) DEFAULT NULL COMMENT 'int型，从1开始，越小优先级越高',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `modify_time` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `operator` int(11) DEFAULT NULL,
  PRIMARY KEY (`vote_paper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票问卷表';

-- ----------------------------
-- Table structure for qw_em_vote_paper_option
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_vote_paper_option`;
CREATE TABLE `qw_em_vote_paper_option` (
  `vote_paper_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_paper_id` int(11) NOT NULL,
  `option_titile` varchar(255) NOT NULL,
  `option_desc` varchar(255) DEFAULT NULL,
  `option_pic` varchar(255) DEFAULT NULL,
  `order_filed` int(11) DEFAULT NULL COMMENT 'int型，从1开始，越小优先级越高',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `modify_time` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `operator` int(11) DEFAULT NULL,
  PRIMARY KEY (`vote_paper_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票问卷选项表';

-- ----------------------------
-- Table structure for qw_em_vote_result
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_vote_result`;
CREATE TABLE `qw_em_vote_result` (
  `vote_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_paper_id` int(11) NOT NULL,
  `vote_id` int(11) NOT NULL,
  `option_value` varchar(255) DEFAULT NULL,
  `answer` varchar(255) DEFAULT NULL,
  `vote_type` int(11) NOT NULL COMMENT '1.业主投票，2.补录，关联字典表',
  `user_id` int(11) NOT NULL COMMENT '投票用户，关联用户表',
  `order_filed` int(11) DEFAULT NULL COMMENT 'int型，从1开始，越小优先级越高',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `modify_time` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `operator` int(11) DEFAULT NULL,
  PRIMARY KEY (`vote_result_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票结果表';

-- ----------------------------
-- Table structure for qw_em_vote_village
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_vote_village`;
CREATE TABLE `qw_em_vote_village` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_id` int(11) NOT NULL,
  `village_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
