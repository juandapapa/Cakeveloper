/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 10.4.7-MariaDB : Database - paketin_db
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `cakeveloper_db`;

/*Table structure for table `cake` */

DROP TABLE IF EXISTS `cake`;

CREATE TABLE `cake` (
  `code` char(8) NOT NULL,
  `name` char(40) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `price` decimal(4,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `cake` */

insert  into `cake`(`code`,`name`,`description`,`price`) values 
('BRNIES00','Brownies (Original)',NULL,2.00),
('BRNIES01','Brownies (Cheese)',NULL,2.80),
('BRNIES02','Brownies (Peanuts)',NULL,2.70),
('CNDLS00','Cendols (Original)',NULL,1.99),
('CNDLS01','Cendols (Large)',NULL,2.49);

/*Table structure for table `order` */

DROP TABLE IF EXISTS `order`;

CREATE TABLE `order` (
  `no` char(20) NOT NULL,
  `customer_name` varchar(40) NOT NULL,
  `address` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `quantity` int(2) NOT NULL DEFAULT 0,
  `cake` char(8) NOT NULL,
  `canceled` tinyint(1) NOT NULL DEFAULT 0,
  `placed_at` datetime NOT NULL,
  PRIMARY KEY (`no`),
  KEY `cake` (`cake`),
  CONSTRAINT `order_ibfk_1` FOREIGN KEY (`cake`) REFERENCES `cake` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `order` */

insert  into `order`(`no`,`customer_name`,`address`,`phone`,`quantity`,`cake`,`canceled`,`placed_at`) values 
('e3ddc231e5b48634a1b1','Norman','Cologne','085262211555',2,'BRNIES02',0,'2019-10-24 11:49:24'),
('e3ddc231e5b48634asss','Semar','Semarang','085262211222',1,'BRNIES00',0,'2019-10-24 16:48:09'),
('e3ddgh31e5b48634a1b1','Jean','Atlanta','085262211444',4,'CNDLS01',0,'2019-10-24 11:36:51'),
('sfddc231e5b48634a1b1','Ucok','Tarutung','085262211333',4,'CNDLS01',0,'2019-10-24 11:36:14');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
