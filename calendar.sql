/*
SQLyog Ultimate v12.5.0 (64 bit)
MySQL - 5.7.18-log : Database - db_mcp_1007
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_mcp_1007` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `db_mcp_1007`;

/*Table structure for table `t_calendar_detail` */

DROP TABLE IF EXISTS `t_calendar_detail`;

CREATE TABLE `t_calendar_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `c_id` int(11) unsigned NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '事件标题',
  `start_time` date NOT NULL COMMENT '事件开始时间',
  `end_time` date NOT NULL COMMENT '事件结束时间',
  `push_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '推送方式，0-不推送，1-短信，2-微信，3-短信加微信',
  `is_manager` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否管理员发布，1-是，0-否',
  `release_user_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '发布人code',
  `release_user_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '发布人名称',
  `cron_ids` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '推送主键',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '编辑时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `t_calendar_detail` */

insert  into `t_calendar_detail`(`id`,`c_id`,`title`,`start_time`,`end_time`,`push_type`,`is_manager`,`release_user_code`,`release_user_name`,`cron_ids`,`created_at`,`updated_at`) values 
(21,7,'32424','2019-04-20','2019-04-20',3,1,'admin','admin','72','2019-04-19 15:44:21','2019-04-19 15:44:21'),
(22,7,'签到','2019-04-23','2019-04-23',1,1,'159009','井志远','73','2019-04-22 13:08:44','2019-04-22 13:08:44'),
(23,13,'签到2222','2019-04-23','2019-04-23',1,1,'159009','井志远','74','2019-04-22 13:33:27','2019-04-22 13:33:27'),
(24,13,'放假啊开了房骄傲四六级老司机付款了桑卡是拉拉肥啥的佛山大，啊我哦啊了覆盖面拉萨见覅偶未来案件发拉风家乐福据了解 斐林试剂干 附加费卡拉设计费发了开发就来 看了放大快乐番薯辣鸡 就发送待缴费is理发了数','2019-04-24','2019-04-24',1,1,'159009','井志远','77','2019-04-22 13:46:43','2019-04-22 13:46:43'),
(25,13,'哈哈哈','2019-04-22','2019-04-21',3,1,'159009','井志远','','2019-04-22 13:57:30','2019-04-22 13:57:30');

/*Table structure for table `t_calendar_detail_user` */

DROP TABLE IF EXISTS `t_calendar_detail_user`;

CREATE TABLE `t_calendar_detail_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `d_id` int(11) NOT NULL,
  `user_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `t_calendar_detail_user` */

insert  into `t_calendar_detail_user`(`id`,`d_id`,`user_code`,`user_name`,`created_at`,`updated_at`) values 
(21,4,'56900','张媛','2019-04-03 18:59:53','2019-04-03 18:59:53'),
(22,4,'90017','刘洪','2019-04-03 18:59:53','2019-04-03 18:59:53'),
(23,4,'57878','陈丹旭','2019-04-03 18:59:53','2019-04-03 18:59:53'),
(24,4,'27845','陈晨','2019-04-03 18:59:53','2019-04-03 18:59:53'),
(25,5,'000809','易念','2019-04-04 09:43:40','2019-04-04 09:43:40'),
(30,9,'10059','李莉','2019-04-04 13:51:48','2019-04-04 13:51:48'),
(31,9,'56900','张媛','2019-04-04 13:51:48','2019-04-04 13:51:48'),
(34,11,'000809','易念','2019-04-04 16:23:24','2019-04-04 16:23:24'),
(35,11,'15754','张居祥','2019-04-04 16:23:24','2019-04-04 16:23:24'),
(39,12,'10059','李莉','2019-04-08 11:13:43','2019-04-08 11:13:43'),
(40,12,'56900','张媛','2019-04-08 11:13:43','2019-04-08 11:13:43'),
(41,12,'90017','刘洪','2019-04-08 11:13:43','2019-04-08 11:13:43'),
(42,13,'000809','易念','2019-04-12 09:23:15','2019-04-12 09:23:15'),
(43,13,'15754','张居祥','2019-04-12 09:23:15','2019-04-12 09:23:15'),
(44,13,'10059','李莉','2019-04-12 09:23:15','2019-04-12 09:23:15'),
(45,13,'56900','张媛','2019-04-12 09:23:15','2019-04-12 09:23:15'),
(46,14,'000809','易念','2019-04-16 14:14:02','2019-04-16 14:14:02'),
(47,14,'15754','张居祥','2019-04-16 14:14:02','2019-04-16 14:14:02'),
(48,15,'90017','刘洪','2019-04-16 14:20:21','2019-04-16 14:20:21'),
(49,15,'57878','陈丹旭','2019-04-16 14:20:21','2019-04-16 14:20:21'),
(50,16,'wangtianyu','王天宇','2019-04-17 15:46:04','2019-04-17 15:46:04'),
(51,16,'000811','王天宇','2019-04-17 15:46:04','2019-04-17 15:46:04'),
(52,17,'admin','admin','2019-04-17 15:49:51','2019-04-17 15:49:51'),
(53,18,'000811','王天宇','2019-04-18 10:14:32','2019-04-18 10:14:32'),
(54,18,'159009','井志远','2019-04-18 10:14:32','2019-04-18 10:14:32'),
(65,20,'000811','王天宇','2019-04-19 13:19:49','2019-04-19 13:19:49'),
(66,20,'159009','井志远','2019-04-19 13:19:49','2019-04-19 13:19:49'),
(67,21,'159009','井志远','2019-04-19 15:44:21','2019-04-19 15:44:21'),
(68,22,'159009','井志远','2019-04-22 13:08:44','2019-04-22 13:08:44'),
(69,23,'000802','易念','2019-04-22 13:33:27','2019-04-22 13:33:27'),
(70,23,'wangtianyu','王天宇','2019-04-22 13:33:27','2019-04-22 13:33:27'),
(71,23,'000811','王天宇','2019-04-22 13:33:27','2019-04-22 13:33:27'),
(72,23,'159009','井志远','2019-04-22 13:33:27','2019-04-22 13:33:27'),
(73,23,'000812','殷槐伟','2019-04-22 13:33:27','2019-04-22 13:33:27'),
(74,23,'000806','史月新开发区','2019-04-22 13:33:27','2019-04-22 13:33:27'),
(75,24,'000802','易念','2019-04-22 13:46:43','2019-04-22 13:46:43'),
(76,24,'wangtianyu','王天宇','2019-04-22 13:46:43','2019-04-22 13:46:43'),
(77,24,'000811','王天宇','2019-04-22 13:46:43','2019-04-22 13:46:43'),
(78,24,'159009','井志远','2019-04-22 13:46:43','2019-04-22 13:46:43'),
(79,24,'000812','殷槐伟','2019-04-22 13:46:43','2019-04-22 13:46:43'),
(80,24,'000806','史月新开发区','2019-04-22 13:46:43','2019-04-22 13:46:43'),
(81,25,'000802','易念','2019-04-22 13:57:30','2019-04-22 13:57:30'),
(82,25,'wangtianyu','王天宇','2019-04-22 13:57:30','2019-04-22 13:57:30'),
(83,25,'000811','王天宇','2019-04-22 13:57:30','2019-04-22 13:57:30'),
(84,25,'159009','井志远','2019-04-22 13:57:30','2019-04-22 13:57:30'),
(85,25,'000812','殷槐伟','2019-04-22 13:57:30','2019-04-22 13:57:30'),
(86,25,'000806','史月新开发区','2019-04-22 13:57:30','2019-04-22 13:57:30');

/*Table structure for table `t_sys_calendar` */

DROP TABLE IF EXISTS `t_sys_calendar`;

CREATE TABLE `t_sys_calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` char(5) NOT NULL,
  `semester` tinyint(4) NOT NULL DEFAULT '1',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '标题',
  `start_time` date DEFAULT NULL COMMENT '开始时间',
  `end_time` date DEFAULT NULL COMMENT '结束时间',
  `end_holiday_date` date NOT NULL COMMENT '假期结束日期',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

/*Data for the table `t_sys_calendar` */

insert  into `t_sys_calendar`(`id`,`year`,`semester`,`title`,`start_time`,`end_time`,`end_holiday_date`,`created_at`,`updated_at`) values 
(11,'2017',2,'南京市2017～2018学年度第一学期校历','2017-08-30','2018-02-04','2018-03-01','2019-04-22 13:28:40','2019-04-22 13:28:40'),
(12,'2018',1,'2018~2019第一学期','2018-09-03','2019-01-20','2019-01-31','2019-04-22 13:29:24','2019-04-22 13:29:24'),
(13,'2019',2,'2018-2019年度第二学期','2019-02-18','2019-07-10','2019-08-31','2019-04-22 13:31:03','2019-04-22 13:31:03');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
