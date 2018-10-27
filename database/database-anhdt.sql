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

USE `fomeco`;

/*Table structure for table `phppos_additional_item_numbers` */

DROP TABLE IF EXISTS `phppos_additional_item_numbers`;

CREATE TABLE `phppos_additional_item_numbers` (
  `item_id` int(11) NOT NULL,
  `item_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`item_id`,`item_number`),
  UNIQUE KEY `item_number` (`item_number`),
  CONSTRAINT `phppos_additional_item_numbers_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_app_config` */

DROP TABLE IF EXISTS `phppos_app_config`;

CREATE TABLE `phppos_app_config` (
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_app_files` */

DROP TABLE IF EXISTS `phppos_app_files`;

CREATE TABLE `phppos_app_files` (
  `file_id` int(10) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_data` longblob NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_attribute_groups` */

DROP TABLE IF EXISTS `phppos_attribute_groups`;

CREATE TABLE `phppos_attribute_groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `code` text NOT NULL,
  `name` text NOT NULL,
  `description` text,
  `related_object` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_attribute_groups_combine` */

DROP TABLE IF EXISTS `phppos_attribute_groups_combine`;

CREATE TABLE `phppos_attribute_groups_combine` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `attr_id` int(10) NOT NULL,
  `attr_group_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_attribute_values` */

DROP TABLE IF EXISTS `phppos_attribute_values`;

CREATE TABLE `phppos_attribute_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` int(11) DEFAULT NULL,
  `entity_type` varchar(255) DEFAULT NULL,
  `attribute_id` int(11) DEFAULT NULL,
  `value` varchar(5000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=76 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_attributes` */

DROP TABLE IF EXISTS `phppos_attributes`;

CREATE TABLE `phppos_attributes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `code` text NOT NULL,
  `name` text NOT NULL,
  `description` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_categories` */

DROP TABLE IF EXISTS `phppos_categories`;

CREATE TABLE `phppos_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `hide_from_grid` int(1) NOT NULL DEFAULT '0',
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image_id` int(10) DEFAULT NULL,
  `color` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_categories_ibfk_1` (`parent_id`),
  KEY `phppos_categories_ibfk_2` (`image_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_customers` */

DROP TABLE IF EXISTS `phppos_customers`;

CREATE TABLE `phppos_customers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `person_id` int(10) NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `company_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `credit_limit` decimal(23,10) DEFAULT NULL,
  `points` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `disable_loyalty` int(1) NOT NULL DEFAULT '0',
  `current_spend_for_points` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `current_sales_for_discount` int(10) NOT NULL DEFAULT '0',
  `taxable` int(1) NOT NULL DEFAULT '1',
  `tax_certificate` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cc_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_ref_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_preview` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `card_issuer` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `tier_id` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `person_id` (`person_id`),
  KEY `deleted` (`deleted`),
  KEY `cc_token` (`cc_token`),
  KEY `phppos_customers_ibfk_2` (`tier_id`),
  KEY `company_name` (`company_name`),
  CONSTRAINT `phppos_customers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_customers_ibfk_2` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_customers_taxes` */

DROP TABLE IF EXISTS `phppos_customers_taxes`;

CREATE TABLE `phppos_customers_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`customer_id`,`name`,`percent`),
  CONSTRAINT `phppos_customers_taxes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_departments` */

DROP TABLE IF EXISTS `phppos_departments`;

CREATE TABLE `phppos_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `location_id` int(11) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`location_id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_ecommerce_products` */

DROP TABLE IF EXISTS `phppos_ecommerce_products`;

CREATE TABLE `phppos_ecommerce_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `product_quantity` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_employees` */

DROP TABLE IF EXISTS `phppos_employees`;

CREATE TABLE `phppos_employees` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `force_password_change` int(1) NOT NULL DEFAULT '0',
  `always_require_password` int(1) NOT NULL DEFAULT '0',
  `person_id` int(10) NOT NULL,
  `language` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commission_percent` decimal(23,10) DEFAULT '0.0000000000',
  `commission_percent_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `hourly_pay_rate` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `not_required_to_clock_in` int(1) NOT NULL DEFAULT '0',
  `inactive` int(1) NOT NULL DEFAULT '0',
  `reason_inactive` text COLLATE utf8_unicode_ci,
  `hire_date` date DEFAULT NULL,
  `employee_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `department_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `employee_number` (`employee_number`),
  KEY `person_id` (`person_id`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `phppos_employees_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_people` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_employees_locations` */

DROP TABLE IF EXISTS `phppos_employees_locations`;

CREATE TABLE `phppos_employees_locations` (
  `employee_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`employee_id`,`location_id`),
  KEY `phppos_employees_locations_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_employees_locations_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_employees_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_employees_reset_password` */

DROP TABLE IF EXISTS `phppos_employees_reset_password`;

CREATE TABLE `phppos_employees_reset_password` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `employee_id` int(11) NOT NULL,
  `expire` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `phppos_employees_reset_password_ibfk_1` (`employee_id`),
  CONSTRAINT `phppos_employees_reset_password_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_employees_time_clock` */

DROP TABLE IF EXISTS `phppos_employees_time_clock`;

CREATE TABLE `phppos_employees_time_clock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `clock_in` timestamp NULL DEFAULT NULL,
  `clock_out` timestamp NULL DEFAULT NULL,
  `clock_in_comment` text COLLATE utf8_unicode_ci NOT NULL,
  `clock_out_comment` text COLLATE utf8_unicode_ci NOT NULL,
  `hourly_pay_rate` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `ip_address_clock_in` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address_clock_out` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_employees_time_clock_ibfk_1` (`employee_id`),
  KEY `phppos_employees_time_clock_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_employees_time_clock_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_employees_time_clock_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_expenses` */

DROP TABLE IF EXISTS `phppos_expenses`;

CREATE TABLE `phppos_expenses` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `location_id` int(10) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `expense_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expense_description` text COLLATE utf8_unicode_ci,
  `expense_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expense_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expense_amount` decimal(23,10) NOT NULL,
  `expense_tax` decimal(23,10) NOT NULL,
  `expense_note` text COLLATE utf8_unicode_ci NOT NULL,
  `employee_id` int(10) NOT NULL,
  `approved_employee_id` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `employee_id` (`employee_id`),
  KEY `approved_employee_id` (`approved_employee_id`),
  KEY `category_id` (`category_id`),
  KEY `deleted` (`deleted`),
  KEY `expense_type` (`expense_type`),
  KEY `expense_date` (`expense_date`),
  KEY `expense_amount` (`expense_amount`),
  KEY `expense_description` (`expense_description`(255)),
  KEY `expense_reason` (`expense_reason`),
  KEY `expense_note` (`expense_note`(255)),
  CONSTRAINT `phppos_expenses_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_expenses_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_expenses_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`),
  CONSTRAINT `phppos_expenses_ibfk_4` FOREIGN KEY (`approved_employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_giftcards` */

DROP TABLE IF EXISTS `phppos_giftcards`;

CREATE TABLE `phppos_giftcards` (
  `giftcard_id` int(11) NOT NULL AUTO_INCREMENT,
  `giftcard_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `value` decimal(23,10) NOT NULL,
  `customer_id` int(10) DEFAULT NULL,
  `inactive` int(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`giftcard_id`),
  UNIQUE KEY `giftcard_number` (`giftcard_number`),
  KEY `deleted` (`deleted`),
  KEY `phppos_giftcards_ibfk_1` (`customer_id`),
  KEY `description` (`description`(255)),
  CONSTRAINT `phppos_giftcards_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_giftcards_log` */

DROP TABLE IF EXISTS `phppos_giftcards_log`;

CREATE TABLE `phppos_giftcards_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `giftcard_id` int(11) NOT NULL,
  `transaction_amount` decimal(23,10) NOT NULL,
  `log_message` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_giftcards_log_ibfk_1` (`giftcard_id`),
  CONSTRAINT `phppos_giftcards_log_ibfk_1` FOREIGN KEY (`giftcard_id`) REFERENCES `phppos_giftcards` (`giftcard_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_inventory` */

DROP TABLE IF EXISTS `phppos_inventory`;

CREATE TABLE `phppos_inventory` (
  `trans_id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_items` int(11) NOT NULL DEFAULT '0',
  `trans_user` int(11) NOT NULL DEFAULT '0',
  `trans_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trans_comment` text COLLATE utf8_unicode_ci NOT NULL,
  `trans_inventory` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`trans_id`),
  KEY `phppos_inventory_ibfk_1` (`trans_items`),
  KEY `phppos_inventory_ibfk_2` (`trans_user`),
  KEY `location_id` (`location_id`),
  KEY `trans_date` (`trans_date`,`trans_inventory`,`location_id`),
  CONSTRAINT `phppos_inventory_ibfk_1` FOREIGN KEY (`trans_items`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_inventory_ibfk_2` FOREIGN KEY (`trans_user`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_inventory_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_inventory_counts` */

DROP TABLE IF EXISTS `phppos_inventory_counts`;

CREATE TABLE `phppos_inventory_counts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count_date` timestamp NULL DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_inventory_counts_ibfk_1` (`employee_id`),
  KEY `phppos_inventory_counts_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_inventory_counts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_inventory_counts_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_inventory_counts_items` */

DROP TABLE IF EXISTS `phppos_inventory_counts_items`;

CREATE TABLE `phppos_inventory_counts_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_counts_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `count` decimal(23,10) DEFAULT '0.0000000000',
  `actual_quantity` decimal(23,10) DEFAULT '0.0000000000',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_inventory_counts_items_ibfk_1` (`inventory_counts_id`),
  KEY `phppos_inventory_counts_items_ibfk_2` (`item_id`),
  CONSTRAINT `phppos_inventory_counts_items_ibfk_1` FOREIGN KEY (`inventory_counts_id`) REFERENCES `phppos_inventory_counts` (`id`),
  CONSTRAINT `phppos_inventory_counts_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_item_images` */

DROP TABLE IF EXISTS `phppos_item_images`;

CREATE TABLE `phppos_item_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `alt_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `item_id` int(11) DEFAULT NULL,
  `ecommerce_image_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_item_images_ibfk_1` (`item_id`),
  KEY `phppos_item_images_ibfk_2` (`image_id`),
  CONSTRAINT `phppos_item_images_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_item_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_item_kit_items` */

DROP TABLE IF EXISTS `phppos_item_kit_items`;

CREATE TABLE `phppos_item_kit_items` (
  `item_kit_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` decimal(23,10) NOT NULL,
  PRIMARY KEY (`item_kit_id`,`item_id`,`quantity`),
  KEY `phppos_item_kit_items_ibfk_2` (`item_id`),
  CONSTRAINT `phppos_item_kit_items_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_item_kit_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_item_kits` */

DROP TABLE IF EXISTS `phppos_item_kits`;

CREATE TABLE `phppos_item_kits` (
  `item_kit_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_kit_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `product_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tax_included` int(1) NOT NULL DEFAULT '0',
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `is_ebt_item` int(1) NOT NULL DEFAULT '0',
  `commission_percent` decimal(23,10) DEFAULT '0.0000000000',
  `commission_percent_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `commission_fixed` decimal(23,10) DEFAULT '0.0000000000',
  `change_cost_price` int(1) NOT NULL DEFAULT '0',
  `disable_loyalty` int(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `aaatex_qb_item_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`item_kit_id`),
  UNIQUE KEY `item_kit_number` (`item_kit_number`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_item_kits_ibfk_1` (`category_id`),
  KEY `phppos_item_kits_ibfk_2` (`manufacturer_id`),
  KEY `name` (`name`),
  KEY `description` (`description`),
  KEY `cost_price` (`cost_price`),
  KEY `unit_price` (`unit_price`),
  CONSTRAINT `phppos_item_kits_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`),
  CONSTRAINT `phppos_item_kits_ibfk_2` FOREIGN KEY (`manufacturer_id`) REFERENCES `phppos_manufacturers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_item_kits_tags` */

DROP TABLE IF EXISTS `phppos_item_kits_tags`;

CREATE TABLE `phppos_item_kits_tags` (
  `item_kit_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`item_kit_id`,`tag_id`),
  KEY `phppos_item_kits_tags_ibfk_2` (`tag_id`),
  CONSTRAINT `phppos_item_kits_tags_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_item_kits_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_item_kits_taxes` */

DROP TABLE IF EXISTS `phppos_item_kits_taxes`;

CREATE TABLE `phppos_item_kits_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_kit_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`item_kit_id`,`name`,`percent`),
  CONSTRAINT `phppos_item_kits_taxes_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_item_kits_tier_prices` */

DROP TABLE IF EXISTS `phppos_item_kits_tier_prices`;

CREATE TABLE `phppos_item_kits_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT '0.0000000000',
  `percent_off` decimal(15,3) DEFAULT NULL,
  `cost_plus_percent` decimal(15,3) DEFAULT NULL,
  PRIMARY KEY (`tier_id`,`item_kit_id`),
  KEY `phppos_item_kits_tier_prices_ibfk_2` (`item_kit_id`),
  CONSTRAINT `phppos_item_kits_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_item_kits_tier_prices_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_items` */

DROP TABLE IF EXISTS `phppos_items`;

CREATE TABLE `phppos_items` (
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `item_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `product_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ecommerce_product_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `size` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tax_included` int(1) NOT NULL DEFAULT '0',
  `cost_price` decimal(23,10) DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `promo_price` decimal(23,10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reorder_level` decimal(23,10) DEFAULT NULL,
  `expire_days` int(10) DEFAULT NULL,
  `item_id` int(10) NOT NULL AUTO_INCREMENT,
  `allow_alt_description` tinyint(1) NOT NULL,
  `is_serialized` tinyint(1) NOT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `is_ecommerce` int(1) DEFAULT '1',
  `is_service` int(1) NOT NULL DEFAULT '0',
  `is_ebt_item` int(1) NOT NULL DEFAULT '0',
  `commission_percent` decimal(23,10) DEFAULT '0.0000000000',
  `commission_percent_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `commission_fixed` decimal(23,10) DEFAULT '0.0000000000',
  `change_cost_price` int(1) NOT NULL DEFAULT '0',
  `disable_loyalty` int(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `aaatex_qb_item_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unit_id` int(11) DEFAULT NULL,
  `limit` float DEFAULT '0',
  `buffer_rate` float DEFAULT '1',
  `attr_group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `item_number` (`item_number`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `phppos_items_ibfk_1` (`supplier_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_items_ibfk_3` (`category_id`),
  KEY `phppos_items_ibfk_4` (`manufacturer_id`),
  KEY `phppos_items_ibfk_5` (`ecommerce_product_id`),
  KEY `description` (`description`(255)),
  KEY `size` (`size`),
  KEY `reorder_level` (`reorder_level`),
  KEY `cost_price` (`cost_price`),
  KEY `unit_price` (`unit_price`),
  KEY `promo_price` (`promo_price`),
  KEY `last_modified` (`last_modified`),
  KEY `name` (`name`),
  CONSTRAINT `phppos_items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`),
  CONSTRAINT `phppos_items_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`),
  CONSTRAINT `phppos_items_ibfk_4` FOREIGN KEY (`manufacturer_id`) REFERENCES `phppos_manufacturers` (`id`),
  CONSTRAINT `phppos_items_ibfk_5` FOREIGN KEY (`ecommerce_product_id`) REFERENCES `phppos_ecommerce_products` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_items_serial_numbers` */

DROP TABLE IF EXISTS `phppos_items_serial_numbers`;

CREATE TABLE `phppos_items_serial_numbers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `serial_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `phppos_items_serial_numbers_ibfk_1` (`item_id`),
  CONSTRAINT `phppos_items_serial_numbers_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_items_suppliers` */

DROP TABLE IF EXISTS `phppos_items_suppliers`;

CREATE TABLE `phppos_items_suppliers` (
  `item_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_items_tags` */

DROP TABLE IF EXISTS `phppos_items_tags`;

CREATE TABLE `phppos_items_tags` (
  `item_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`item_id`,`tag_id`),
  KEY `phppos_items_tags_ibfk_2` (`tag_id`),
  CONSTRAINT `phppos_items_tags_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_items_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_items_taxes` */

DROP TABLE IF EXISTS `phppos_items_taxes`;

CREATE TABLE `phppos_items_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`item_id`,`name`,`percent`),
  CONSTRAINT `phppos_items_taxes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_items_tier_prices` */

DROP TABLE IF EXISTS `phppos_items_tier_prices`;

CREATE TABLE `phppos_items_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT '0.0000000000',
  `percent_off` decimal(15,3) DEFAULT NULL,
  `cost_plus_percent` decimal(15,3) DEFAULT NULL,
  PRIMARY KEY (`tier_id`,`item_id`),
  KEY `phppos_items_tier_prices_ibfk_2` (`item_id`),
  CONSTRAINT `phppos_items_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_items_tier_prices_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_location_item_kits` */

DROP TABLE IF EXISTS `phppos_location_item_kits`;

CREATE TABLE `phppos_location_item_kits` (
  `location_id` int(11) NOT NULL,
  `item_kit_id` int(11) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_id`,`item_kit_id`),
  KEY `phppos_location_item_kits_ibfk_2` (`item_kit_id`),
  CONSTRAINT `phppos_location_item_kits_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_item_kits_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_location_item_kits_taxes` */

DROP TABLE IF EXISTS `phppos_location_item_kits_taxes`;

CREATE TABLE `phppos_location_item_kits_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(16,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`location_id`,`item_kit_id`,`name`,`percent`),
  KEY `phppos_location_item_kits_taxes_ibfk_2` (`item_kit_id`),
  CONSTRAINT `phppos_location_item_kits_taxes_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_item_kits_taxes_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_location_item_kits_tier_prices` */

DROP TABLE IF EXISTS `phppos_location_item_kits_tier_prices`;

CREATE TABLE `phppos_location_item_kits_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT '0.0000000000',
  `percent_off` decimal(15,3) DEFAULT NULL,
  `cost_plus_percent` decimal(15,3) DEFAULT NULL,
  PRIMARY KEY (`tier_id`,`item_kit_id`,`location_id`),
  KEY `phppos_location_item_kits_tier_prices_ibfk_2` (`location_id`),
  KEY `phppos_location_item_kits_tier_prices_ibfk_3` (`item_kit_id`),
  CONSTRAINT `phppos_location_item_kits_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_item_kits_tier_prices_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_item_kits_tier_prices_ibfk_3` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_location_items` */

DROP TABLE IF EXISTS `phppos_location_items`;

CREATE TABLE `phppos_location_items` (
  `location_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cost_price` decimal(23,10) DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `promo_price` decimal(23,10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `quantity` decimal(23,10) DEFAULT '0.0000000000',
  `reorder_level` decimal(23,10) DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_id`,`item_id`),
  KEY `phppos_location_items_ibfk_2` (`item_id`),
  KEY `quantity` (`quantity`),
  CONSTRAINT `phppos_location_items_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_location_items_taxes` */

DROP TABLE IF EXISTS `phppos_location_items_taxes`;

CREATE TABLE `phppos_location_items_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `item_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(16,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`location_id`,`item_id`,`name`,`percent`),
  KEY `phppos_location_items_taxes_ibfk_2` (`item_id`),
  CONSTRAINT `phppos_location_items_taxes_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_location_items_tier_prices` */

DROP TABLE IF EXISTS `phppos_location_items_tier_prices`;

CREATE TABLE `phppos_location_items_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT '0.0000000000',
  `percent_off` decimal(15,3) DEFAULT NULL,
  `cost_plus_percent` decimal(15,3) DEFAULT NULL,
  PRIMARY KEY (`tier_id`,`item_id`,`location_id`),
  KEY `phppos_location_items_tier_prices_ibfk_2` (`location_id`),
  KEY `phppos_location_items_tier_prices_ibfk_3` (`item_id`),
  CONSTRAINT `phppos_location_items_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_items_tier_prices_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_items_tier_prices_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_locations` */

DROP TABLE IF EXISTS `phppos_locations`;

CREATE TABLE `phppos_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci,
  `company` text COLLATE utf8_unicode_ci,
  `website` text COLLATE utf8_unicode_ci,
  `company_logo` int(10) DEFAULT NULL,
  `address` text COLLATE utf8_unicode_ci,
  `phone` text COLLATE utf8_unicode_ci,
  `fax` text COLLATE utf8_unicode_ci,
  `email` text COLLATE utf8_unicode_ci,
  `color` text COLLATE utf8_unicode_ci,
  `return_policy` text COLLATE utf8_unicode_ci,
  `receive_stock_alert` text COLLATE utf8_unicode_ci,
  `stock_alert_email` text COLLATE utf8_unicode_ci,
  `timezone` text COLLATE utf8_unicode_ci,
  `mailchimp_api_key` text COLLATE utf8_unicode_ci,
  `enable_credit_card_processing` text COLLATE utf8_unicode_ci,
  `credit_card_processor` text COLLATE utf8_unicode_ci,
  `hosted_checkout_merchant_id` text COLLATE utf8_unicode_ci,
  `hosted_checkout_merchant_password` text COLLATE utf8_unicode_ci,
  `emv_merchant_id` text COLLATE utf8_unicode_ci,
  `net_e_pay_server` text COLLATE utf8_unicode_ci,
  `listener_port` text COLLATE utf8_unicode_ci,
  `com_port` text COLLATE utf8_unicode_ci,
  `stripe_public` text COLLATE utf8_unicode_ci,
  `stripe_private` text COLLATE utf8_unicode_ci,
  `stripe_currency_code` text COLLATE utf8_unicode_ci,
  `braintree_merchant_id` text COLLATE utf8_unicode_ci,
  `braintree_public_key` text COLLATE utf8_unicode_ci,
  `braintree_private_key` text COLLATE utf8_unicode_ci,
  `default_tax_1_rate` text COLLATE utf8_unicode_ci,
  `default_tax_1_name` text COLLATE utf8_unicode_ci,
  `default_tax_2_rate` text COLLATE utf8_unicode_ci,
  `default_tax_2_name` text COLLATE utf8_unicode_ci,
  `default_tax_2_cumulative` text COLLATE utf8_unicode_ci,
  `default_tax_3_rate` text COLLATE utf8_unicode_ci,
  `default_tax_3_name` text COLLATE utf8_unicode_ci,
  `default_tax_4_rate` text COLLATE utf8_unicode_ci,
  `default_tax_4_name` text COLLATE utf8_unicode_ci,
  `default_tax_5_rate` text COLLATE utf8_unicode_ci,
  `default_tax_5_name` text COLLATE utf8_unicode_ci,
  `deleted` int(1) DEFAULT '0',
  `secure_device_override_emv` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `secure_device_override_non_emv` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`location_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_locations_ibfk_1` (`company_logo`),
  KEY `name` (`name`(255)),
  KEY `address` (`address`(255)),
  KEY `phone` (`phone`(255)),
  KEY `email` (`email`(255)),
  CONSTRAINT `phppos_locations_ibfk_1` FOREIGN KEY (`company_logo`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_manufacturers` */

DROP TABLE IF EXISTS `phppos_manufacturers`;

CREATE TABLE `phppos_manufacturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_message_receiver` */

DROP TABLE IF EXISTS `phppos_message_receiver`;

CREATE TABLE `phppos_message_receiver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_read` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phppos_message_receiver_ibfk_2` (`receiver_id`),
  KEY `phppos_message_receiver_key_1` (`message_id`,`receiver_id`),
  CONSTRAINT `phppos_message_receiver_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `phppos_messages` (`id`),
  CONSTRAINT `phppos_message_receiver_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_messages` */

DROP TABLE IF EXISTS `phppos_messages`;

CREATE TABLE `phppos_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `sender_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phppos_messages_ibfk_1` (`sender_id`),
  KEY `phppos_messages_key_1` (`deleted`,`created_at`,`id`),
  CONSTRAINT `phppos_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_migrations` */

DROP TABLE IF EXISTS `phppos_migrations`;

CREATE TABLE `phppos_migrations` (
  `version` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_modules` */

DROP TABLE IF EXISTS `phppos_modules`;

CREATE TABLE `phppos_modules` (
  `name_lang_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `desc_lang_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sort` int(10) NOT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `enable` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `desc_lang_key` (`desc_lang_key`),
  UNIQUE KEY `name_lang_key` (`name_lang_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_modules_actions` */

DROP TABLE IF EXISTS `phppos_modules_actions`;

CREATE TABLE `phppos_modules_actions` (
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `action_name_key` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`action_id`,`module_id`),
  KEY `phppos_modules_actions_ibfk_1` (`module_id`),
  CONSTRAINT `phppos_modules_actions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_mrp_items_boms` */

DROP TABLE IF EXISTS `phppos_mrp_items_boms`;

CREATE TABLE `phppos_mrp_items_boms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_items_boms_archive` */

DROP TABLE IF EXISTS `phppos_mrp_items_boms_archive`;

CREATE TABLE `phppos_mrp_items_boms_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bom_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `total_rate` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_items_boms_raw` */

DROP TABLE IF EXISTS `phppos_mrp_items_boms_raw`;

CREATE TABLE `phppos_mrp_items_boms_raw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bom_id` int(11) DEFAULT NULL,
  `semi_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `rate_of_qty` float DEFAULT NULL,
  `rate_of_unit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_items_semi` */

DROP TABLE IF EXISTS `phppos_mrp_items_semi`;

CREATE TABLE `phppos_mrp_items_semi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `semi_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_materials_plans` */

DROP TABLE IF EXISTS `phppos_mrp_materials_plans`;

CREATE TABLE `phppos_mrp_materials_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `month` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `detail` text,
  `note` text,
  `employee_id` int(11) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_production_planning_detail` */

DROP TABLE IF EXISTS `phppos_mrp_production_planning_detail`;

CREATE TABLE `phppos_mrp_production_planning_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `month` varchar(255) DEFAULT NULL,
  `type` varchar(225) DEFAULT NULL,
  `detail` text,
  `note` text,
  `employee_id` int(11) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_sales_daily` */

DROP TABLE IF EXISTS `phppos_mrp_sales_daily`;

CREATE TABLE `phppos_mrp_sales_daily` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_monthly_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=301 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_sales_forecast_items` */

DROP TABLE IF EXISTS `phppos_mrp_sales_forecast_items`;

CREATE TABLE `phppos_mrp_sales_forecast_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_monthly_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `qty` float DEFAULT NULL,
  `port` varchar(255) DEFAULT NULL,
  `export` varchar(255) DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_sales_items` */

DROP TABLE IF EXISTS `phppos_mrp_sales_items`;

CREATE TABLE `phppos_mrp_sales_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_daily_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `qty` float DEFAULT NULL,
  `port` varchar(255) DEFAULT NULL,
  `export` varchar(255) DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=265 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_sales_monthly` */

DROP TABLE IF EXISTS `phppos_mrp_sales_monthly`;

CREATE TABLE `phppos_mrp_sales_monthly` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `month` varchar(255) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `po_monthly_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_mrp_sales_view` */

DROP TABLE IF EXISTS `phppos_mrp_sales_view`;

CREATE TABLE `phppos_mrp_sales_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_monthly_id` int(11) DEFAULT NULL,
  `data` longtext,
  `merge_cell` longtext,
  `cell` longtext,
  `end_row_body` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_people` */

DROP TABLE IF EXISTS `phppos_people`;

CREATE TABLE `phppos_people` (
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` text COLLATE utf8_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  `image_id` int(10) DEFAULT NULL,
  `person_id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`person_id`),
  KEY `phppos_people_ibfk_1` (`image_id`),
  KEY `first_name` (`first_name`),
  KEY `last_name` (`last_name`),
  KEY `email` (`email`),
  KEY `phone_number` (`phone_number`),
  KEY `full_name` (`full_name`(255)),
  CONSTRAINT `phppos_people_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_permissions` */

DROP TABLE IF EXISTS `phppos_permissions`;

CREATE TABLE `phppos_permissions` (
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `person_id` int(10) NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`),
  KEY `person_id` (`person_id`),
  CONSTRAINT `phppos_permissions_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_permissions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_permissions_actions` */

DROP TABLE IF EXISTS `phppos_permissions_actions`;

CREATE TABLE `phppos_permissions_actions` (
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `person_id` int(11) NOT NULL,
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`,`action_id`),
  KEY `phppos_permissions_actions_ibfk_2` (`person_id`),
  KEY `phppos_permissions_actions_ibfk_3` (`action_id`),
  CONSTRAINT `phppos_permissions_actions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_actions_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_permissions_actions_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `phppos_modules_actions` (`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_price_rules` */

DROP TABLE IF EXISTS `phppos_price_rules`;

CREATE TABLE `phppos_price_rules` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `added_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `active` int(1) NOT NULL DEFAULT '1',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `items_to_buy` decimal(23,10) DEFAULT NULL,
  `items_to_get` decimal(23,10) DEFAULT NULL,
  `percent_off` decimal(23,10) DEFAULT NULL,
  `fixed_off` decimal(23,10) DEFAULT NULL,
  `spend_amount` decimal(23,10) DEFAULT NULL,
  `num_times_to_apply` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_price_rules_categories` */

DROP TABLE IF EXISTS `phppos_price_rules_categories`;

CREATE TABLE `phppos_price_rules_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_categories_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_categories_ibfk_2` (`category_id`),
  CONSTRAINT `phppos_price_rules_categories_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_price_rules_item_kits` */

DROP TABLE IF EXISTS `phppos_price_rules_item_kits`;

CREATE TABLE `phppos_price_rules_item_kits` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_item_kits_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_item_kits_ibfk_2` (`item_kit_id`),
  CONSTRAINT `phppos_price_rules_item_kits_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_item_kits_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_price_rules_items` */

DROP TABLE IF EXISTS `phppos_price_rules_items`;

CREATE TABLE `phppos_price_rules_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_items_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_items_ibfk_2` (`item_id`),
  CONSTRAINT `phppos_price_rules_items_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_price_rules_price_breaks` */

DROP TABLE IF EXISTS `phppos_price_rules_price_breaks`;

CREATE TABLE `phppos_price_rules_price_breaks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `item_qty_to_buy` decimal(23,10) DEFAULT NULL,
  `discount_per_unit_fixed` decimal(23,10) DEFAULT NULL,
  `discount_per_unit_percent` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_custom_ibfk_1` (`rule_id`),
  CONSTRAINT `phppos_price_rules_price_breaks_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_price_rules_tags` */

DROP TABLE IF EXISTS `phppos_price_rules_tags`;

CREATE TABLE `phppos_price_rules_tags` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `tag_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_tags_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_tags_ibfk_2` (`tag_id`),
  CONSTRAINT `phppos_price_rules_tags_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_price_tiers` */

DROP TABLE IF EXISTS `phppos_price_tiers`;

CREATE TABLE `phppos_price_tiers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `default_percent_off` decimal(15,3) DEFAULT NULL,
  `default_cost_plus_percent` decimal(15,3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_purchase_order_items` */

DROP TABLE IF EXISTS `phppos_purchase_order_items`;

CREATE TABLE `phppos_purchase_order_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int(10) unsigned DEFAULT NULL,
  `item_id` int(10) unsigned DEFAULT NULL,
  `quantity` smallint(4) unsigned DEFAULT NULL,
  `month` varchar(25) DEFAULT NULL,
  `comment` varchar(525) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

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
  `created_at` int(10) unsigned DEFAULT NULL,
  `updated_at` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_receivings` */

DROP TABLE IF EXISTS `phppos_receivings`;

CREATE TABLE `phppos_receivings` (
  `receiving_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `supplier_id` int(10) DEFAULT NULL,
  `employee_id` int(10) NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `receiving_id` int(10) NOT NULL AUTO_INCREMENT,
  `payment_type` text COLLATE utf8_unicode_ci,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `deleted_by` int(10) DEFAULT NULL,
  `suspended` int(1) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL,
  `transfer_to_location_id` int(11) DEFAULT NULL,
  `deleted_taxes` text COLLATE utf8_unicode_ci,
  `is_po` int(1) NOT NULL DEFAULT '0',
  `store_account_payment` int(1) NOT NULL DEFAULT '0',
  `total_quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total_quantity_received` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  PRIMARY KEY (`receiving_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `employee_id` (`employee_id`),
  KEY `deleted` (`deleted`),
  KEY `location_id` (`location_id`),
  KEY `transfer_to_location_id` (`transfer_to_location_id`),
  KEY `recv_search` (`location_id`,`deleted`,`receiving_time`,`suspended`,`store_account_payment`,`total_quantity_purchased`),
  CONSTRAINT `phppos_receivings_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_receivings_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`),
  CONSTRAINT `phppos_receivings_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_receivings_ibfk_4` FOREIGN KEY (`transfer_to_location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_receivings_items` */

DROP TABLE IF EXISTS `phppos_receivings_items`;

CREATE TABLE `phppos_receivings_items` (
  `receiving_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serialnumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `quantity_received` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `item_cost_price` decimal(23,10) NOT NULL,
  `item_unit_price` decimal(23,10) NOT NULL,
  `discount_percent` decimal(15,3) NOT NULL DEFAULT '0.000',
  `expire_date` date DEFAULT NULL,
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  PRIMARY KEY (`receiving_id`,`item_id`,`line`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `phppos_receivings_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_receivings_items_ibfk_2` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_receivings_items_taxes` */

DROP TABLE IF EXISTS `phppos_receivings_items_taxes`;

CREATE TABLE `phppos_receivings_items_taxes` (
  `receiving_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`receiving_id`,`item_id`,`line`,`name`,`percent`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `phppos_receivings_items_taxes_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_receivings_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_receivings_payments` */

DROP TABLE IF EXISTS `phppos_receivings_payments`;

CREATE TABLE `phppos_receivings_payments` (
  `payment_id` int(10) NOT NULL AUTO_INCREMENT,
  `receiving_id` int(10) NOT NULL,
  `payment_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_amount` decimal(23,10) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `receiving_id` (`receiving_id`),
  KEY `payment_date` (`payment_date`),
  CONSTRAINT `phppos_receivings_payments_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_register_currency_denominations` */

DROP TABLE IF EXISTS `phppos_register_currency_denominations`;

CREATE TABLE `phppos_register_currency_denominations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` decimal(23,10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_register_log` */

DROP TABLE IF EXISTS `phppos_register_log`;

CREATE TABLE `phppos_register_log` (
  `register_log_id` int(10) NOT NULL AUTO_INCREMENT,
  `employee_id_open` int(10) NOT NULL,
  `employee_id_close` int(11) DEFAULT NULL,
  `register_id` int(11) DEFAULT NULL,
  `shift_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shift_end` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `open_amount` decimal(23,10) NOT NULL,
  `close_amount` decimal(23,10) NOT NULL,
  `cash_sales_amount` decimal(23,10) NOT NULL,
  `total_cash_additions` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total_cash_subtractions` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`register_log_id`),
  KEY `phppos_register_log_ibfk_1` (`employee_id_open`),
  KEY `phppos_register_log_ibfk_2` (`register_id`),
  KEY `phppos_register_log_ibfk_3` (`employee_id_close`),
  CONSTRAINT `phppos_register_log_ibfk_1` FOREIGN KEY (`employee_id_open`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_register_log_ibfk_2` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`),
  CONSTRAINT `phppos_register_log_ibfk_3` FOREIGN KEY (`employee_id_close`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_register_log_audit` */

DROP TABLE IF EXISTS `phppos_register_log_audit`;

CREATE TABLE `phppos_register_log_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_log_id` int(10) NOT NULL,
  `employee_id` int(10) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `note` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `register_log_audit_ibfk_1` (`register_log_id`),
  KEY `register_log_audit_ibfk_2` (`employee_id`),
  CONSTRAINT `register_log_audit_ibfk_1` FOREIGN KEY (`register_log_id`) REFERENCES `phppos_register_log` (`register_log_id`),
  CONSTRAINT `register_log_audit_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_registers` */

DROP TABLE IF EXISTS `phppos_registers`;

CREATE TABLE `phppos_registers` (
  `register_id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `iptran_device_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emv_terminal_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`register_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_registers_ibfk_1` (`location_id`),
  CONSTRAINT `phppos_registers_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_registers_cart` */

DROP TABLE IF EXISTS `phppos_registers_cart`;

CREATE TABLE `phppos_registers_cart` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `register_id` int(11) NOT NULL,
  `data` longblob NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `register_id` (`register_id`),
  CONSTRAINT `phppos_registers_cart_ibfk_1` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_sales` */

DROP TABLE IF EXISTS `phppos_sales`;

CREATE TABLE `phppos_sales` (
  `sale_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_id` int(10) DEFAULT NULL,
  `employee_id` int(10) NOT NULL DEFAULT '0',
  `sold_by_employee_id` int(10) DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `discount_reason` text COLLATE utf8_unicode_ci NOT NULL,
  `show_comment_on_receipt` int(1) NOT NULL DEFAULT '0',
  `sale_id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) DEFAULT NULL,
  `rule_discount` decimal(23,10) DEFAULT NULL,
  `payment_type` text COLLATE utf8_unicode_ci,
  `cc_ref_no` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `auth_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `deleted_by` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `suspended` int(1) NOT NULL DEFAULT '0',
  `store_account_payment` int(1) NOT NULL DEFAULT '0',
  `was_layaway` int(1) NOT NULL DEFAULT '0',
  `was_estimate` int(1) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL,
  `register_id` int(11) DEFAULT NULL,
  `tier_id` int(10) DEFAULT NULL,
  `points_used` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `points_gained` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `did_redeem_discount` int(1) NOT NULL DEFAULT '0',
  `signature_image_id` int(10) DEFAULT NULL,
  `deleted_taxes` text COLLATE utf8_unicode_ci,
  `total_quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `aaatex_qb_imported` int(1) DEFAULT '0',
  PRIMARY KEY (`sale_id`),
  KEY `customer_id` (`customer_id`),
  KEY `employee_id` (`employee_id`),
  KEY `deleted` (`deleted`),
  KEY `location_id` (`location_id`),
  KEY `phppos_sales_ibfk_4` (`deleted_by`),
  KEY `phppos_sales_ibfk_5` (`tier_id`),
  KEY `phppos_sales_ibfk_7` (`register_id`),
  KEY `phppos_sales_ibfk_6` (`sold_by_employee_id`),
  KEY `phppos_sales_ibfk_8` (`signature_image_id`),
  KEY `was_layaway` (`was_layaway`),
  KEY `was_estimate` (`was_estimate`),
  KEY `phppos_sales_ibfk_9` (`rule_id`),
  KEY `sales_search` (`location_id`,`deleted`,`sale_time`,`suspended`,`store_account_payment`,`total_quantity_purchased`),
  CONSTRAINT `phppos_sales_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`),
  CONSTRAINT `phppos_sales_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_sales_ibfk_4` FOREIGN KEY (`deleted_by`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_sales_ibfk_5` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`),
  CONSTRAINT `phppos_sales_ibfk_6` FOREIGN KEY (`sold_by_employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_sales_ibfk_7` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`),
  CONSTRAINT `phppos_sales_ibfk_8` FOREIGN KEY (`signature_image_id`) REFERENCES `phppos_app_files` (`file_id`),
  CONSTRAINT `phppos_sales_ibfk_9` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_sales_item_kits` */

DROP TABLE IF EXISTS `phppos_sales_item_kits`;

CREATE TABLE `phppos_sales_item_kits` (
  `sale_id` int(10) NOT NULL DEFAULT '0',
  `item_kit_id` int(10) NOT NULL DEFAULT '0',
  `rule_id` int(10) DEFAULT NULL,
  `rule_discount` decimal(23,10) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `item_kit_cost_price` decimal(23,10) NOT NULL,
  `item_kit_unit_price` decimal(23,10) NOT NULL,
  `regular_item_kit_unit_price_at_time_of_sale` decimal(23,10) DEFAULT NULL,
  `discount_percent` decimal(15,3) NOT NULL DEFAULT '0.000',
  `commission` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `is_bogo` tinyint(1) NOT NULL DEFAULT '0',
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  PRIMARY KEY (`sale_id`,`item_kit_id`,`line`),
  KEY `item_kit_id` (`item_kit_id`),
  KEY `phppos_sales_item_kits_ibfk_3` (`rule_id`),
  CONSTRAINT `phppos_sales_item_kits_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_sales_item_kits_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_item_kits_ibfk_3` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_sales_item_kits_taxes` */

DROP TABLE IF EXISTS `phppos_sales_item_kits_taxes`;

CREATE TABLE `phppos_sales_item_kits_taxes` (
  `sale_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sale_id`,`item_kit_id`,`line`,`name`,`percent`),
  KEY `item_id` (`item_kit_id`),
  CONSTRAINT `phppos_sales_item_kits_taxes_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales_item_kits` (`sale_id`),
  CONSTRAINT `phppos_sales_item_kits_taxes_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_sales_items` */

DROP TABLE IF EXISTS `phppos_sales_items`;

CREATE TABLE `phppos_sales_items` (
  `sale_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `rule_id` int(10) DEFAULT NULL,
  `rule_discount` decimal(23,10) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serialnumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `item_cost_price` decimal(23,10) NOT NULL,
  `item_unit_price` decimal(23,10) NOT NULL,
  `regular_item_unit_price_at_time_of_sale` decimal(23,10) DEFAULT NULL,
  `discount_percent` decimal(15,3) NOT NULL DEFAULT '0.000',
  `commission` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `is_bogo` tinyint(1) NOT NULL DEFAULT '0',
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  PRIMARY KEY (`sale_id`,`item_id`,`line`),
  KEY `item_id` (`item_id`),
  KEY `phppos_sales_items_ibfk_3` (`rule_id`),
  CONSTRAINT `phppos_sales_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_sales_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_items_ibfk_3` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_sales_items_taxes` */

DROP TABLE IF EXISTS `phppos_sales_items_taxes`;

CREATE TABLE `phppos_sales_items_taxes` (
  `sale_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sale_id`,`item_id`,`line`,`name`,`percent`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `phppos_sales_items_taxes_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales_items` (`sale_id`),
  CONSTRAINT `phppos_sales_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_sales_payments` */

DROP TABLE IF EXISTS `phppos_sales_payments`;

CREATE TABLE `phppos_sales_payments` (
  `payment_id` int(10) NOT NULL AUTO_INCREMENT,
  `sale_id` int(10) NOT NULL,
  `payment_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_amount` decimal(23,10) NOT NULL,
  `auth_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `ref_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `cc_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `acq_ref_data` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `process_data` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `entry_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `aid` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `tvr` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `iad` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `tsi` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `arc` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `cvm` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `tran_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `application_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `truncated_card` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `card_issuer` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `payment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ebt_auth_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `ebt_voucher_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`payment_id`),
  KEY `sale_id` (`sale_id`),
  KEY `payment_date` (`payment_date`),
  CONSTRAINT `phppos_sales_payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_sessions` */

DROP TABLE IF EXISTS `phppos_sessions`;

CREATE TABLE `phppos_sessions` (
  `id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `data` longblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_stock` */

DROP TABLE IF EXISTS `phppos_stock`;

CREATE TABLE `phppos_stock` (
  `stock_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT 'In, Out',
  `receipt_title` varchar(525) DEFAULT NULL,
  `employee_id` int(10) unsigned DEFAULT NULL,
  `supplier_id` int(10) unsigned DEFAULT NULL,
  `comment` varchar(525) DEFAULT NULL,
  `total` double DEFAULT NULL,
  `sub_total` double DEFAULT NULL,
  `quantity` int(4) DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `deleted` tinyint(1) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  `created_at` int(10) DEFAULT NULL,
  `updated_at` int(10) DEFAULT NULL,
  PRIMARY KEY (`stock_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_stock_items` */

DROP TABLE IF EXISTS `phppos_stock_items`;

CREATE TABLE `phppos_stock_items` (
  `item_id` int(11) unsigned NOT NULL,
  `line` smallint(4) unsigned DEFAULT NULL,
  `stock_id` int(11) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `product_id` varchar(25) DEFAULT NULL,
  `quantity` int(4) unsigned DEFAULT NULL,
  `quantity_received` int(4) unsigned DEFAULT NULL,
  `price` double DEFAULT NULL,
  `default_cost_price` double DEFAULT NULL,
  `selling_price` double DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `phppos_store_accounts` */

DROP TABLE IF EXISTS `phppos_store_accounts`;

CREATE TABLE `phppos_store_accounts` (
  `sno` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `transaction_amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sno`),
  KEY `phppos_store_accounts_ibfk_1` (`sale_id`),
  KEY `phppos_store_accounts_ibfk_2` (`customer_id`),
  CONSTRAINT `phppos_store_accounts_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_store_accounts_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_store_accounts_paid_sales` */

DROP TABLE IF EXISTS `phppos_store_accounts_paid_sales`;

CREATE TABLE `phppos_store_accounts_paid_sales` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `store_account_payment_sale_id` int(10) DEFAULT NULL,
  `sale_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_store_accounts_sales_ibfk_1` (`sale_id`),
  KEY `phppos_store_accounts_sales_ibfk_2` (`store_account_payment_sale_id`),
  CONSTRAINT `phppos_store_accounts_sales_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_store_accounts_sales_ibfk_2` FOREIGN KEY (`store_account_payment_sale_id`) REFERENCES `phppos_sales` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_supplier_store_accounts` */

DROP TABLE IF EXISTS `phppos_supplier_store_accounts`;

CREATE TABLE `phppos_supplier_store_accounts` (
  `sno` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `receiving_id` int(11) DEFAULT NULL,
  `transaction_amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`sno`),
  KEY `phppos_supplier_store_accounts_ibfk_1` (`receiving_id`),
  KEY `phppos_supplier_store_accounts_ibfk_2` (`supplier_id`),
  CONSTRAINT `phppos_supplier_store_accounts_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_supplier_store_accounts_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_supplier_store_accounts_paid_receivings` */

DROP TABLE IF EXISTS `phppos_supplier_store_accounts_paid_receivings`;

CREATE TABLE `phppos_supplier_store_accounts_paid_receivings` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `store_account_payment_receiving_id` int(10) DEFAULT NULL,
  `receiving_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_supplier_store_accounts_paid_receivings_ibfk_1` (`receiving_id`),
  KEY `phppos_supplier_store_accounts_paid_receivings_ibfk_2` (`store_account_payment_receiving_id`),
  CONSTRAINT `phppos_supplier_store_accounts_paid_receivings_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_supplier_store_accounts_paid_receivings_ibfk_2` FOREIGN KEY (`store_account_payment_receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_suppliers` */

DROP TABLE IF EXISTS `phppos_suppliers`;

CREATE TABLE `phppos_suppliers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `person_id` int(10) NOT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `person_id` (`person_id`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `phppos_suppliers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_people` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_suppliers_taxes` */

DROP TABLE IF EXISTS `phppos_suppliers_taxes`;

CREATE TABLE `phppos_suppliers_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`supplier_id`,`name`,`percent`),
  CONSTRAINT `phppos_suppliers_taxes_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_tags` */

DROP TABLE IF EXISTS `phppos_tags`;

CREATE TABLE `phppos_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_name` (`name`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `phppos_units` */

DROP TABLE IF EXISTS `phppos_units`;

CREATE TABLE `phppos_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
