-- MySQL dump 10.13  Distrib 8.0.13, for Win64 (x86_64)
--
-- ------------------------------------------------------
-- Server version	5.7.14-google

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 SET NAMES utf8 ;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mmr_type_ids`
--

DROP TABLE IF EXISTS `mmr_type_ids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `mmr_type_ids` (
  `mmr_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`mmr_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100006 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mmr_type_ids`
--

LOCK TABLES `mmr_type_ids` WRITE;
/*!40000 ALTER TABLE `mmr_type_ids` DISABLE KEYS */;
INSERT INTO `mmr_type_ids` VALUES (1,'Abathur'),(2,'Alarak'),(3,'Anub\'arak'),(4,'Artanis'),(5,'Arthas'),(6,'Auriel'),(7,'Azmodan'),(8,'Brightwing'),(9,'Cassia'),(10,'Chen'),(11,'Cho'),(12,'Chromie'),(13,'D.Va'),(14,'Dehaka'),(15,'Diablo'),(16,'E.T.C.'),(17,'Falstad'),(18,'Gall'),(19,'Garrosh'),(20,'Gazlowe'),(21,'Genji'),(22,'Greymane'),(23,'Gul\'dan'),(24,'Illidan'),(25,'Jaina'),(26,'Johanna'),(27,'Kael\'thas'),(28,'Kerrigan'),(29,'Kharazim'),(30,'Leoric'),(31,'Li Li'),(32,'Li-Ming'),(33,'Lt. Morales'),(34,'Lúcio'),(35,'Lunara'),(36,'Malfurion'),(37,'Malthael'),(38,'Medivh'),(39,'Muradin'),(40,'Murky'),(41,'Nazeebo'),(42,'Nova'),(43,'Probius'),(44,'Ragnaros'),(45,'Raynor'),(46,'Rehgar'),(47,'Rexxar'),(48,'Samuro'),(49,'Sgt. Hammer'),(50,'Sonya'),(51,'Stitches'),(52,'Stukov'),(53,'Sylvanas'),(54,'Tassadar'),(55,'The Butcher'),(56,'The Lost Vikings'),(57,'Thrall'),(58,'Tracer'),(59,'Tychus'),(60,'Tyrael'),(61,'Tyrande'),(62,'Uther'),(63,'Valeera'),(64,'Valla'),(65,'Varian'),(66,'Xul'),(67,'Zagara'),(68,'Zarya'),(69,'Zeratul'),(70,'Zul\'jin'),(71,'Kel\'Thuzad'),(72,'Ana'),(73,'Junkrat'),(74,'Alexstrasza'),(75,'Hanzo'),(77,'Blaze'),(78,'Maiev'),(79,'Fenix'),(80,'Deckard'),(81,'Yrel'),(82,'Whitemane'),(83,'Mephisto'),(84,'Mal\'Ganis'),(85,'Orphea'),(86,'Imperius'),(87,'Anduin'),(88,'Qhira'), (89, 'Deathwing'),(10000,'player'),(100000,'Support'),(100001,'Melee Assassin'),(100002,'Tank'),(100003,'Bruiser'),(100004,'Healer'),(100005,'Ranged Assassin');
/*!40000 ALTER TABLE `mmr_type_ids` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-10-10  9:20:34
