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
-- Table structure for table `maps`
--

DROP TABLE IF EXISTS `maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maps` (
  `map_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`map_id`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maps`
--

LOCK TABLES `maps` WRITE;
/*!40000 ALTER TABLE `maps` DISABLE KEYS */;
INSERT INTO `maps` VALUES (1,'Battlefield of Eternity','BattlefieldOfEternity','standard'),(2,'Blackheart\'s Bay','BlackheartsBay','standard'),(3,'Braxis Holdout','BraxisHoldout','standard'),(4,'Cursed Hollow','CursedHollow','standard'),(5,'Dragon Shire','DragonShire','standard'),(6,'Garden of Terror','HauntedWoods','standard'),(7,'Hanamura Temple','Hanamura','standard'),(8,'Haunted Mines','HauntedMines','standard'),(9,'Infernal Shrines','Shrines','standard'),(10,'Sky Temple','ControlPoints','standard'),(11,'Tomb of the Spider Queen','Crypts','standard'),(12,'Towers of Doom','TowersOfDoom','standard'),(13,'Warhead Junction','Warhead Junction','standard'),(14,'Volskaya Foundry','Volskaya','standard'),(15,'Alterac Pass','AlteracPass','standard'),(16,'Escape From Braxis','EscapeFromBraxis','brawl'),(17,'Industrial District','IndustrialDistrict','brawl'),(18,'Lost Cavern','LostCavern','brawl'),(19,'Pull Party','PullParty','brawl'),(20,'Silver City','SilverCity','brawl'),(21,'Braxis Outpost','BraxisOutpost','brawl'),(22,'Checkpoint: Hanamura','HanamuraPayloadPush','brawl');
/*!40000 ALTER TABLE `maps` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-10-12 15:35:57
