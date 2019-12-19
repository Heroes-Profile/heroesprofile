-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- ------------------------------------------------------
-- Server version	5.7.14-google

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
-- Table structure for table `global_hero_stats_bans`
--

DROP TABLE IF EXISTS `global_hero_stats_bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_hero_stats_bans` (
  `game_version` varchar(45) NOT NULL,
  `game_type` tinyint(4) NOT NULL,
  `league_tier` tinyint(4) NOT NULL,
  `hero_league_tier` tinyint(4) NOT NULL DEFAULT '0',
  `role_league_tier` tinyint(4) NOT NULL DEFAULT '0',
  `game_map` tinyint(4) NOT NULL,
  `hero_level` int(10) NOT NULL,
  `hero` tinyint(4) NOT NULL,
  `bans` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`game_version`,`game_type`,`league_tier`,`hero_league_tier`,`role_league_tier`,`game_map`,`hero_level`,`hero`),
  KEY `Index_Bans` (`game_version`,`game_type`,`league_tier`,`hero_league_tier`,`role_league_tier`,`game_map`,`hero_level`,`hero`,`bans`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `global_hero_stats_bans`
--

LOCK TABLES `global_hero_stats_bans` WRITE;
/*!40000 ALTER TABLE `global_hero_stats_bans` DISABLE KEYS */;
INSERT INTO `global_hero_stats_bans` VALUES ('2.49.1.77692',1,1,1,1,1,1,0,6),('2.49.1.77692',1,1,1,2,1,1,0,3),('2.49.1.77692',1,1,3,3,1,1,0,3),('2.49.1.77692',1,2,1,2,1,1,0,12),('2.49.1.77692',1,2,2,2,1,1,0,24),('2.49.1.77692',1,2,2,3,1,1,0,12),('2.49.1.77692',1,2,3,2,1,1,0,6),('2.49.1.77692',1,2,3,3,1,1,0,3),('2.49.1.77692',1,3,1,4,1,1,0,3),('2.49.1.77692',1,3,2,2,1,1,0,3),('2.49.1.77692',1,3,2,3,1,1,0,9),('2.49.1.77692',1,3,3,3,1,1,0,3),('2.49.1.77692',1,3,3,4,1,1,0,9),('2.49.1.77692',1,4,2,4,1,1,0,3),('2.49.1.77692',1,4,3,4,1,1,0,3),('2.49.1.77692',5,3,3,4,1,1,26,1),('2.49.1.77692',5,3,3,4,1,1,27,1),('2.49.1.77692',5,3,3,4,1,1,64,1);
/*!40000 ALTER TABLE `global_hero_stats_bans` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-12-18 18:44:45
