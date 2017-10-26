/*
Navicat MySQL Data Transfer

Source Server         : em
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : qwadmin

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-10-25 18:26:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for qw_em_building
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_building`;
CREATE TABLE `qw_em_building` (
  `BUILDING_ID` int(11) NOT NULL,
  `BUILDING_NAME` varchar(255) DEFAULT NULL COMMENT '楼宇名称',
  `VILLAGE` int(11) NOT NULL COMMENT '所属小区',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `OPERATOR` int(11) DEFAULT NULL,
  `UNIT_NUMBER` int(11) DEFAULT NULL COMMENT '单元数量',
  `FLOOR_NUMBER` int(11) DEFAULT NULL COMMENT '楼宇层数',
  `BUILDING_TYPE` int(11) DEFAULT NULL COMMENT '楼宇类型,关联字典表',
  `BUILDING_STRUCTURE` int(11) DEFAULT NULL COMMENT '楼宇结构，关联字典表',
  `BUILDING_ORIENTATION` int(11) DEFAULT NULL COMMENT '楼宇朝向，关联字典表',
  PRIMARY KEY (`BUILDING_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小区楼宇';

-- ----------------------------
-- Table structure for qw_em_dictionary
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_dictionary`;
CREATE TABLE `qw_em_dictionary` (
  `DICT_ID` int(11) NOT NULL,
  `DICT_NAME` varchar(64) DEFAULT NULL COMMENT '字典名称',
  `DICT_KEY` varchar(64) NOT NULL COMMENT '字典KEY',
  `DICT_VALUE` varchar(64) NOT NULL COMMENT '字典值',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `DICT_ORDER_BY` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`DICT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统字典表';

-- ----------------------------
-- Table structure for qw_em_house
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_house`;
CREATE TABLE `qw_em_house` (
  `HOUSE_ID` int(11) NOT NULL,
  `HOUSE_NAME` varchar(255) DEFAULT NULL COMMENT '房屋名称',
  `VILLAGE` int(11) NOT NULL COMMENT '所属小区',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `OPERATOR` int(11) DEFAULT NULL,
  `BUILDING` int(11) NOT NULL COMMENT '所属楼宇',
  `UNIT` int(11) NOT NULL COMMENT '所属单元',
  `FLOOR` int(11) DEFAULT NULL COMMENT '所在楼层',
  `BUILT_UP_AREA` double DEFAULT NULL COMMENT '建筑面积',
  `SET_IN_AREA` double DEFAULT NULL COMMENT '套内面积',
  `POLL_AREA` double DEFAULT NULL COMMENT '公摊面积',
  `HOUSE_TYPE` int(11) DEFAULT NULL COMMENT '房屋类型',
  `HOUSE_STRUCTURE` int(11) DEFAULT NULL COMMENT '房屋结构',
  `HOUSE_ORIENTATION` int(11) DEFAULT NULL COMMENT '房屋朝向',
  `HOUSE_TRANSFER_TIME` timestamp NULL DEFAULT NULL COMMENT '交房时间',
  `PROPERTY_RIGHT_AGE` int(11) DEFAULT NULL COMMENT '产权年限',
  `ATTACHMENT` varchar(255) DEFAULT NULL COMMENT '附件',
  `REMARK` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`HOUSE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小区楼宇单元房屋';

-- ----------------------------
-- Table structure for qw_em_household
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_household`;
CREATE TABLE `qw_em_household` (
  `HOUSEHOLD_ID` int(11) NOT NULL,
  `HOUSEHOLD_NAME` varchar(255) DEFAULT NULL COMMENT '住户名称',
  `VILLAGE` int(11) NOT NULL COMMENT '所属小区',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `OPERATOR` int(11) DEFAULT NULL,
  `HOUSE` int(11) NOT NULL COMMENT '所属房屋',
  `REMARK` varchar(1000) DEFAULT NULL,
  `NICKNAME` varchar(255) DEFAULT NULL COMMENT '昵称',
  `TEL` varchar(20) DEFAULT NULL COMMENT '手机号',
  `WECHAT_ACCOUNT` varchar(100) DEFAULT NULL COMMENT '微信号',
  `WECHAT_NICKNAME` varchar(255) DEFAULT NULL COMMENT '微信昵称',
  `QQ` varchar(20) DEFAULT NULL COMMENT 'QQ号',
  `QQ_NICKNAME` varchar(255) DEFAULT NULL COMMENT 'QQ昵称',
  `ALIPAY_ACCOUNT` varchar(100) DEFAULT NULL COMMENT '支付宝帐号',
  `ALIPAY_NICKNAME` varchar(255) DEFAULT NULL COMMENT '支付宝昵称',
  `EMAIL` varchar(255) DEFAULT NULL COMMENT '邮箱',
  `HOME_TEL` varchar(20) DEFAULT NULL COMMENT '家庭电话',
  `CARD_NUMBER` varchar(30) DEFAULT NULL COMMENT '业主卡号',
  `DOOR_CARD_NUMBER` varchar(30) DEFAULT NULL COMMENT '门禁卡号',
  `FIRST_LOGIN_TIME` timestamp NULL DEFAULT NULL COMMENT '注册/首次登陆时间',
  `AUTH_TIME` timestamp NULL DEFAULT NULL COMMENT '迁入/认证时间',
  `MOVE_REASON` varchar(255) DEFAULT NULL COMMENT '迁入原因',
  `AUTH_RESULT` varchar(100) DEFAULT NULL COMMENT '认证结果',
  `LOGIN_TIMES` int(11) DEFAULT NULL COMMENT '登录次数',
  `LAST_LOGIN_TIME` timestamp NULL DEFAULT NULL COMMENT '最后一次登录时间',
  `HOUSEHOLD_TYPE` int(11) DEFAULT NULL COMMENT '住户类型，关联字典表',
  PRIMARY KEY (`HOUSEHOLD_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='房屋住户信息表';

-- ----------------------------
-- Table structure for qw_em_parking_lot
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_parking_lot`;
CREATE TABLE `qw_em_parking_lot` (
  `PARKING_LOT_ID` int(11) NOT NULL,
  `PARKING_LOT_CODE` varchar(255) NOT NULL COMMENT '车位号',
  `VILLAGE` int(11) NOT NULL COMMENT '所属小区',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `OPERATOR` int(11) DEFAULT NULL,
  `STATUS` int(11) DEFAULT NULL COMMENT '车位状态，关联字典表',
  `REMARK` varchar(1000) DEFAULT NULL COMMENT '备注',
  `PROPERTY_OWNER` varchar(50) DEFAULT NULL COMMENT '产权人',
  `USER` varchar(50) DEFAULT NULL COMMENT '使用人',
  `PROPERTY_AREA` double DEFAULT NULL COMMENT '产权面积',
  PRIMARY KEY (`PARKING_LOT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小区车位信息表';

-- ----------------------------
-- Table structure for qw_em_sys_org
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_sys_org`;
CREATE TABLE `qw_em_sys_org` (
  `ORG_ID` int(11) NOT NULL,
  `ORG_NAME` varchar(255) DEFAULT NULL COMMENT '组织机构名称',
  `ORG_CODE` varchar(255) NOT NULL COMMENT '组织机构编码',
  `ORG_DESC` varchar(1000) DEFAULT NULL COMMENT '描述',
  `ORG_TYPE` int(11) DEFAULT NULL COMMENT '组织机构类型：1.省，2.市，3.县（区），4.街道（社区），5.社居委',
  `PARENT_ID` int(11) DEFAULT NULL COMMENT '父机构ID',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `CONTACTS` varchar(100) DEFAULT NULL COMMENT '联系人',
  `TEL` varchar(20) DEFAULT NULL COMMENT '联系电话',
  PRIMARY KEY (`ORG_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='组织机构表';

-- ----------------------------
-- Table structure for qw_em_unit
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_unit`;
CREATE TABLE `qw_em_unit` (
  `UNIT_ID` int(11) NOT NULL,
  `UNIT_NAME` varchar(255) DEFAULT NULL COMMENT '单元名称',
  `VILLAGE` int(11) NOT NULL COMMENT '所属小区',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `OPERATOR` int(11) DEFAULT NULL,
  `BUILDING` int(11) NOT NULL COMMENT '所属楼宇',
  `LOWEST` int(11) DEFAULT NULL COMMENT '单元最低楼层',
  `HIGHEST` int(11) DEFAULT NULL COMMENT '单元最高楼层',
  `ENTRY_AND_EXIT_NUMBER` int(11) DEFAULT NULL COMMENT '单元出入口数量',
  PRIMARY KEY (`UNIT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小区楼宇单元';

-- ----------------------------
-- Table structure for qw_em_vehicle
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_vehicle`;
CREATE TABLE `qw_em_vehicle` (
  `VEHICLE_ID` int(11) NOT NULL,
  `PLATE_NUMBER` varchar(20) NOT NULL COMMENT '车牌号',
  `PARKING_LOT_CODE` varchar(255) NOT NULL COMMENT '所属车位',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `OPERATOR` int(11) DEFAULT NULL,
  `REMARK` varchar(1000) DEFAULT NULL COMMENT '备注',
  `BRAND` varchar(50) DEFAULT NULL COMMENT '产权人',
  `VEHICLE_TYPE` int(11) DEFAULT NULL COMMENT '车辆类型，关联字典表',
  `HOLDER` varchar(50) DEFAULT NULL COMMENT '产权面积',
  `MOTOR_CODE` varchar(50) DEFAULT NULL,
  `VEHICLE_ID_CODE` varchar(50) DEFAULT NULL,
  `USER` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`VEHICLE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小区车辆信息表';

-- ----------------------------
-- Table structure for qw_em_village
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_village`;
CREATE TABLE `qw_em_village` (
  `VILLAGE_ID` int(11) NOT NULL,
  `VILLAGE_NAME` varchar(255) NOT NULL COMMENT '小区名称',
  `PROVINCE` int(11) NOT NULL COMMENT '所属省',
  `CITY` int(11) NOT NULL COMMENT '所属市',
  `COUNTY` int(11) NOT NULL COMMENT '所属县区',
  `STREET` int(11) NOT NULL COMMENT '所属街道社区',
  `NEIGH_COMMITTEE` int(11) DEFAULT NULL COMMENT '所属社居委',
  `PROPERTY_COMPANY` varchar(255) DEFAULT NULL COMMENT '物业服务公司',
  `PROPERTY_CUSTOMER_TEL` varchar(20) DEFAULT NULL COMMENT '物业客服电话',
  `PROPERTY_CHARGE_PERSON` varchar(100) DEFAULT NULL COMMENT '物业负责人',
  `PROPERTY_CHARGE_PERSON_TEL` varchar(20) DEFAULT NULL COMMENT '物业负责人电话',
  `OWNERS_COMMITTEE_CONTACTS` varchar(100) DEFAULT NULL COMMENT '业主委员会联系人',
  `OWNERS_COMMITTEE_TEL` varchar(20) DEFAULT NULL COMMENT '业主委员会电话',
  `VILLAGE_CONTACTS` varchar(100) DEFAULT NULL COMMENT '联系人',
  `VILLAGE_CONTACTS_TEL` varchar(20) DEFAULT NULL COMMENT '联系人电话',
  `VILLAGE_LOGO` varchar(255) DEFAULT NULL COMMENT '小区LOGO',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `OPERATOR` int(11) DEFAULT NULL,
  PRIMARY KEY (`VILLAGE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小区表';
