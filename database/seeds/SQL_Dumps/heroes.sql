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
-- Table structure for table `heroes`
--

DROP TABLE IF EXISTS `heroes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `heroes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `short_name` varchar(32) CHARACTER SET utf8mb4 NOT NULL,
  `alt_name` varchar(45) DEFAULT NULL,
  `role` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_role` varchar(45) DEFAULT NULL,
  `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_date` datetime DEFAULT NULL,
  `rework_date` datetime DEFAULT NULL,
  `attribute_id` char(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Hero` (`name`),
  KEY `heroes_name_index` (`name`(191)),
  KEY `heroes_shortcut_index` (`attribute_id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `heroes`
--

LOCK TABLES `heroes` WRITE;
/*!40000 ALTER TABLE `heroes` DISABLE KEYS */;
INSERT INTO `heroes` VALUES (1,'Abathur','abathur',NULL,'Specialist','Support','Melee','2014-03-13 00:00:00',NULL,'Abat'),(2,'Alarak','alarak',NULL,'Assassin','Melee Assassin','Melee','2016-09-13 00:00:00','2017-05-16 00:00:00','Alar'),(3,'Anub\'arak','anubarak','Anubarak','Warrior','Tank','Melee','2014-10-07 00:00:00','2017-03-14 00:00:00','Anub'),(4,'Artanis','artanis',NULL,'Warrior','Bruiser','Melee','2015-10-20 00:00:00','2016-12-14 00:00:00','Arts'),(5,'Arthas','arthas',NULL,'Warrior','Tank','Melee','2014-03-13 00:00:00','2017-03-14 00:00:00','Arth'),(6,'Auriel','auriel',NULL,'Support','Healer','Ranged','2016-08-09 00:00:00',NULL,'Auri'),(7,'Azmodan','azmodan',NULL,'Specialist','Ranged Assassin','Ranged','2014-10-07 00:00:00',NULL,'Azmo'),(8,'Brightwing','brightwing','FaerieDragon','Support','Healer','Ranged','2014-04-15 00:00:00','2018-09-25 00:00:00','Faer'),(9,'Cassia','cassia','Amazon','Assassin','Ranged Assassin','Ranged','2017-04-04 00:00:00',NULL,'Amaz'),(10,'Chen','chen',NULL,'Warrior','Bruiser','Melee','2014-09-10 00:00:00','2016-09-27 00:00:00','Chen'),(11,'Cho','cho',NULL,'Warrior','Bruiser','Melee','2015-11-17 00:00:00','2017-03-14 00:00:00','CCho'),(12,'Chromie','chromie',NULL,'Assassin','Ranged Assassin','Ranged','2016-05-17 00:00:00','2018-08-07 00:00:00','Chro'),(13,'D.Va','dva','DVa','Warrior','Bruiser','Ranged','2017-05-16 00:00:00',NULL,'DVA0'),(14,'Dehaka','dehaka',NULL,'Warrior','Bruiser','Melee','2016-03-29 00:00:00','2016-12-14 00:00:00','Deha'),(15,'Diablo','diablo',NULL,'Warrior','Tank','Melee','2014-03-13 00:00:00','2018-05-14 00:00:00','Diab'),(16,'E.T.C.','etc','L90ETC','Warrior','Tank','Melee','2014-03-13 00:00:00','2016-09-27 00:00:00','L90E'),(17,'Falstad','falstad',NULL,'Assassin','Ranged Assassin','Ranged','2014-03-13 00:00:00',NULL,'Fals'),(18,'Gall','gall',NULL,'Assassin','Ranged Assassin','Ranged','2015-11-17 00:00:00','2017-03-14 00:00:00','Gall'),(19,'Garrosh','garrosh',NULL,'Warrior','Tank','Melee','2017-08-08 00:00:00',NULL,'Garr'),(20,'Gazlowe','gazlowe','Tinker','Specialist','Melee Assassin','Melee','2014-03-13 00:00:00','2017-03-14 00:00:00','Tink'),(21,'Genji','genji',NULL,'Assassin','Ranged Assassin','Ranged','2017-04-25 00:00:00',NULL,'Genj'),(22,'Greymane','greymane',NULL,'Assassin','Ranged Assassin','Ranged','2016-01-12 00:00:00',NULL,'Genn'),(23,'Gul\'dan','guldan','Guldan','Assassin','Ranged Assassin','Ranged','2016-07-12 00:00:00',NULL,'Guld'),(24,'Illidan','illidan',NULL,'Assassin','Melee Assassin','Melee','2014-03-13 00:00:00',NULL,'Illi'),(25,'Jaina','jaina',NULL,'Assassin','Ranged Assassin','Ranged','2014-12-02 00:00:00','2017-09-05 00:00:00','Jain'),(26,'Johanna','johanna','Crusader','Warrior','Tank','Melee','2015-06-02 00:00:00','2017-09-26 00:00:00','Crus'),(27,'Kael\'thas','kaelthas','Kaelthas','Assassin','Ranged Assassin','Ranged','2015-05-12 00:00:00',NULL,'Kael'),(28,'Kerrigan','kerrigan',NULL,'Assassin','Melee Assassin','Melee','2014-03-13 00:00:00','2018-09-25 00:00:00','Kerr'),(29,'Kharazim','kharazim','Monk','Support','Healer','Melee','2015-08-18 00:00:00','2016-11-15 00:00:00','Monk'),(30,'Leoric','leoric',NULL,'Warrior','Bruiser','Melee','2015-07-21 00:00:00','2017-09-05 00:00:00','Leor'),(31,'Li Li','lili','LiLi','Support','Healer','Ranged','2014-04-15 00:00:00','2017-11-14 00:00:00','LiLi'),(32,'Li-Ming','liming','Wizard','Assassin','Ranged Assassin','Ranged','2016-02-02 00:00:00',NULL,'Wiza'),(33,'Lt. Morales','ltmorales','Medic','Support','Healer','Ranged','2015-10-06 00:00:00','2017-09-05 00:00:00','Medi'),(34,'Lúcio','lucio',NULL,'Support','Healer','Ranged','2017-02-14 00:00:00',NULL,'Luci'),(35,'Lunara','lunara','Dryad','Assassin','Ranged Assassin','Ranged','2015-12-15 00:00:00','2018-05-14 00:00:00','Drya'),(36,'Malfurion','malfurion',NULL,'Support','Healer','Ranged','2014-03-13 00:00:00','2018-01-02 00:00:00','Malf'),(37,'Malthael','malthael',NULL,'Assassin','Bruiser','Melee','2017-06-13 00:00:00','2018-02-06 00:00:00','MALT'),(38,'Medivh','medivh',NULL,'Specialist','Support','Ranged','2016-06-14 00:00:00','2018-07-10 00:00:00','Mdvh'),(39,'Muradin','muradin',NULL,'Warrior','Tank','Melee','2014-03-13 00:00:00','2017-10-17 00:00:00','Mura'),(40,'Murky','murky',NULL,'Specialist','Melee Assassin','Melee','2014-05-22 00:00:00','2017-02-14 00:00:00','Murk'),(41,'Nazeebo','nazeebo','WitchDoctor','Specialist','Ranged Assassin','Ranged','2014-03-13 00:00:00','2016-09-27 00:00:00','Witc'),(42,'Nova','nova',NULL,'Assassin','Ranged Assassin','Ranged','2014-03-13 00:00:00','2017-12-12 00:00:00','Nova'),(43,'Probius','probius',NULL,'Specialist','Ranged Assassin','Ranged','2017-03-14 00:00:00',NULL,'Prob'),(44,'Ragnaros','ragnaros',NULL,'Assassin','Bruiser','Melee','2016-12-14 00:00:00',NULL,'Ragn'),(45,'Raynor','raynor',NULL,'Assassin','Ranged Assassin','Ranged','2014-03-13 00:00:00','2018-07-10 00:00:00','Rayn'),(46,'Rehgar','rehgar',NULL,'Support','Healer','Melee','2014-07-23 00:00:00',NULL,'Rehg'),(47,'Rexxar','rexxar',NULL,'Warrior','Bruiser','Ranged','2015-09-08 00:00:00','2017-01-24 00:00:00','Rexx'),(48,'Samuro','samuro',NULL,'Assassin','Melee Assassin','Melee','2016-10-18 00:00:00','2017-12-12 00:00:00','Samu'),(49,'Sgt. Hammer','sgthammer','SgtHammer','Specialist','Ranged Assassin','Ranged','2014-03-13 00:00:00','2017-11-29 00:00:00','Sgth'),(50,'Sonya','sonya','Barbarian','Warrior','Bruiser','Melee','2014-03-13 00:00:00','2018-03-06 00:00:00','Barb'),(51,'Stitches','stitches',NULL,'Warrior','Tank','Melee','2014-03-13 00:00:00',NULL,'Stit'),(52,'Stukov','stukov',NULL,'Support','Healer','Melee','2017-07-11 00:00:00',NULL,'STUK'),(53,'Sylvanas','sylvanas',NULL,'Specialist','Ranged Assassin','Ranged','2015-03-24 00:00:00',NULL,'Sylv'),(54,'Tassadar','tassadar',NULL,'Support','Support','Ranged','2014-03-13 00:00:00','2017-01-24 00:00:00','Tass'),(55,'The Butcher','thebutcher','Butcher','Assassin','Melee Assassin','Melee','2015-06-30 00:00:00','2016-09-13 00:00:00','Butc'),(56,'The Lost Vikings','thelostvikings','LostVikings','Specialist','Support','Melee','2015-02-10 00:00:00',NULL,'Lost'),(57,'Thrall','thrall',NULL,'Assassin','Bruiser','Melee','2015-01-13 00:00:00','2017-06-13 00:00:00','Thra'),(58,'Tracer','tracer',NULL,'Assassin','Ranged Assassin','Ranged','2016-04-19 00:00:00',NULL,'Tra0'),(59,'Tychus','tychus',NULL,'Assassin','Ranged Assassin','Ranged','2014-03-18 00:00:00',NULL,'Tych'),(60,'Tyrael','tyrael',NULL,'Warrior','Tank','Melee','2014-03-13 00:00:00','2018-01-16 00:00:00','Tyrl'),(61,'Tyrande','tyrande',NULL,'Support','Healer','Ranged','2014-03-13 00:00:00','2018-08-27 00:00:00','Tyrd'),(62,'Uther','uther',NULL,'Support','Healer','Melee','2014-03-13 00:00:00','2017-04-25 00:00:00','Uthe'),(63,'Valeera','valeera',NULL,'Assassin','Melee Assassin','Melee','2017-01-24 00:00:00','2017-12-12 00:00:00','VALE'),(64,'Valla','valla','DemonHunter','Assassin','Ranged Assassin','Ranged','2014-03-13 00:00:00','2016-09-13 00:00:00','Demo'),(65,'Varian','varian',NULL,'Multiclass','Bruiser','Melee','2016-11-15 00:00:00','2018-03-19 00:00:00','Vari'),(66,'Xul','xul','Necromancer','Specialist','Bruiser','Melee','2016-03-01 00:00:00','2017-07-11 00:00:00','Necr'),(67,'Zagara','zagara',NULL,'Specialist','Ranged Assassin','Ranged','2014-06-25 00:00:00',NULL,'Zaga'),(68,'Zarya','zarya',NULL,'Warrior','Support','Ranged','2016-09-27 00:00:00',NULL,'Zary'),(69,'Zeratul','zeratul',NULL,'Assassin','Melee Assassin','Melee','2014-03-13 00:00:00','2017-12-12 00:00:00','Zera'),(70,'Zul\'jin','zuljin','Zuljin','Assassin','Ranged Assassin','Ranged','2017-01-04 00:00:00','2017-10-17 00:00:00','ZULJ'),(71,'Kel\'Thuzad','kelthuzad','KelThuzad','Assassin','Ranged Assassin','Ranged','2017-09-05 00:00:00',NULL,'KelT'),(72,'Ana','ana',NULL,'Support','Healer','Ranged','2017-09-26 00:00:00','2018-03-06 00:00:00','HANA'),(73,'Junkrat','junkrat',NULL,'Assassin','Ranged Assassin','Ranged','2017-10-17 00:00:00',NULL,'Junk'),(74,'Alexstrasza','alexstrasza',NULL,'Support','Healer','Ranged','2017-11-14 00:00:00',NULL,'Alex'),(75,'Hanzo','hanzo',NULL,'Assassin','Ranged Assassin','Ranged','2017-12-12 00:00:00',NULL,'Hanz'),(77,'Blaze','blaze','Firebat','Warrior','Tank','Ranged','2018-01-09 00:00:00',NULL,'Fire'),(78,'Maiev','maiev',NULL,'Assassin','Melee Assassin','Melee','2018-02-06 00:00:00',NULL,'Maie'),(79,'Fenix','fenix',NULL,'Assassin','Ranged Assassin','Ranged','2018-03-27 00:00:00',NULL,'FENX'),(80,'Deckard','deckard',NULL,'Support','Healer','Melee','2018-04-24 00:00:00',NULL,'DECK'),(81,'Yrel','yrel',NULL,'Warrior','Bruiser','Melee','2018-06-12 00:00:00',NULL,'YREL'),(82,'Whitemane','whitemane',NULL,'Support','Healer','Ranged','2018-08-05 00:00:00',NULL,'WHIT'),(83,'Mephisto','mephisto',NULL,'Assassin','Ranged Assassin','Ranged','2018-09-03 00:00:00',NULL,'MEPH'),(84,'Mal\'Ganis','malganis','MalGanis','Warrior','Tank','Melee','2018-10-16 00:00:00',NULL,'MalG'),(85,'Orphea','orphea','','Assassin','Ranged Assassin','Ranged','2018-11-13 00:00:00',NULL,'ORPH'),(86,'Imperius','imperius',NULL,'Warrior','Bruiser','Melee','2019-01-08 00:00:00',NULL,'IMPE'),(87,'Anduin','anduin','','Healer','Healer','Ranged','2019-04-30 00:00:00','2019-04-23 11:58:16','Andu'),(88,'Qhira','qhira',NULL,NULL,'Melee Assassin','Melee','2019-08-06 00:00:00',NULL,'NXHU'),('89', 'Deathwing', 'deathwing', NULL, NULL, 'Bruiser', 'Melee', '2019-12-03 00:00:00', NULL, 'DEAT');
/*!40000 ALTER TABLE `heroes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-10-10  9:13:22
