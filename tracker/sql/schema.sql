-- MySQL dump 10.11
--
-- Host: localhost    Database: tracker
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny5

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `handles`
--

DROP TABLE IF EXISTS `handles`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `handles` (
  `id` int(20) NOT NULL auto_increment,
  `handle` varchar(15) NOT NULL,
  `last_text_id` bigint(20) default NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `text_idx` (`last_text_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `handles`
--

LOCK TABLES `handles` WRITE;
/*!40000 ALTER TABLE `handles` DISABLE KEYS */;
INSERT INTO `handles` VALUES (1,'wtctest',211227821119516672,1),(2,'NASA',NULL,1);
/*!40000 ALTER TABLE `handles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `texts`
--

DROP TABLE IF EXISTS `texts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `texts` (
  `id` bigint(20) NOT NULL auto_increment,
  `handle_id` int(20) NOT NULL,
  `text` varchar(140) NOT NULL,
  KEY `id_idx` (`id`),
  KEY `handles_fk1` (`handle_id`),
  CONSTRAINT `texts_ibfk_1` FOREIGN KEY (`handle_id`) REFERENCES `handles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=211227794192080898 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `texts`
--

LOCK TABLES `texts` WRITE;
/*!40000 ALTER TABLE `texts` DISABLE KEYS */;
INSERT INTO `texts` VALUES (211227821119516672,1,'Creativity7'),(211227794192080897,1,'Creativity6'),(211227764118929408,1,'Creativity5'),(211227738554638336,1,'Creativity4'),(211227713103609856,1,'Creativity3'),(211227674524393472,1,'Creativity2'),(211227651002740736,1,'Creativity1');
/*!40000 ALTER TABLE `texts` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-06-08 23:41:45
