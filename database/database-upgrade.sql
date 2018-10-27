/*

SQLyog Ultimate v8.55 
MySQL - 5.7.21 : Database - fomeco

*********************************************************************

*/



/*!40101 SET NAMES utf8 */;



/*!40101 SET SQL_MODE=''*/;



/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`fomeco` /*!40100 DEFAULT CHARACTER SET utf8 */;

/*Table structure for table `phppos_mrp_production_planning_detail_sub` */

USE `fomeco`;


DROP TABLE IF EXISTS `phppos_mrp_production_planning_detail_sub`;
CREATE TABLE `phppos_mrp_production_planning_detail_sub` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `detail` text DEFAULT NULL,
  `month` varchar(25) DEFAULT NULL,
  `status` int(1) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;


/*Table structure for table `phppos_purchase_order_items` */



DROP TABLE IF EXISTS `phppos_purchase_order_items`;



CREATE TABLE `phppos_purchase_order_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int(10) unsigned DEFAULT NULL,
  `item_id` int(10) unsigned DEFAULT NULL,
  `quantity` smallint(4) unsigned DEFAULT NULL,
  `month` varchar(25) DEFAULT NULL,
  `progress` varchar(255) DEFAULT NULL COMMENT 'Tiến độ',
  `comment` varchar(525) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;



/*Table structure for table `phppos_purchase_orders` */



DROP TABLE IF EXISTS `phppos_purchase_orders`;



CREATE TABLE `phppos_purchase_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_code` varchar(25) DEFAULT NULL,
  `person_id` int(10) unsigned DEFAULT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `receive_date` int(10) unsigned DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT NULL,
  `comment` varchar(525) DEFAULT NULL,
  `status` smallint(4) unsigned DEFAULT NULL,
  `created_at` int(10) unsigned DEFAULT NULL,
  `updated_at` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;

/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

