/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : qwadmin

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-11-03 12:45:39
*/




-- ----------------------------
-- Table structure for qw_em_building
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_building`;
CREATE TABLE `qw_em_building` (
  `BUILDING_ID` int(11) NOT NULL AUTO_INCREMENT,
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
-- Records of qw_em_building
-- ----------------------------

-- ----------------------------
-- Table structure for qw_em_dictionary
-- ----------------------------
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_dictionary`;
CREATE TABLE `qw_em_dictionary` (
  `DICT_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DICT_NAME` varchar(64) DEFAULT NULL COMMENT '字典名称',
  `DICT_KEY` varchar(64) NOT NULL COMMENT '字典KEY',
  `DICT_VALUE` varchar(64) NOT NULL COMMENT '字典值',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `DICT_ORDER_BY` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`DICT_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='系统字典表';

-- ----------------------------
-- Records of qw_em_dictionary
-- ----------------------------
INSERT INTO `qw_em_dictionary` VALUES ('1', 'buildingType', '住宅', '1', null, '2017-11-02 17:12:12', null);
INSERT INTO `qw_em_dictionary` VALUES ('2', 'buildingType', '公寓', '2', null, '2017-11-02 17:12:43', null);
INSERT INTO `qw_em_dictionary` VALUES ('3', 'buildingType', '商铺', '3', '2017-11-02 17:13:34', '2017-11-02 17:12:43', null);
INSERT INTO `qw_em_dictionary` VALUES ('4', 'buildingOrientation', '朝南', '1', '2017-11-02 17:14:37', '2017-11-02 17:14:35', null);
INSERT INTO `qw_em_dictionary` VALUES ('5', 'buildingOrientation', '朝北', '2', '2017-11-02 17:14:37', '2017-11-02 17:14:35', null);
INSERT INTO `qw_em_dictionary` VALUES ('6', 'buildingOrientation', '朝东', '3', '2017-11-02 17:14:37', '2017-11-02 17:14:35', null);
INSERT INTO `qw_em_dictionary` VALUES ('7', 'buildingOrientation', '朝西', '4', '2017-11-02 17:14:37', '2017-11-02 17:14:35', null);
INSERT INTO `qw_em_dictionary` VALUES ('8', 'buildingType', '写字楼', '5', '2017-11-02 17:14:37', '2017-11-02 17:14:35', null);
INSERT INTO `qw_em_dictionary` VALUES ('9', 'buildingStructure', '砖混', '1', '2017-11-02 17:14:37', '2017-11-02 17:14:35', null);
INSERT INTO `qw_em_dictionary` VALUES ('10', 'buildingStructure', '混泥土', '2', '2017-11-02 17:14:37', '2017-11-02 17:14:35', null);
INSERT INTO `qw_em_dictionary` VALUES ('11', 'buildingStructure', '钢结构', '3', '2017-11-02 17:14:37', '2017-11-02 17:14:35', null);
INSERT INTO `qw_em_dictionary` VALUES ('12', 'houseType', '住宅', '1', '2017-11-03 23:32:18', '2017-11-03 23:32:21', null);
INSERT INTO `qw_em_dictionary` VALUES ('13', 'houseType', '公寓', '2', '2017-11-03 23:32:18', '2017-11-03 23:32:21', null);
INSERT INTO `qw_em_dictionary` VALUES ('14', 'houseType', '办公', '3', '2017-11-03 23:32:18', '2017-11-03 23:32:21', null);
INSERT INTO `qw_em_dictionary` VALUES ('15', 'houseType', '厂房', '4', '2017-11-03 23:32:18', '2017-11-03 23:32:21', null);
INSERT INTO `qw_em_dictionary` VALUES ('16', 'houseType', '仓库', '5', '2017-11-03 23:32:18', '2017-11-03 23:32:21', null);
INSERT INTO `qw_em_dictionary` VALUES ('17', 'houseType', '商铺', '6', '2017-11-03 23:32:18', '2017-11-03 23:32:21', null);
INSERT INTO `qw_em_dictionary` VALUES ('18', 'houseType', '酒店', '7', '2017-11-03 23:32:18', '2017-11-03 23:32:21', null);
INSERT INTO `qw_em_dictionary` VALUES ('19', 'houseType', '别墅', '8', '2017-11-03 23:32:18', '2017-11-03 23:32:21', null);
INSERT INTO `qw_em_dictionary` VALUES ('20', 'houseType', '其他', '9', '2017-11-03 23:32:18', '2017-11-03 23:32:21', null);
INSERT INTO `qw_em_dictionary` VALUES ('21', 'householdType', '集体户口', '1', '2017-11-06 15:02:08', '2017-11-06 15:02:10', null);
INSERT INTO `qw_em_dictionary` VALUES ('22', 'householdType', '城镇户口', '2', '2017-11-06 15:02:45', '2017-11-06 15:02:47', null);
INSERT INTO `qw_em_dictionary` VALUES ('23', 'householdType', '农村居民户口', '3', '2017-11-06 15:02:45', '2017-11-06 15:02:47', null);
INSERT INTO `qw_em_dictionary` VALUES ('24', 'householdStatus', '业主本人', '1', '2017-11-06 15:12:44', '2017-11-06 15:12:46', null);
INSERT INTO `qw_em_dictionary` VALUES ('25', 'householdStatus', '亲属', '2', '2017-11-06 15:13:15', '2017-11-06 15:13:17', null);
INSERT INTO `qw_em_dictionary` VALUES ('26', 'householdStatus', '租客', '3', '2017-11-06 15:13:15', '2017-11-06 15:13:17', null);
INSERT INTO `qw_em_dictionary` VALUES ('27', 'householdStatus', '其他', '4', '2017-11-06 15:13:15', '2017-11-06 15:13:17', null);
INSERT INTO `qw_em_dictionary` VALUES ('28', 'authResult', '已迁入', '1', '2017-11-06 15:14:59', '2017-11-06 15:15:01', null);
INSERT INTO `qw_em_dictionary` VALUES ('29', 'authResult', '待审核', '2', '2017-11-06 15:15:26', '2017-11-06 15:15:28', null);
INSERT INTO `qw_em_dictionary` VALUES ('30', 'authResult', '未通过', '3', '2017-11-06 15:15:26', '2017-11-06 15:15:28', null);
INSERT INTO `qw_em_dictionary` VALUES ('31', 'authResult', '已迁出', '4', '2017-11-06 15:15:26', '2017-11-06 15:15:28', null);

-- ----------------------------
-- Table structure for qw_em_house
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_house`;
CREATE TABLE `qw_em_house` (
  `HOUSE_ID` int(11) NOT NULL AUTO_INCREMENT,
  `HOUSE_NAME` varchar(255) DEFAULT NULL COMMENT '房屋名称',
  `VILLAGE` int(11) NOT NULL COMMENT '所属小区',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `OPERATOR` int(11) DEFAULT NULL,
  `BUILDING` int(11) NOT NULL COMMENT '所属楼宇',
  `UNIT` int(11) NOT NULL COMMENT '所属单元',
  `FLOOR` int(11) DEFAULT NULL COMMENT '所在楼层',
  `BUILD_UP_AREA` double DEFAULT NULL COMMENT '建筑面积',
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
-- Records of qw_em_house
-- ----------------------------

-- ----------------------------
-- Table structure for qw_em_household
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_household`;
CREATE TABLE `qw_em_household` (
  `HOUSEHOLD_ID` int(11) NOT NULL AUTO_INCREMENT,
  `HOUSEHOLD_NAME` varchar(255) DEFAULT NULL COMMENT '住户名称',
  `CREATE_TIME` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `MODIFY_TIME` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `OPERATOR` int(11) DEFAULT NULL,
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
  `AUTH_RESULT` int(11) DEFAULT NULL COMMENT '认证结果（1.已迁入，2.带审核，3.未通过，4.已迁出）',
  `LOGIN_TIMES` int(11) DEFAULT NULL COMMENT '登录次数',
  `LAST_LOGIN_TIME` timestamp NULL DEFAULT NULL COMMENT '最后一次登录时间',
  `HOUSEHOLD_TYPE` int(11) DEFAULT NULL COMMENT '住户类型，关联字典表（1.集体户口，2城镇户口，3.农村居民户口）',
  PRIMARY KEY (`HOUSEHOLD_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='房屋住户信息表';

-- ----------------------------
-- Records of qw_em_household
-- ----------------------------

-- ----------------------------
-- Table structure for qw_em_house_household
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_house_household`;
CREATE TABLE `qw_em_house_household` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HOUSE_ID` int(11) NOT NULL,
  `HOUSEHOLD_ID` int(11) NOT NULL,
  `HOUSEHOLD_STATUS` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='房屋住户信息关联表，多对多关系';


-- ----------------------------
-- Table structure for qw_em_parking_lot
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_parking_lot`;
CREATE TABLE `qw_em_parking_lot` (
  `PARKING_LOT_ID` int(11) NOT NULL AUTO_INCREMENT,
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
-- Records of qw_em_parking_lot
-- ----------------------------

-- ----------------------------
-- Table structure for qw_em_smsmodel
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_smsmodel`;
CREATE TABLE `qw_em_smsmodel` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `smscontent` text COMMENT '短信内容',
  `creater` int(11) DEFAULT NULL COMMENT '创建人',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `modifier` int(11) DEFAULT NULL COMMENT '修改人',
  `modifytime` datetime DEFAULT NULL COMMENT '修改时间',
  `status` tinyint(1) NOT NULL COMMENT '状态1：正常；0：禁用',
  `smstype` varchar(10) DEFAULT NULL COMMENT '类型1：短信模板2：微信模板',
  `isapprove` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否审核通过0：未通过 1：通过',
  `smstitle` varchar(255) DEFAULT NULL COMMENT '标题',
  `signname` varchar(255) DEFAULT '' COMMENT '签名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for qw_em_sys_org
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_sys_org`;
CREATE TABLE `qw_em_sys_org` (
  `ORG_ID` int(11) NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='组织机构表';

-- ----------------------------
-- Records of qw_em_sys_org
-- ----------------------------
INSERT INTO `qw_em_sys_org` VALUES ('1', '安徽省', '340000', null, '1', '0', '2017-10-28 20:14:34', null, null, null);
INSERT INTO `qw_em_sys_org` VALUES ('2', '合肥市', '340100', null, '2', '1', '2017-10-28 20:15:07', null, null, null);
INSERT INTO `qw_em_sys_org` VALUES ('3', '安庆市', '340800', null, '2', '1', '2017-10-28 20:15:33', null, null, null);
INSERT INTO `qw_em_sys_org` VALUES ('4', '合肥市辖区', '340101', null, '3', '2', '2017-10-28 20:16:16', null, null, null);
INSERT INTO `qw_em_sys_org` VALUES ('5', '瑶海区', '340102', null, '3', '2', '2017-10-28 20:17:06', null, null, null);
INSERT INTO `qw_em_sys_org` VALUES ('6', '庐阳区', '340103', null, '3', '2', '2017-10-28 20:17:30', null, null, null);
INSERT INTO `qw_em_sys_org` VALUES ('7', '蜀山区', '340104', null, '3', '2', '2017-10-28 20:18:48', null, null, null);
INSERT INTO `qw_em_sys_org` VALUES ('8', '梦城街道', '34010401', null, '4', '7', '2017-10-28 20:19:34', null, null, null);
INSERT INTO `qw_em_sys_org` VALUES ('9', '天乐社区', '3401040101', null, '5', '8', '2017-10-28 20:19:58', null, null, null);
INSERT INTO `qw_em_sys_org` VALUES ('10', '宿松县', '340826', null, '3', '3', '2017-10-30 01:26:58', null, null, null);

-- ----------------------------
-- Table structure for qw_em_unit
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_unit`;
CREATE TABLE `qw_em_unit` (
  `UNIT_ID` int(11) NOT NULL AUTO_INCREMENT,
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
-- Records of qw_em_unit
-- ----------------------------

-- ----------------------------
-- Table structure for qw_em_vehicle
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_vehicle`;
CREATE TABLE `qw_em_vehicle` (
  `VEHICLE_ID` int(11) NOT NULL AUTO_INCREMENT,
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
-- Records of qw_em_vehicle
-- ----------------------------

-- ----------------------------
-- Table structure for qw_em_village
-- ----------------------------
DROP TABLE IF EXISTS `qw_em_village`;
CREATE TABLE `qw_em_village` (
  `VILLAGE_ID` int(11) NOT NULL AUTO_INCREMENT,
  `VILLAGE_NAME` varchar(255) NOT NULL COMMENT '小区名称',
  `PROVINCE` int(11) NOT NULL COMMENT '所属省',
  `CITY` int(11) NOT NULL COMMENT '所属市',
  `COUNTY` int(11) NOT NULL COMMENT '所属县区',
  `STREET` varchar(255) DEFAULT NULL COMMENT '所属街道社区',
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


DROP TABLE IF EXISTS `qw_em_smslog`;
CREATE TABLE `qw_em_smslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `smscontent` text COMMENT '短信内容',
  `creater` int(11) DEFAULT NULL COMMENT '创建人',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `amount` int(11) DEFAULT NULL COMMENT '发送消息数量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='短信发送记录';


DROP TABLE IF EXISTS `qw_em_contentmanager`;
CREATE TABLE `qw_em_contentmanager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contenttitile` varchar(255) DEFAULT NULL COMMENT '模块名称',
  `contenttype` int(11) DEFAULT NULL COMMENT '模块类型',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `creater` int(11) DEFAULT NULL COMMENT '创建人',
  `modifier` int(11) DEFAULT NULL COMMENT '修改人id',
  `modifytime` datetime DEFAULT NULL COMMENT '最后一次修改时间',
  `status` int(11) DEFAULT '1' COMMENT '状态1生效，0失效',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='内容管理';

DROP TABLE IF EXISTS `qw_em_notice`;
CREATE TABLE `qw_em_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noticetitle` varchar(255) DEFAULT NULL,
  `noticepicture` varchar(255) DEFAULT NULL,
  `noticecontent` text,
  `contentid` int(11) DEFAULT NULL COMMENT '关联到内容模块id(em_contentmanager)',
  `istop` tinyint(4) DEFAULT '0' COMMENT '是否置顶0不置顶1置顶',
  `creater` int(11) DEFAULT NULL COMMENT '创建人id',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `modifier` int(11) DEFAULT NULL COMMENT '最后修改人id',
  `modifytime` datetime DEFAULT NULL COMMENT '最后一次修改时间',
  `stauts` int(11) DEFAULT '1' COMMENT '状态1正常0不显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='文章';


DROP TABLE IF EXISTS `qw_em_noticetovillage`;
CREATE TABLE `qw_em_noticetovillage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noticeid` int(11) DEFAULT NULL COMMENT '关联文章',
  `villageid` int(11) DEFAULT NULL COMMENT '文章关联小区id',
  `creater` int(11) DEFAULT NULL COMMENT '创建人',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `modifier` int(11) DEFAULT NULL COMMENT '修改人',
  `modifytime` datetime DEFAULT NULL COMMENT '修改时间',
  `status` int(11) DEFAULT NULL COMMENT '状态1显示0不显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章管理哪些小区';


ALTER TABLE `qw_member` ADD `openid` VARCHAR(32) NULL COMMENT '微信授权码' AFTER `t`;

DROP TABLE IF EXISTS `qw_em_news`;
CREATE TABLE `qw_em_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `newspicture` varchar(255) DEFAULT NULL COMMENT '图片地址',
  `newstitle` varchar(255) DEFAULT NULL COMMENT '图文标题',
  `newssummary` varchar(2000) DEFAULT NULL COMMENT '简介',
  `newscontent` text,
  `newsouturl` varchar(2000) DEFAULT NULL COMMENT '外链地址',
  `creater` int(255) DEFAULT NULL COMMENT '创建人',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `modifier` int(11) DEFAULT NULL COMMENT '修改人',
  `modifytime` datetime DEFAULT NULL COMMENT '修改时间',
  `status` int(11) DEFAULT NULL COMMENT '状态：1启用,0禁用',
  `thumb_media_id` varchar(200) NOT NULL COMMENT '图文消息的封面图片素材id（必须是永久mediaID）',
  `media_id` varchar(200) NOT NULL COMMENT '最终图文素材的id',
  `url` varchar(255) DEFAULT NULL COMMENT '封面图片素材url',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='图文主表';

DROP TABLE IF EXISTS `qw_em_newsitems`;
CREATE TABLE `qw_em_newsitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `newsid` int(11) DEFAULT NULL COMMENT '关联图文主表',
  `itempicture` varchar(255) DEFAULT NULL COMMENT '图片地址',
  `itemtitle` varchar(255) DEFAULT NULL COMMENT '图文标题',
  `itemsummary` varchar(2000) DEFAULT NULL COMMENT '简介',
  `itemcontent` text,
  `itemouturl` varchar(2000) DEFAULT NULL COMMENT '外链地址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='图文明细表';

