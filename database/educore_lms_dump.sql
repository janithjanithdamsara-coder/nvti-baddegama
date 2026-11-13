-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: educore_lms
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_by` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `target_role` enum('all','teachers','students') DEFAULT 'all',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,1,'Welcome to EduCore LMS - NVTI Baddegama','Welcome all lecturers and students to the official NVTI Baddegama Diploma LMS. Morning sessions run 09:00 - 12:00 and afternoon sessions run 13:00 - 16:00.','all','2026-11-06 11:57:15');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `instructions` text NOT NULL,
  `reference_file` varchar(255) DEFAULT NULL,
  `total_marks` decimal(5,2) NOT NULL DEFAULT 100.00,
  `deadline` datetime NOT NULL,
  `status` enum('active','closed') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` VALUES (1,3,1,2,'Assignment 01: Secure Login & CRUD System in PHP','Develop a PHP application with user registration, login with password_hash, and role-based redirect. Upload your project as a ZIP or PDF report.',NULL,100.00,'2026-11-13 11:57:15','active','2026-11-06 11:57:15'),(2,2,1,1,'ERD Quiz 1','Read the hospital management case scenario and draw the complete Entity Relationship Diagram (ERD) with cardinality and primary/foreign keys.',NULL,50.00,'2026-11-20 23:59:00','active','2026-09-08 14:00:00'),(3,2,1,1,'Normalization Quiz 01','Take the unnormalized purchase invoice table and convert it through 1NF, 2NF and 3NF step-by-step with justification for each transition.',NULL,50.00,'2026-11-22 23:59:00','active','2026-09-08 14:30:00'),(4,2,1,1,'Assignment 01-Written Submission (DBMS Architecture & ERD)','Prepare a comprehensive written technical report covering 3-Tier Client Server DBMS architecture, data redundancy reduction, and complete normalized schema with SQL DDL scripts.',NULL,100.00,'2026-11-30 23:59:00','active','2026-09-10 15:00:00'),(5,2,1,8,'Assignment 01: Responsive Multi-Page Web Portfolio','Design and build a responsive web application portfolio using semantic HTML5, modern CSS Grid/Flexbox, and PHP backend integration.',NULL,100.00,'2026-12-05 23:59:00','active','2026-09-12 10:00:00');
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late','excused') DEFAULT 'present',
  `marked_by_user_id` int(11) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_date` (`student_id`,`date`),
  KEY `student_id` (`student_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,4,1,'2026-10-05','present',NULL,'On time','2026-11-06 14:04:03'),(2,4,1,'2026-10-06','present',NULL,'On time','2026-11-06 14:04:03'),(3,4,1,'2026-10-07','present',NULL,'On time','2026-11-06 14:04:03'),(4,4,1,'2026-10-08','present',NULL,'On time','2026-11-06 14:04:03'),(5,4,1,'2026-10-09','late',NULL,'15 mins late','2026-11-06 14:04:03'),(6,4,1,'2026-10-12','present',NULL,'On time','2026-11-06 14:04:03'),(7,4,1,'2026-10-13','present',NULL,'On time','2026-11-06 14:04:03'),(8,4,1,'2026-10-14','absent',NULL,'Medical','2026-11-06 14:04:03'),(9,4,1,'2026-10-15','present',NULL,'On time','2026-11-06 14:04:03'),(10,4,1,'2026-10-16','present',NULL,'On time','2026-11-06 14:04:03'),(11,4,1,'2026-10-19','present',NULL,'On time','2026-11-06 14:04:03'),(12,4,1,'2026-10-20','present',NULL,'On time','2026-11-06 14:04:03'),(13,4,1,'2026-10-21','present',NULL,'On time','2026-11-06 14:04:03'),(14,4,1,'2026-10-22','present',NULL,'On time','2026-11-06 14:04:03'),(15,4,1,'2026-10-23','present',NULL,'On time','2026-11-06 14:04:03'),(16,4,1,'2026-10-26','present',NULL,'On time','2026-11-06 14:04:03'),(17,4,1,'2026-10-27','present',NULL,'On time','2026-11-06 14:04:03'),(18,4,1,'2026-10-28','late',NULL,'Bus delay','2026-11-06 14:04:03'),(19,4,1,'2026-10-29','present',NULL,'On time','2026-11-06 14:04:03'),(20,4,1,'2026-10-30','present',NULL,'On time','2026-11-06 14:04:03'),(21,4,1,'2026-11-02','present',NULL,'On time','2026-11-06 14:04:03'),(22,4,1,'2026-11-03','present',NULL,'On time','2026-11-06 14:04:03'),(23,4,1,'2026-11-04','present',NULL,'On time','2026-11-06 14:04:03'),(24,4,1,'2026-11-05','present',NULL,'On time','2026-11-06 14:04:03'),(26,5,NULL,'2026-10-05','present',4,'On time','2026-11-06 15:00:50'),(27,6,NULL,'2026-10-05','present',4,'On time','2026-11-06 15:00:50'),(28,7,NULL,'2026-10-05','present',4,'On time','2026-11-06 15:00:50'),(29,8,NULL,'2026-10-05','present',4,'On time','2026-11-06 15:00:50'),(30,9,NULL,'2026-10-05','present',4,'On time','2026-11-06 15:00:50'),(31,10,NULL,'2026-10-05','present',4,'On time','2026-11-06 15:00:50'),(32,11,NULL,'2026-10-05','present',4,'On time','2026-11-06 15:00:50'),(34,5,NULL,'2026-10-06','absent',4,'Medical leave','2026-11-06 15:00:50'),(35,6,NULL,'2026-10-06','present',4,'On time','2026-11-06 15:00:50'),(36,7,NULL,'2026-10-06','present',4,'On time','2026-11-06 15:00:50'),(37,8,NULL,'2026-10-06','present',4,'On time','2026-11-06 15:00:50'),(38,9,NULL,'2026-10-06','present',4,'On time','2026-11-06 15:00:50'),(39,10,NULL,'2026-10-06','present',4,'On time','2026-11-06 15:00:50'),(40,11,NULL,'2026-10-06','present',4,'On time','2026-11-06 15:00:50'),(42,5,NULL,'2026-10-07','present',4,'On time','2026-11-06 15:00:50'),(43,6,NULL,'2026-10-07','present',4,'On time','2026-11-06 15:00:50'),(44,7,NULL,'2026-10-07','present',4,'On time','2026-11-06 15:00:50'),(45,8,NULL,'2026-10-07','late',4,'Transport delay','2026-11-06 15:00:50'),(46,9,NULL,'2026-10-07','present',4,'On time','2026-11-06 15:00:50'),(47,10,NULL,'2026-10-07','present',4,'On time','2026-11-06 15:00:50'),(48,11,NULL,'2026-10-07','present',4,'On time','2026-11-06 15:00:50'),(50,5,NULL,'2026-10-08','present',4,'On time','2026-11-06 15:00:50'),(51,6,NULL,'2026-10-08','present',4,'On time','2026-11-06 15:00:50'),(52,7,NULL,'2026-10-08','present',4,'On time','2026-11-06 15:00:50'),(53,8,NULL,'2026-10-08','absent',4,'Medical leave','2026-11-06 15:00:50'),(54,9,NULL,'2026-10-08','present',4,'On time','2026-11-06 15:00:50'),(55,10,NULL,'2026-10-08','present',4,'On time','2026-11-06 15:00:50'),(56,11,NULL,'2026-10-08','late',4,'Transport delay','2026-11-06 15:00:50'),(58,5,NULL,'2026-10-09','present',4,'On time','2026-11-06 15:00:50'),(59,6,NULL,'2026-10-09','present',4,'On time','2026-11-06 15:00:50'),(60,7,NULL,'2026-10-09','present',4,'On time','2026-11-06 15:00:50'),(61,8,NULL,'2026-10-09','present',4,'On time','2026-11-06 15:00:50'),(62,9,NULL,'2026-10-09','present',4,'On time','2026-11-06 15:00:50'),(63,10,NULL,'2026-10-09','present',4,'On time','2026-11-06 15:00:50'),(64,11,NULL,'2026-10-09','present',4,'On time','2026-11-06 15:00:50'),(66,5,NULL,'2026-10-12','absent',4,'Medical leave','2026-11-06 15:00:50'),(67,6,NULL,'2026-10-12','present',4,'On time','2026-11-06 15:00:50'),(68,7,NULL,'2026-10-12','present',4,'On time','2026-11-06 15:00:50'),(69,8,NULL,'2026-10-12','present',4,'On time','2026-11-06 15:00:50'),(70,9,NULL,'2026-10-12','present',4,'On time','2026-11-06 15:00:50'),(71,10,NULL,'2026-10-12','present',4,'On time','2026-11-06 15:00:50'),(72,11,NULL,'2026-10-12','absent',4,'Medical leave','2026-11-06 15:00:50'),(74,5,NULL,'2026-10-13','present',4,'On time','2026-11-06 15:00:50'),(75,6,NULL,'2026-10-13','present',4,'On time','2026-11-06 15:00:50'),(76,7,NULL,'2026-10-13','present',4,'On time','2026-11-06 15:00:50'),(77,8,NULL,'2026-10-13','present',4,'On time','2026-11-06 15:00:50'),(78,9,NULL,'2026-10-13','present',4,'On time','2026-11-06 15:00:50'),(79,10,NULL,'2026-10-13','present',4,'On time','2026-11-06 15:00:50'),(80,11,NULL,'2026-10-13','present',4,'On time','2026-11-06 15:00:50'),(82,5,NULL,'2026-10-14','present',4,'On time','2026-11-06 15:00:50'),(83,6,NULL,'2026-10-14','present',4,'On time','2026-11-06 15:00:50'),(84,7,NULL,'2026-10-14','present',4,'On time','2026-11-06 15:00:50'),(85,8,NULL,'2026-10-14','present',4,'On time','2026-11-06 15:00:50'),(86,9,NULL,'2026-10-14','present',4,'On time','2026-11-06 15:00:50'),(87,10,NULL,'2026-10-14','present',4,'On time','2026-11-06 15:00:50'),(88,11,NULL,'2026-10-14','present',4,'On time','2026-11-06 15:00:50'),(90,5,NULL,'2026-10-15','present',4,'On time','2026-11-06 15:00:50'),(91,6,NULL,'2026-10-15','present',4,'On time','2026-11-06 15:00:50'),(92,7,NULL,'2026-10-15','present',4,'On time','2026-11-06 15:00:50'),(93,8,NULL,'2026-10-15','present',4,'On time','2026-11-06 15:00:50'),(94,9,NULL,'2026-10-15','present',4,'On time','2026-11-06 15:00:50'),(95,10,NULL,'2026-10-15','present',4,'On time','2026-11-06 15:00:50'),(96,11,NULL,'2026-10-15','present',4,'On time','2026-11-06 15:00:50'),(98,5,NULL,'2026-10-16','present',4,'On time','2026-11-06 15:00:50'),(99,6,NULL,'2026-10-16','present',4,'On time','2026-11-06 15:00:50'),(100,7,NULL,'2026-10-16','present',4,'On time','2026-11-06 15:00:50'),(101,8,NULL,'2026-10-16','present',4,'On time','2026-11-06 15:00:50'),(102,9,NULL,'2026-10-16','absent',4,'Medical leave','2026-11-06 15:00:50'),(103,10,NULL,'2026-10-16','present',4,'On time','2026-11-06 15:00:50'),(104,11,NULL,'2026-10-16','present',4,'On time','2026-11-06 15:00:50'),(106,5,NULL,'2026-10-19','present',4,'On time','2026-11-06 15:00:50'),(107,6,NULL,'2026-10-19','present',4,'On time','2026-11-06 15:00:50'),(108,7,NULL,'2026-10-19','absent',4,'Medical leave','2026-11-06 15:00:50'),(109,8,NULL,'2026-10-19','present',4,'On time','2026-11-06 15:00:50'),(110,9,NULL,'2026-10-19','present',4,'On time','2026-11-06 15:00:50'),(111,10,NULL,'2026-10-19','present',4,'On time','2026-11-06 15:00:50'),(112,11,NULL,'2026-10-19','present',4,'On time','2026-11-06 15:00:50'),(114,5,NULL,'2026-10-20','present',4,'On time','2026-11-06 15:00:50'),(115,6,NULL,'2026-10-20','present',4,'On time','2026-11-06 15:00:50'),(116,7,NULL,'2026-10-20','present',4,'On time','2026-11-06 15:00:50'),(117,8,NULL,'2026-10-20','present',4,'On time','2026-11-06 15:00:50'),(118,9,NULL,'2026-10-20','present',4,'On time','2026-11-06 15:00:50'),(119,10,NULL,'2026-10-20','present',4,'On time','2026-11-06 15:00:50'),(120,11,NULL,'2026-10-20','present',4,'On time','2026-11-06 15:00:50'),(122,5,NULL,'2026-10-21','present',4,'On time','2026-11-06 15:00:50'),(123,6,NULL,'2026-10-21','present',4,'On time','2026-11-06 15:00:50'),(124,7,NULL,'2026-10-21','present',4,'On time','2026-11-06 15:00:50'),(125,8,NULL,'2026-10-21','present',4,'On time','2026-11-06 15:00:50'),(126,9,NULL,'2026-10-21','present',4,'On time','2026-11-06 15:00:50'),(127,10,NULL,'2026-10-21','present',4,'On time','2026-11-06 15:00:50'),(128,11,NULL,'2026-10-21','present',4,'On time','2026-11-06 15:00:50'),(130,5,NULL,'2026-10-22','present',4,'On time','2026-11-06 15:00:50'),(131,6,NULL,'2026-10-22','present',4,'On time','2026-11-06 15:00:50'),(132,7,NULL,'2026-10-22','present',4,'On time','2026-11-06 15:00:50'),(133,8,NULL,'2026-10-22','present',4,'On time','2026-11-06 15:00:50'),(134,9,NULL,'2026-10-22','present',4,'On time','2026-11-06 15:00:50'),(135,10,NULL,'2026-10-22','present',4,'On time','2026-11-06 15:00:50'),(136,11,NULL,'2026-10-22','present',4,'On time','2026-11-06 15:00:50'),(138,5,NULL,'2026-10-23','present',4,'On time','2026-11-06 15:00:50'),(139,6,NULL,'2026-10-23','absent',4,'Medical leave','2026-11-06 15:00:50'),(140,7,NULL,'2026-10-23','present',4,'On time','2026-11-06 15:00:50'),(141,8,NULL,'2026-10-23','present',4,'On time','2026-11-06 15:00:50'),(142,9,NULL,'2026-10-23','present',4,'On time','2026-11-06 15:00:50'),(143,10,NULL,'2026-10-23','present',4,'On time','2026-11-06 15:00:50'),(144,11,NULL,'2026-10-23','late',4,'Transport delay','2026-11-06 15:00:50'),(146,5,NULL,'2026-10-26','present',4,'On time','2026-11-06 15:00:50'),(147,6,NULL,'2026-10-26','present',4,'On time','2026-11-06 15:00:50'),(148,7,NULL,'2026-10-26','present',4,'On time','2026-11-06 15:00:50'),(149,8,NULL,'2026-10-26','present',4,'On time','2026-11-06 15:00:50'),(150,9,NULL,'2026-10-26','present',4,'On time','2026-11-06 15:00:50'),(151,10,NULL,'2026-10-26','present',4,'On time','2026-11-06 15:00:50'),(152,11,NULL,'2026-10-26','present',4,'On time','2026-11-06 15:00:50'),(154,5,NULL,'2026-10-27','present',4,'On time','2026-11-06 15:00:50'),(155,6,NULL,'2026-10-27','present',4,'On time','2026-11-06 15:00:50'),(156,7,NULL,'2026-10-27','present',4,'On time','2026-11-06 15:00:50'),(157,8,NULL,'2026-10-27','present',4,'On time','2026-11-06 15:00:50'),(158,9,NULL,'2026-10-27','present',4,'On time','2026-11-06 15:00:50'),(159,10,NULL,'2026-10-27','present',4,'On time','2026-11-06 15:00:50'),(160,11,NULL,'2026-10-27','absent',4,'Medical leave','2026-11-06 15:00:50'),(162,5,NULL,'2026-10-28','present',4,'On time','2026-11-06 15:00:50'),(163,6,NULL,'2026-10-28','present',4,'On time','2026-11-06 15:00:50'),(164,7,NULL,'2026-10-28','present',4,'On time','2026-11-06 15:00:50'),(165,8,NULL,'2026-10-28','present',4,'On time','2026-11-06 15:00:50'),(166,9,NULL,'2026-10-28','present',4,'On time','2026-11-06 15:00:50'),(167,10,NULL,'2026-10-28','present',4,'On time','2026-11-06 15:00:50'),(168,11,NULL,'2026-10-28','present',4,'On time','2026-11-06 15:00:50'),(170,5,NULL,'2026-10-29','present',4,'On time','2026-11-06 15:00:50'),(171,6,NULL,'2026-10-29','present',4,'On time','2026-11-06 15:00:50'),(172,7,NULL,'2026-10-29','late',4,'Transport delay','2026-11-06 15:00:50'),(173,8,NULL,'2026-10-29','present',4,'On time','2026-11-06 15:00:50'),(174,9,NULL,'2026-10-29','present',4,'On time','2026-11-06 15:00:50'),(175,10,NULL,'2026-10-29','present',4,'On time','2026-11-06 15:00:50'),(176,11,NULL,'2026-10-29','present',4,'On time','2026-11-06 15:00:50'),(178,5,NULL,'2026-10-30','present',4,'On time','2026-11-06 15:00:50'),(179,6,NULL,'2026-10-30','present',4,'On time','2026-11-06 15:00:50'),(180,7,NULL,'2026-10-30','present',4,'On time','2026-11-06 15:00:50'),(181,8,NULL,'2026-10-30','present',4,'On time','2026-11-06 15:00:50'),(182,9,NULL,'2026-10-30','present',4,'On time','2026-11-06 15:00:50'),(183,10,NULL,'2026-10-30','late',4,'Transport delay','2026-11-06 15:00:50'),(184,11,NULL,'2026-10-30','present',4,'On time','2026-11-06 15:00:50'),(186,5,NULL,'2026-11-02','late',4,'Transport delay','2026-11-06 15:00:50'),(187,6,NULL,'2026-11-02','present',4,'On time','2026-11-06 15:00:50'),(188,7,NULL,'2026-11-02','present',4,'On time','2026-11-06 15:00:50'),(189,8,NULL,'2026-11-02','present',4,'On time','2026-11-06 15:00:50'),(190,9,NULL,'2026-11-02','present',4,'On time','2026-11-06 15:00:50'),(191,10,NULL,'2026-11-02','present',4,'On time','2026-11-06 15:00:50'),(192,11,NULL,'2026-11-02','present',4,'On time','2026-11-06 15:00:50'),(194,5,NULL,'2026-11-03','present',4,'On time','2026-11-06 15:00:50'),(195,6,NULL,'2026-11-03','present',4,'On time','2026-11-06 15:00:50'),(196,7,NULL,'2026-11-03','present',4,'On time','2026-11-06 15:00:50'),(197,8,NULL,'2026-11-03','present',4,'On time','2026-11-06 15:00:50'),(198,9,NULL,'2026-11-03','present',4,'On time','2026-11-06 15:00:50'),(199,10,NULL,'2026-11-03','present',4,'On time','2026-11-06 15:00:50'),(200,11,NULL,'2026-11-03','present',4,'On time','2026-11-06 15:00:50'),(202,5,NULL,'2026-11-04','late',4,'Transport delay','2026-11-06 15:00:50'),(203,6,NULL,'2026-11-04','present',4,'On time','2026-11-06 15:00:50'),(204,7,NULL,'2026-11-04','present',4,'On time','2026-11-06 15:00:50'),(205,8,NULL,'2026-11-04','present',4,'On time','2026-11-06 15:00:50'),(206,9,NULL,'2026-11-04','present',4,'On time','2026-11-06 15:00:50'),(207,10,NULL,'2026-11-04','present',4,'On time','2026-11-06 15:00:50'),(208,11,NULL,'2026-11-04','present',4,'On time','2026-11-06 15:00:50'),(210,5,NULL,'2026-11-05','present',4,'On time','2026-11-06 15:00:50'),(211,6,NULL,'2026-11-05','present',4,'On time','2026-11-06 15:00:50'),(212,7,NULL,'2026-11-05','present',4,'On time','2026-11-06 15:00:50'),(213,8,NULL,'2026-11-05','absent',4,'Medical leave','2026-11-06 15:00:50'),(214,9,NULL,'2026-11-05','present',4,'On time','2026-11-06 15:00:50'),(215,10,NULL,'2026-11-05','present',4,'On time','2026-11-06 15:00:50'),(216,11,NULL,'2026-11-05','present',4,'On time','2026-11-06 15:00:50'),(217,4,NULL,'2026-11-06','present',2,'Monitor on time','2026-11-06 15:05:30'),(218,5,NULL,'2026-11-06','present',2,'Backup monitor present','2026-11-06 15:05:30'),(219,6,NULL,'2026-11-06','absent',2,'Flu','2026-11-06 15:05:30'),(220,7,NULL,'2026-11-06','late',2,'Traffic','2026-11-06 15:05:30'),(225,4,NULL,'2026-11-08','present',1,'','2026-11-08 08:31:38'),(226,5,NULL,'2026-11-08','present',1,'','2026-11-08 08:31:38'),(227,6,NULL,'2026-11-08','present',1,'','2026-11-08 08:31:38'),(228,7,NULL,'2026-11-08','present',1,'','2026-11-08 08:31:38'),(229,8,NULL,'2026-11-08','present',1,'','2026-11-08 08:31:38'),(230,9,NULL,'2026-11-08','present',1,'','2026-11-08 08:31:38'),(231,10,NULL,'2026-11-08','late',1,'','2026-11-08 08:31:38'),(232,11,NULL,'2026-11-08','absent',1,'','2026-11-08 08:31:38'),(249,4,NULL,'2026-11-09','present',1,'','2026-11-09 09:11:03'),(250,5,NULL,'2026-11-09','present',1,'','2026-11-09 09:11:03'),(251,6,NULL,'2026-11-09','present',1,'','2026-11-09 09:11:03'),(252,7,NULL,'2026-11-09','present',1,'','2026-11-09 09:11:03'),(253,8,NULL,'2026-11-09','present',1,'','2026-11-09 09:11:03'),(254,9,NULL,'2026-11-09','present',1,'','2026-11-09 09:11:03'),(255,10,NULL,'2026-11-09','present',1,'','2026-11-09 09:11:03'),(256,11,NULL,'2026-11-09','present',1,'','2026-11-09 09:11:03');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_day_locks`
--

DROP TABLE IF EXISTS `attendance_day_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_day_locks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `is_locked` tinyint(1) DEFAULT 1,
  `marked_by_user_id` int(11) NOT NULL,
  `marked_by_role` enum('student','teacher','admin') DEFAULT 'student',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unlocked_by` int(11) DEFAULT NULL,
  `unlocked_at` datetime DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending_verification','verified','rejected') NOT NULL DEFAULT 'pending_verification',
  `verified_by_user_id` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_class_date` (`class_id`,`date`),
  KEY `class_id` (`class_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_day_locks`
--

LOCK TABLES `attendance_day_locks` WRITE;
/*!40000 ALTER TABLE `attendance_day_locks` DISABLE KEYS */;
INSERT INTO `attendance_day_locks` VALUES (1,1,'2026-10-05',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(2,1,'2026-10-06',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(3,1,'2026-10-07',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(4,1,'2026-10-08',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(5,1,'2026-10-09',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(6,1,'2026-10-12',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(7,1,'2026-10-13',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(8,1,'2026-10-14',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(9,1,'2026-10-15',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(10,1,'2026-10-16',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(11,1,'2026-10-19',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(12,1,'2026-10-20',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(13,1,'2026-10-21',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(14,1,'2026-10-22',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(15,1,'2026-10-23',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(16,1,'2026-10-26',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(17,1,'2026-10-27',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(18,1,'2026-10-28',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(19,1,'2026-10-29',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(20,1,'2026-10-30',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(21,1,'2026-11-02',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(22,1,'2026-11-03',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(23,1,'2026-11-04',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(24,1,'2026-11-05',1,4,'student','2026-11-06 15:00:50',NULL,NULL,NULL,'pending_verification',NULL,NULL),(25,1,'2026-11-06',1,2,'teacher','2026-11-06 15:05:30',NULL,NULL,NULL,'pending_verification',NULL,NULL),(27,1,'2026-11-08',1,1,'teacher','2026-11-08 05:14:35',NULL,NULL,NULL,'verified',1,'2026-11-08 14:01:38'),(30,1,'2026-11-09',1,1,'admin','2026-11-09 09:10:55',NULL,NULL,NULL,'verified',1,'2026-11-09 14:41:03');
/*!40000 ALTER TABLE `attendance_day_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_holidays`
--

DROP TABLE IF EXISTS `attendance_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `holiday_name` varchar(150) NOT NULL,
  `holiday_type` enum('public_holiday','mercantile_holiday','special_closure','curriculum_recess','other') DEFAULT 'public_holiday',
  `description` text DEFAULT NULL,
  `marked_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_date_class` (`date`,`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_holidays`
--

LOCK TABLES `attendance_holidays` WRITE;
/*!40000 ALTER TABLE `attendance_holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_monitors`
--

DROP TABLE IF EXISTS `attendance_monitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_monitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `monitor_role` enum('primary','backup') DEFAULT 'primary',
  `status` enum('active','disabled') DEFAULT 'active',
  `assigned_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_class_student` (`class_id`,`student_id`),
  KEY `class_id` (`class_id`),
  KEY `student_id` (`student_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_monitors`
--

LOCK TABLES `attendance_monitors` WRITE;
/*!40000 ALTER TABLE `attendance_monitors` DISABLE KEYS */;
INSERT INTO `attendance_monitors` VALUES (1,1,4,'primary','active',2,'2026-11-06 14:59:57','2026-11-06 15:05:30'),(2,1,5,'backup','active',2,'2026-11-06 14:59:57','2026-11-06 14:59:57');
/*!40000 ALTER TABLE `attendance_monitors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'LOGIN','User successfully logged in.','::1','2026-11-06 12:15:06'),(2,1,'LOGIN','User successfully logged in.','::1','2026-11-06 14:12:55'),(3,1,'LOGOUT','User logged out.','::1','2026-11-06 15:07:27'),(4,1,'LOGIN','User successfully logged in.','::1','2026-11-06 15:49:03'),(5,1,'UPDATE_TEACHER','Updated instructor details for Gayan sir (ID 3)','::1','2026-11-06 15:49:26'),(6,1,'LOGOUT','User logged out.','::1','2026-11-06 15:51:05'),(7,1,'LOGIN','User successfully logged in.','::1','2026-11-06 15:59:26'),(8,1,'UPDATE_TEACHER','Updated instructor details for Gayan sir (ID 3)','::1','2026-11-06 16:04:54'),(9,1,'LOGOUT','User logged out.','::1','2026-11-06 18:56:25'),(10,4,'LOGIN','User successfully logged in.','::1','2026-11-06 19:46:00'),(11,4,'LOGOUT','User logged out.','::1','2026-11-06 20:08:56'),(12,4,'LOGIN','User successfully logged in.','::1','2026-11-06 20:11:39'),(13,4,'UPDATE_STUDENT_PROFILE','Student updated their profile details.','::1','2026-11-06 20:12:13'),(14,4,'LOGOUT','User logged out.','::1','2026-11-06 20:20:46'),(15,3,'LOGIN','User successfully logged in.','::1','2026-11-06 20:36:31'),(16,3,'LOGOUT','User logged out.','::1','2026-11-06 20:40:15'),(17,4,'LOGIN','User successfully logged in.','::1','2026-11-06 20:40:21'),(18,4,'LOGIN','User successfully logged in.','UNKNOWN','2026-11-06 20:48:54'),(19,4,'LOGIN','User successfully logged in.','UNKNOWN','2026-11-06 21:28:37'),(20,4,'LOGIN','User successfully logged in.','UNKNOWN','2026-11-06 21:34:13'),(21,4,'LOGIN','User successfully logged in.','UNKNOWN','2026-11-06 21:45:24'),(22,4,'LOGOUT','User logged out.','::1','2026-11-06 21:49:42'),(23,4,'LOGIN','User successfully logged in.','::1','2026-11-06 21:49:43'),(24,4,'LOGOUT','User logged out.','::1','2026-11-08 09:38:59'),(25,1,'LOGIN','User successfully logged in.','::1','2026-11-08 10:11:36'),(26,1,'UPDATE_STUDENT_STATUS','Changed student ID 11 status to suspended','::1','2026-11-08 10:11:59'),(27,1,'UPDATE_STUDENT_STATUS','Changed student ID 11 status to active','::1','2026-11-08 10:12:04'),(28,1,'VERIFY_ATTENDANCE','Verified and saved attendance for class 1 on 2026-11-08','::1','2026-11-08 10:44:35'),(29,1,'VERIFY_ATTENDANCE','Verified and saved attendance for class 1 on 2026-11-08','::1','2026-11-08 10:44:48'),(30,1,'CREATE_CLASSROOM_MODULE','Created new classroom module: it (DDD)','::1','2026-11-08 11:07:00'),(31,1,'DELETE_CLASSROOM_MODULE','Deleted class module subject ID 4','::1','2026-11-08 12:10:48'),(32,1,'CREATE_CLASSROOM_MODULE','Created new classroom module: demo (1234)','::1','2026-11-08 12:16:59'),(33,1,'CREATE_CLASSROOM_MODULE','Created new classroom module: demo 222 (222)','::1','2026-11-08 12:17:15'),(34,1,'UPLOAD_EXAM_PAPER','Uploaded paper \'2026 Semester 1 Final Exam - System analysis and design\' (model_paper) for K72T001M02','::1','2026-11-08 13:52:25'),(35,1,'VERIFY_ATTENDANCE','Verified and saved attendance for class 1 on 2026-11-08','::1','2026-11-08 14:01:38'),(36,1,'LOGOUT','User logged out.','::1','2026-11-08 15:16:24'),(37,4,'LOGIN','User successfully logged in.','::1','2026-11-08 15:16:31'),(38,4,'LOGOUT','User logged out.','::1','2026-11-08 15:16:49'),(39,1,'LOGIN','User successfully logged in.','::1','2026-11-08 15:16:53'),(40,1,'VERIFY_STUDENT_DOCUMENT','Document ID 1 status updated to verified','::1','2026-11-08 15:17:08'),(41,1,'LOGOUT','User logged out.','::1','2026-11-09 09:00:15'),(42,4,'LOGIN','User successfully logged in.','::1','2026-11-09 09:00:23'),(43,4,'LOGOUT','User logged out.','::1','2026-11-09 10:33:40'),(44,1,'LOGIN','User successfully logged in.','::1','2026-11-09 10:33:45'),(45,1,'VERIFY_ATTENDANCE','Verified and saved attendance for class 1 on 2026-11-09 (Role: admin)','::1','2026-11-09 14:40:55'),(46,1,'VERIFY_ATTENDANCE','Verified and saved attendance for class 1 on 2026-11-09 (Role: admin)','::1','2026-11-09 14:41:03'),(47,1,'DELETE_CLASSROOM_MODULE','Deleted class module subject ID 15','::1','2026-11-09 14:41:22'),(48,1,'DELETE_CLASSROOM_MODULE','Deleted class module subject ID 16','::1','2026-11-09 14:41:25'),(49,1,'DELETE_CLASSROOM_MODULE','Deleted class module subject ID 14','::1','2026-11-09 14:41:28');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'bi-folder',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'PDF Notes','bi-file-earmark-pdf-fill','Comprehensive lecture slide notes and handouts'),(2,'Past Papers','bi-archive-fill','Past semester and national examination papers'),(3,'Model Papers','bi-file-text-fill','Practice and model question papers with guide answers'),(4,'Tutorials & Lab Sheets','bi-journal-code','Practical exercises, worksheets and lab manuals'),(5,'Diagrams & Images','bi-file-image-fill','System architecture, network diagrams, ERD charts'),(6,'YouTube Video Lessons','bi-youtube','Curated YouTube video tutorials & online lectures'),(7,'Other Resources & Links','bi-link-45deg','Reference links, documentation, online tools & repos');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_sessions`
--

DROP TABLE IF EXISTS `class_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `session_title` varchar(200) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT 'Monday',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `session_type` enum('physical','online','hybrid') DEFAULT 'physical',
  `hall_or_link` varchar(255) DEFAULT 'ICT Lab 01',
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `class_sessions_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_sessions_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_sessions_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_sessions`
--

LOCK TABLES `class_sessions` WRITE;
/*!40000 ALTER TABLE `class_sessions` DISABLE KEYS */;
INSERT INTO `class_sessions` VALUES (1,2,1,1,'Database Systems & ER Modeling (Morning Session)','Monday','09:00:00','12:00:00','physical','ICT Lab 01 (Baddegama)','scheduled','Teacher 01 Morning Session'),(2,3,1,2,'PHP Web Development & LMS Architecture (Afternoon Session)','Monday','13:00:00','16:00:00','physical','ICT Lab 02 (Baddegama)','scheduled','Teacher 02 Afternoon Session'),(3,2,1,3,'Network Security & Firewalls (Morning Session)','Wednesday','09:00:00','12:00:00','physical','Hardware & Network Lab','scheduled','Teacher 01 Morning Session');
/*!40000 ALTER TABLE `class_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_subjects`
--

DROP TABLE IF EXISTS `class_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_class_subject` (`class_id`,`subject_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_subjects`
--

LOCK TABLES `class_subjects` WRITE;
/*!40000 ALTER TABLE `class_subjects` DISABLE KEYS */;
INSERT INTO `class_subjects` VALUES (1,1,1,'2026-11-08 06:32:47'),(2,1,2,'2026-11-08 06:32:47'),(3,1,3,'2026-11-08 06:32:47'),(5,1,6,'2026-11-08 06:32:47'),(6,1,7,'2026-11-08 06:32:47'),(7,1,8,'2026-11-08 06:32:47'),(8,1,13,'2026-11-08 06:32:47'),(9,1,14,'2026-11-13 14:00:00'),(10,1,15,'2026-11-13 14:00:00'),(11,1,16,'2026-11-13 14:00:00'),(12,1,17,'2026-11-13 14:00:00'),(13,1,18,'2026-11-13 14:00:00'),(14,1,19,'2026-11-13 14:00:00'),(15,1,20,'2026-11-13 14:00:00'),(16,1,21,'2026-11-13 14:00:00'),(17,1,22,'2026-11-13 14:00:00'),(18,1,23,'2026-11-13 14:00:00'),(19,1,24,'2026-11-13 14:00:00'),(20,1,25,'2026-11-13 14:00:00'),(21,1,26,'2026-11-13 14:00:00'),(22,1,27,'2026-11-13 14:00:00'),(23,1,28,'2026-11-13 14:00:00'),(24,1,29,'2026-11-13 14:00:00'),(25,1,30,'2026-11-13 14:00:00'),(26,1,31,'2026-11-13 14:00:00');
/*!40000 ALTER TABLE `class_subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `class_name` varchar(150) NOT NULL,
  `academic_year` varchar(50) DEFAULT '2026/2027',
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,1,'NVTI Baddegama ICT L5 - Batch 2026','2026/2027','active','2026-11-06 11:57:15');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(30) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `nvq_level` varchar(20) DEFAULT 'Level 5',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`course_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'K72T001.2','National Diploma - Information & Communication Technology','NVQ Level 5','The NVQ ICT Level 5 qualification develops advanced skills in information and communication technology, focusing on system management and professional IT solutions. It prepares learners for higher-level technical roles and further studies in the ICT field.','active','2026-11-06 11:57:15');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `curriculum_modules`
--

DROP TABLE IF EXISTS `curriculum_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curriculum_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_no` int(11) NOT NULL,
  `module_code` varchar(30) NOT NULL,
  `module_title` varchar(255) NOT NULL,
  `semester` int(11) NOT NULL DEFAULT 1,
  `module_type` enum('Technical','Employability') NOT NULL DEFAULT 'Technical',
  `related_units` varchar(255) NOT NULL DEFAULT '',
  `curriculum_page` varchar(30) DEFAULT '',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_code` (`module_code`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `curriculum_modules`
--

LOCK TABLES `curriculum_modules` WRITE;
/*!40000 ALTER TABLE `curriculum_modules` DISABLE KEYS */;
INSERT INTO `curriculum_modules` VALUES (1,1,'K72T001M01','User requirements for software solution',1,'Technical','K72T001U01','01','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(2,2,'K72T001M02','System analysis and design',1,'Technical','K72T001U02','03','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(3,3,'K72T001M03','Mathematics For Computing',1,'Technical','All Units','05','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(4,4,'K72T001M04','Digital Electronics and Computer Architecture',1,'Technical','All Units','07','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(5,5,'K72T001M05','Object Oriented Programming Concepts',1,'Technical','K72T001U04, K72T001U07','09','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(6,6,'K72T001M06','Data Structures and Algorithms',1,'Technical','K72T001U04, K72T001U07','11','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(7,7,'K72T001M07','Web Site Design and Hosting',1,'Technical','K72T001U06, K72T001U07','14','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(8,8,'K72T001M08','Creative design for print media',1,'Technical','K72T001U05','17','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(9,9,'EMPM02','Workplace Communications Management',1,'Employability','EMPU02','20','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(10,10,'K72T001M09','Software Testing & Quality Assurance',2,'Technical','K72T001U04, K72T001U07','22','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(11,11,'K72T001M10','Web Application Development',2,'Technical','K72T001U07','24','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(12,12,'K72T001M11','Database Management Systems',2,'Technical','K72T001U03','27','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(13,13,'K72T001M12','Multimedia production',2,'Technical','K72T001U06, K72T001U07','30','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(14,14,'K72T001M13','Computer Networks',2,'Technical','K72T001U08','34','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(15,15,'K72T001M14','Cyber Security and Cloud computing',2,'Technical','K72T001U08','37','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(16,16,'K72T001M15','Developing Mobile App',2,'Technical','K72T001U09','39','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(17,17,'EMPM01','Workplace Information Management',2,'Employability','EMPU01','42','active','2026-11-06 15:23:46','2026-11-06 15:23:46'),(18,18,'EMPM03','Planning and scheduling work at workplace',2,'Employability','EMPU03','45','active','2026-11-06 15:23:46','2026-11-06 15:23:46');
/*!40000 ALTER TABLE `curriculum_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `enrolled_at` datetime DEFAULT current_timestamp(),
  `status` enum('active','completed','dropped') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_enrollment` (`student_id`,`class_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (1,4,1,'2026-11-06 11:57:15','active');
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_papers`
--

DROP TABLE IF EXISTS `exam_papers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_papers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `semester` int(11) NOT NULL DEFAULT 1,
  `module_code` varchar(30) NOT NULL,
  `subject_title` varchar(200) NOT NULL,
  `paper_type` enum('past_paper','model_paper','marking_scheme') DEFAULT 'past_paper',
  `exam_year` int(11) NOT NULL DEFAULT 2025,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT 'sample_past_paper.pdf',
  `original_filename` varchar(255) DEFAULT 'NVTI_Past_Paper.pdf',
  `file_size_bytes` bigint(20) DEFAULT 1850000,
  `download_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_semester` (`semester`),
  KEY `idx_module_code` (`module_code`),
  KEY `idx_paper_type` (`paper_type`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_papers`
--

LOCK TABLES `exam_papers` WRITE;
/*!40000 ALTER TABLE `exam_papers` DISABLE KEYS */;
INSERT INTO `exam_papers` VALUES (1,1,'K72T001M01','User requirements for software solution','past_paper',2025,'2025 Semester 1 Final Exam - User Requirements','Official TVEC past exam paper with question paper and marking criteria.','sample_past_paper.pdf','K72T001M01_Exam_2025.pdf',2150000,0,'2026-11-06 22:42:13'),(2,1,'K72T001M02','System analysis and design','past_paper',2025,'2025 Semester 1 Final Exam - Systems Analysis & Design','Official TVEC examination paper including DFD, Use Case, and SRS questions.','sample_past_paper.pdf','K72T001M02_Exam_2025.pdf',2420000,0,'2026-11-06 22:42:13'),(3,1,'K72T001M03','Mathematics For Computing','past_paper',2025,'2025 Semester 1 Final Exam - Mathematics for Computing','Past paper covering number conversions, two\'s complement, and statistics.','sample_past_paper.pdf','K72T001M03_Exam_2025.pdf',1820000,0,'2026-11-06 22:42:13'),(4,1,'K72T001M04','Digital Electronics and Computer Architecture','past_paper',2025,'2025 Semester 1 Final Exam - Computer Architecture','National diploma examination paper on logic gates, K-maps, and CPU cycles.','sample_past_paper.pdf','K72T001M04_Exam_2025.pdf',1940000,0,'2026-11-06 22:42:13'),(5,1,'K72T001M05','Object Oriented Programming Concepts','past_paper',2025,'2025 Semester 1 Final Exam - OOP Concepts','Complete examination paper with code tracing, OOP design questions, and Java/C++ syntax.','sample_past_paper.pdf','K72T001M05_Exam_2025.pdf',2650000,0,'2026-11-06 22:42:13'),(6,1,'K72T001M06','Data Structures and Algorithms','past_paper',2025,'2025 Semester 1 Final Exam - Data Structures & Algorithms','Covers stacks, queues, linked lists, quick sort, and algorithm complexity.','sample_past_paper.pdf','K72T001M06_Exam_2025.pdf',2510000,0,'2026-11-06 22:42:13'),(7,1,'K72T001M07','Web Site Design and Hosting','past_paper',2025,'2025 Semester 1 Final Exam - Web Site Design & Hosting','Past paper on HTML5, CSS Grid/Flexbox, JavaScript events, and DNS hosting.','sample_past_paper.pdf','K72T001M07_Exam_2025.pdf',2280000,0,'2026-11-06 22:42:13'),(8,1,'K72T001M08','Creative design for print media','past_paper',2025,'2025 Semester 1 Final Exam - Creative Design for Print','Theory paper on prepress, CMYK vs RGB, typography, and image resolution.','sample_past_paper.pdf','K72T001M08_Exam_2025.pdf',1790000,0,'2026-11-06 22:42:13'),(9,1,'K72T001M05','Object Oriented Programming Concepts','model_paper',2026,'2026 Model Examination Paper - OOP Concepts','Practice model paper prepared by NVTI senior lecturers with model answers.','sample_past_paper.pdf','K72T001M05_Model_2026.pdf',1950000,0,'2026-11-06 22:42:13'),(10,1,'K72T001M06','Data Structures and Algorithms','model_paper',2026,'2026 Model Paper - Data Structures & Algorithms','Curriculum aligned practice questions for NVQ Level 5 upcoming final exam.','sample_past_paper.pdf','K72T001M06_Model_2026.pdf',2100000,0,'2026-11-06 22:42:13'),(11,1,'K72T001M07','Web Site Design and Hosting','marking_scheme',2025,'2025 Web Design & Hosting Official Marking Scheme','Comprehensive marking guide and points distribution scheme.','sample_past_paper.pdf','K72T001M07_Scheme_2025.pdf',1400000,0,'2026-11-06 22:42:13'),(12,2,'K72T001M09','Software Testing & Quality Assurance','past_paper',2025,'2025 Semester 2 Final Exam - Software Testing & QA','Official examination paper covering STLC, test case design, and black-box testing.','sample_past_paper.pdf','K72T001M09_Exam_2025.pdf',2300000,0,'2026-11-06 22:42:13'),(13,2,'K72T001M10','Web Application Development','past_paper',2025,'2025 Semester 2 Final Exam - Web Application Development','Past paper on PHP/MySQL full stack, AJAX, session management, and web security.','sample_past_paper.pdf','K72T001M10_Exam_2025.pdf',2580000,0,'2026-11-06 22:42:13'),(14,2,'K72T001M11','Database Management Systems','past_paper',2025,'2025 Semester 2 Final Exam - Database Management Systems','Official TVEC examination paper covering 3NF normalization, ERD mapping, and SQL.','sample_past_paper.pdf','K72T001M11_Exam_2025.pdf',2480000,0,'2026-11-06 22:42:13'),(15,2,'K72T001M12','Multimedia production','past_paper',2025,'2025 Semester 2 Final Exam - Multimedia Production','Written examination paper on video editing, audio synchronization, and visual projection.','sample_past_paper.pdf','K72T001M12_Exam_2025.pdf',1980000,0,'2026-11-06 22:42:13'),(16,2,'K72T001M13','Computer Networks','past_paper',2025,'2025 Semester 2 Final Exam - Computer Networks','Official paper with subnetting calculations, OSI/TCP layers, and router/switch CLI.','sample_past_paper.pdf','K72T001M13_Exam_2025.pdf',2720000,0,'2026-11-06 22:42:13'),(17,2,'K72T001M14','Cyber Security and Cloud computing','past_paper',2025,'2025 Semester 2 Final Exam - Cyber Security & Cloud','Covers CIA triad, cryptography, cloud models (IaaS/PaaS/SaaS), and network tools.','sample_past_paper.pdf','K72T001M14_Exam_2025.pdf',2340000,0,'2026-11-06 22:42:13'),(18,2,'K72T001M15','Developing Mobile App','past_paper',2025,'2025 Semester 2 Final Exam - Developing Mobile Apps','Examination paper on mobile app lifecycle, Kotlin/Swift, SQLite, and REST APIs.','sample_past_paper.pdf','K72T001M15_Exam_2025.pdf',2210000,0,'2026-11-06 22:42:13'),(19,2,'K72T001M11','Database Management Systems','model_paper',2026,'2026 Model Examination Paper - Database Management Systems','Practice paper with comprehensive 3NF scenario questions and SQL schema tasks.','sample_past_paper.pdf','K72T001M11_Model_2026.pdf',2100000,0,'2026-11-06 22:42:13'),(20,2,'K72T001M13','Computer Networks','model_paper',2026,'2026 Model Paper - Computer Networks & Subnetting','Includes step-by-step subnetting questions and network topology troubleshooting.','sample_past_paper.pdf','K72T001M13_Model_2026.pdf',2240000,0,'2026-11-06 22:42:13'),(21,2,'K72T001M11','Database Management Systems','marking_scheme',2025,'2025 Database Systems Official Marking Scheme','Detailed marking breakdown and sample answers for ERD & Normalization questions.','sample_past_paper.pdf','K72T001M11_Scheme_2025.pdf',1650000,0,'2026-11-06 22:42:13'),(22,1,'K72T001M02','System analysis and design','model_paper',2026,'2026 Semester 1 Final Exam - System analysis and design','demoooooooooooooooo','1842fec90793440ce834e6076d59dc3d_1794126145.pdf',NULL,873399,1,'2026-11-08 13:52:25');
/*!40000 ALTER TABLE `exam_papers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_logs`
--

DROP TABLE IF EXISTS `login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_logs`
--

LOCK TABLES `login_logs` WRITE;
/*!40000 ALTER TABLE `login_logs` DISABLE KEYS */;
INSERT INTO `login_logs` VALUES (1,'admin@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Antigravity/2.29.0 Chrome/152.0.7977.130 Electron/44.5.1 Safari/537.36','2026-11-06 12:15:06'),(2,'admin@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-06 14:12:55'),(3,'admin@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-06 15:49:03'),(4,'admin@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-06 15:59:26'),(5,'200125409876','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-06 19:46:00'),(6,'200125409876','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-06 20:11:39'),(7,'teacher2@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-06 20:36:31'),(8,'student@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-06 20:40:21'),(9,'200125409876','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-06 21:49:43'),(10,'admin@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-08 10:11:36'),(11,'student@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-08 15:16:31'),(12,'admin@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-08 15:16:53'),(13,'student@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-09 09:00:23'),(14,'admin@nvti.edu.lk','success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-09 10:33:45');
/*!40000 ALTER TABLE `login_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `material_type` enum('file','youtube','url') NOT NULL DEFAULT 'file',
  `file_path` varchar(255) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `external_url` text DEFAULT NULL,
  `file_size_bytes` bigint(20) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `materials_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `materials_ibfk_4` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materials`
--

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
INSERT INTO `materials` VALUES (1,2,1,1,1,'Introduction to Relational Databases & Normalization','Complete lecture presentation slides for 3NF Normalization.','file','sample_db_notes.pdf','Lecture_01_Normalization.pdf',NULL,1542000,1,'2026-11-06 11:57:15'),(2,3,1,2,6,'Complete PHP 8 & MySQL Tutorial Series','Video guide covering PDO, prepared statements, and session security.','youtube',NULL,NULL,'https://www.youtube.com/watch?v=OK_JCtrrv-c',0,0,'2026-11-06 11:57:15'),(3,3,1,2,7,'PHP Official PDO Manual & Security Guidelines','Official documentation link for secure database queries.','url',NULL,NULL,'https://www.php.net/manual/en/book.pdo.php',0,0,'2026-11-06 11:57:15'),(4,2,1,1,2,'2025 Semester 1 Past Examination Paper','Past paper for Database Systems including marking scheme.','file','sample_past_paper.pdf','ICT501_Past_Paper_2025.pdf',NULL,2300000,0,'2026-11-06 11:57:15'),(5,2,1,1,1,'Introduction to Database','Fundamental concepts of Relational Database Management Systems, DBMS Architecture, and Database Engine models.','file','db_intro_lecture_01.pdf','Introduction_to_Database_Systems_Lecture01.pdf',NULL,1524288,0,'2026-09-04 09:30:00'),(6,2,1,1,1,'Normalization','Comprehensive lecture notes on Functional Dependencies, 1NF, 2NF, 3NF and BCNF with solved industry normalization scenarios.','file','database_normalization_guide.pdf','Database_Normalization_1NF_2NF_3NF_Guide.pdf',NULL,2457600,0,'2026-09-08 10:15:00'),(7,2,1,1,1,'EER Note','Extended Entity Relationship (EER) modeling: Specialization, Generalization, Aggregation and Category mapping rules.','file','eer_extended_modeling_notes.pdf','Enhanced_ERD_Specialization_Generalization_Notes.pdf',NULL,1843200,0,'2026-09-10 11:00:00'),(8,2,1,8,1,'PHP 8 & MySQL Web Application Development Guide','Complete guide on PDO database connection, prepared statements, and MVC structure for Web Design & Hosting.','file','php_mysql_pdo_guide.pdf','PHP8_PDO_Secure_Database_Web_Development.pdf',NULL,3145728,0,'2026-09-05 14:00:00'),(9,2,1,2,1,'Software Development & OOP Design Principles','Core object-oriented programming concepts: Encapsulation, Inheritance, Polymorphism, and Abstraction.','file','oop_software_development_principles.pdf','OOP_Software_Development_Core_Principles.pdf',NULL,2097152,0,'2026-09-06 11:30:00');
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_topics`
--

DROP TABLE IF EXISTS `module_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `module_code` varchar(30) NOT NULL,
  `topic_no` int(11) NOT NULL,
  `topic_title` varchar(255) NOT NULL,
  `allocated_hours` varchar(30) DEFAULT '06 hrs',
  `sub_topics` text DEFAULT NULL,
  `order_no` int(11) DEFAULT 1,
  `status` enum('active','completed') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_module_id` (`module_id`),
  KEY `idx_module_code` (`module_code`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_topics`
--

LOCK TABLES `module_topics` WRITE;
/*!40000 ALTER TABLE `module_topics` DISABLE KEYS */;
INSERT INTO `module_topics` VALUES (1,1,'K72T001M01',1,'Introduction to Business Processes & Stakeholders','15 hrs','Business domains (Telecom, Hospitality, Healthcare, Banking, Education); Stakeholder roles and responsibilities; Software solutions for business efficiency improvement.',1,'active','2026-11-06 22:42:13'),(2,1,'K72T001M01',2,'Business Process Study & Workflow Modeling','15 hrs','Study current system & manual workflows; Role matrices; Business Process Re-engineering (BPR) options.',2,'active','2026-11-06 22:42:13'),(3,1,'K72T001M01',3,'Requirements Gathering Techniques','12 hrs','Document reviews; Stakeholder interviews; Questionnaires & surveys; Field observation techniques.',3,'active','2026-11-06 22:42:13'),(4,1,'K72T001M01',4,'Requirements Analysis & Scope Definition','12 hrs','Project scope boundaries & limitations; Functional requirements; Non-functional requirements (Performance, Security, Reliability).',4,'active','2026-11-06 22:42:13'),(5,1,'K72T001M01',5,'Feasibility Study & Project Reporting','06 hrs','Technical feasibility; Economical & ROI analysis; Operational & organizational feasibility; Legal compliance.',5,'active','2026-11-06 22:42:13'),(6,2,'K72T001M02',1,'Systems Analysis Techniques & Diagramming','20 hrs','Data Flow Diagrams (Context, Level 0, Level 1 DFD); Use Case Diagrams and Actor specifications; Entity Relationship (ER) and Extended ER.',1,'active','2026-11-06 22:42:13'),(7,2,'K72T001M02',2,'System Design & UI/UX Principles','20 hrs','Program flowcharts & structured pseudocode; UI/UX wireframes and interaction design.',2,'active','2026-11-06 22:42:13'),(8,2,'K72T001M02',3,'SRS Documentation & Test Case Development','20 hrs','Software Requirements Specification (SRS) industry standard structure; Test cases based on functional design specifications.',3,'active','2026-11-06 22:42:13'),(9,3,'K72T001M03',1,'Number Systems & Base Conversions','07 hrs','Positional number systems; Conversions between Decimal, Binary, Octal, Hexadecimal; Fractional numbers.',1,'active','2026-11-06 22:42:13'),(10,3,'K72T001M03',2,'Computer Arithmetic & Data Representation','06 hrs','Binary addition & subtraction; One\'s and Two\'s complements; Fixed-point & IEEE floating-point representations.',2,'active','2026-11-06 22:42:13'),(11,3,'K72T001M03',3,'Set Theory, Statistics & Encoding Standards','07 hrs','Venn diagrams & Set operations; Mean, Median, Mode, Standard Deviation; BCD, EBCDIC, ASCII, and Unicode standards.',3,'active','2026-11-06 22:42:13'),(12,4,'K72T001M04',1,'Logic Gates & Boolean Algebra','12 hrs','Basic logic gates (AND, OR, NOT); Universal gates (NAND, NOR); Boolean algebra laws & Karnaugh maps (K-Maps) minimization.',1,'active','2026-11-06 22:42:13'),(13,4,'K72T001M04',2,'Computer Organization & Processor Architecture','21 hrs','ALU, Registers & Control Unit; Instruction Set Architecture (ISA); Fetch-Decode-Execute program cycle; Cache memory hierarchy & Pipelining.',2,'active','2026-11-06 22:42:13'),(14,4,'K72T001M04',3,'Von-Neumann Architecture & Modern Paradigms','09 hrs','Von-Neumann bottlenecks; Multicore systems; Introduction to Quantum Computing & Neural hardware.',3,'active','2026-11-06 22:42:13'),(15,5,'K72T001M05',1,'Programming Paradigms & Algorithm Design','24 hrs','Procedural vs Object-Oriented paradigms; Flowcharts, pseudocode & modular programming.',1,'active','2026-11-06 22:42:13'),(16,5,'K72T001M05',2,'Classes, Objects, Attributes & Methods','15 hrs','Real-world modeling; Object instantiation; Constructors & Destructors; Method overloading; Garbage collection.',2,'active','2026-11-06 22:42:13'),(17,5,'K72T001M05',3,'Core OOP Pillars (Encapsulation, Inheritance, Polymorphism)','15 hrs','Access modifiers (public, private, protected); Getters & Setters; Class hierarchies (Single, Multilevel); Method overriding; Dynamic binding.',3,'active','2026-11-06 22:42:13'),(18,5,'K72T001M05',4,'Abstraction, Interfaces & Object Relationships','06 hrs','Abstract classes; Interface contracts; Association, Aggregation, and Composition relationships.',4,'active','2026-11-06 22:42:13'),(19,6,'K72T001M06',1,'Software Engineering & Programming Environments','12 hrs','SDLC models (Waterfall, Agile, Scrum); IDE configurations; Execution workflow.',1,'active','2026-11-06 22:42:13'),(20,6,'K72T001M06',2,'Fundamental Data Structures','12 hrs','Arrays, Records; Stacks (LIFO) & Queues (FIFO); Singly and Doubly Linked Lists; Trees, Hash Maps, Tuples, Sets.',2,'active','2026-11-06 22:42:13'),(21,6,'K72T001M06',3,'Sorting & Searching Algorithms','12 hrs','Bubble Sort, Selection Sort, Insertion Sort, Quick Sort; Linear Search, Binary Search; Time complexity basics.',3,'active','2026-11-06 22:42:13'),(22,6,'K72T001M06',4,'Exception Handling, DB Connectivity & Git','18 hrs','Try-Catch-Finally exception management; Database connection handling; Git version control (clone, commit, push, branch).',4,'active','2026-11-06 22:42:13'),(23,7,'K72T001M07',1,'UX/UI Design, Wireframing & Web Standards','09 hrs','User experience principles; Wireframe prototyping; Client-server architecture and HTTP/HTTPS protocols.',1,'active','2026-11-06 22:42:13'),(24,7,'K72T001M07',2,'HTML5 Semantic Structure & Forms','15 hrs','Semantic tags (header, nav, section, article, footer); Accessible forms, multimedia audio/video embeds.',2,'active','2026-11-06 22:42:13'),(25,7,'K72T001M07',3,'CSS3 Styling, Flexbox & Grid Systems','12 hrs','Selectors, box model, CSS Grid, Flexbox layouts; Media queries for responsive layouts.',3,'active','2026-11-06 22:42:13'),(26,7,'K72T001M07',4,'JavaScript Interactivity & DOM Manipulation','09 hrs','Variables, loops, functions; Event listeners; DOM element selection and dynamic manipulation.',4,'active','2026-11-06 22:42:13'),(27,7,'K72T001M07',5,'Web Hosting, DNS Configuration & C-Panel Deployment','09 hrs','Domain registration; DNS record management (A, CNAME, MX); C-Panel file management & FTP deployment.',5,'active','2026-11-06 22:42:13'),(28,8,'K72T001M08',1,'Graphic Design Fundamentals & Typography','06 hrs','Gestalt theory, Golden ratio; Font categories, kerning, leading, hierarchy.',1,'active','2026-11-06 22:42:13'),(29,8,'K72T001M08',2,'Vector Graphics & Raster Image Editing','13 hrs','Photoshop & Illustrator toolsets; Layer masks, clipping paths, color grading; Vector illustrations.',2,'active','2026-11-06 22:42:13'),(30,8,'K72T001M08',3,'Prepress Production & Color Modes','06 hrs','RGB vs CMYK; Resolution (DPI/PPI); Bleed, slug, trim marks; Offset, digital, and screen printing techniques.',3,'active','2026-11-06 22:42:13'),(31,9,'EMPM02',1,'Workplace Communication Channels & Protocols','20 hrs','Formal vs informal channels; Verbal, non-verbal, and technical written communication; Overcoming barriers.',1,'active','2026-11-06 22:42:13'),(32,9,'EMPM02',2,'Technical Reporting & Meeting Facilitation','25 hrs','Minutes of meetings, executive summaries, oral presentations, business correspondence.',2,'active','2026-11-06 22:42:13'),(33,10,'K72T001M09',1,'Software Testing Fundamentals & STLC','12 hrs','Quality Assurance (QA) vs Quality Control (QC); Software Testing Life Cycle (STLC); Verification vs Validation.',1,'active','2026-11-06 22:42:13'),(34,10,'K72T001M09',2,'Testing Levels & Test Types','09 hrs','Unit, Integration, System, Acceptance (UAT); Functional vs Non-functional; Regression testing.',2,'active','2026-11-06 22:42:13'),(35,10,'K72T001M09',3,'Test Design Techniques & Documentation','18 hrs','Black-box (Boundary Value Analysis, Equivalence Partitioning); White-box (Statement, Branch coverage); Test plan, test cases, defect logging.',3,'active','2026-11-06 22:42:13'),(36,10,'K72T001M09',4,'Test Automation & Security Assessments','06 hrs','Automated testing frameworks; Security vulnerability checks; Performance & load test overview.',4,'active','2026-11-06 22:42:13'),(37,11,'K72T001M10',1,'Front-End Architecture & Asynchronous JS','15 hrs','Advanced DOM handling, AJAX, Fetch API, JSON data interchange; Single Page Application (SPA) concepts.',1,'active','2026-11-06 22:42:13'),(38,11,'K72T001M10',2,'Server-Side Back-End Programming (PHP/Node.js)','18 hrs','MVC architecture; Request routing; Session management; Form validation & data sanitization.',2,'active','2026-11-06 22:42:13'),(39,11,'K72T001M10',3,'Database Integration & CRUD Operations','12 hrs','PDO database abstraction, prepared statements, transactional safety; CRUD workflows.',3,'active','2026-11-06 22:42:13'),(40,11,'K72T001M10',4,'Application Security & Payment Integration','09 hrs','XSS, CSRF, SQL Injection mitigation; SSL/TLS certificates; Payment gateway API integration.',4,'active','2026-11-06 22:42:13'),(41,12,'K72T001M11',1,'Database Architectures & Data Models','09 hrs','File systems vs DBMS; 1-tier, 2-tier, 3-tier architectures; Relational vs NoSQL vs Cloud data storage.',1,'active','2026-11-06 22:42:13'),(42,12,'K72T001M11',2,'Conceptual Data Modeling (ERD & EERD)','18 hrs','Strong & Weak entities; Attribute classification; Cardinality & Optionality constraints; EERD specialization & generalization; Schema conversion.',2,'active','2026-11-06 22:42:13'),(43,12,'K72T001M11',3,'Relational Database Normalization','12 hrs','Data redundancy anomalies (Insert, Update, Delete); Functional dependencies; First Normal Form (1NF), Second Normal Form (2NF), Third Normal Form (3NF).',3,'active','2026-11-06 22:42:13'),(44,12,'K72T001M11',4,'SQL Implementation (DDL, DML, DCL)','15 hrs','Table definitions, Alter, Drop; Insert, Update, Delete, Select queries; Inner, Left, Right joins; Subqueries, Aggregate functions.',4,'active','2026-11-06 22:42:13'),(45,12,'K72T001M11',5,'Stored Procedures, Triggers & Administration','06 hrs','Stored procedures, parameterized functions, triggers; Backup & restoration; Role-based user privileges.',5,'active','2026-11-06 22:42:13'),(46,13,'K72T001M12',1,'Multimedia Concepts & Design Principles','05 hrs','C.R.A.P design rules; Interactive multimedia applications; Storyboarding & scriptwriting; Online safety acts.',1,'active','2026-11-06 22:42:13'),(47,13,'K72T001M12',2,'Audio Engineering & Post-Production','06 hrs','Sound theory, Foley sound effects, microphone recording, audio mixing, multitrack synchronization.',2,'active','2026-11-06 22:42:13'),(48,13,'K72T001M12',3,'Video Production, Editing & Color Grading','09 hrs','Camera framing, 3-point lighting; Adobe Premiere non-linear editing, transitions, audio-video sync.',3,'active','2026-11-06 22:42:13'),(49,13,'K72T001M12',4,'2D Motion Graphics, Animation & LED Wall Projection','08 hrs','Keyframe animation in After Effects; Title motion design; LED wall projection & live vision mapping (Vmix, Resolume).',4,'active','2026-11-06 22:42:13'),(50,14,'K72T001M13',1,'Network Fundamentals, Topologies & Media','12 hrs','LAN, MAN, WAN; Star, Mesh, Bus topologies; Twisted pair, Fiber optic, Coaxial cabling standards.',1,'active','2026-11-06 22:42:13'),(51,14,'K72T001M13',2,'OSI 7-Layer Architecture & TCP/IP Protocol Suite','06 hrs','Encapsulation & Decapsulation; TCP, UDP, IP, ICMP, ARP; Port numbers & socket communication.',2,'active','2026-11-06 22:42:13'),(52,14,'K72T001M13',3,'IP Addressing, Subnetting & Supernetting','15 hrs','IPv4 address classes; Classless Inter-Domain Routing (CIDR); Subnet mask calculation; Introduction to IPv6.',3,'active','2026-11-06 22:42:13'),(53,14,'K72T001M13',4,'Network Device Configuration & Infrastructure Services','15 hrs','Switch VLAN configuration; Router static & dynamic routing (RIP, OSPF); DHCP, DNS, and Proxy server configuration.',4,'active','2026-11-06 22:42:13'),(54,15,'K72T001M14',1,'Cybersecurity Fundamentals & Threat Landscapes','08 hrs','CIA Triad (Confidentiality, Integrity, Availability); Malware, ransomware, phishing, social engineering attacks.',1,'active','2026-11-06 22:42:13'),(55,15,'K72T001M14',2,'Cryptography & Security Measures','08 hrs','Symmetric & Asymmetric encryption (AES, RSA); Hashing algorithms (SHA); Digital signatures and SSL/TLS certificates.',2,'active','2026-11-06 22:42:13'),(56,15,'K72T001M14',3,'Cloud Computing Architectures & Security','12 hrs','Cloud delivery models (IaaS, PaaS, SaaS); Deployment models (Public, Private, Hybrid); AWS, Azure, GCP fundamentals; Kali Linux & Wireshark analysis.',3,'active','2026-11-06 22:42:13'),(57,16,'K72T001M15',1,'Mobile App Architecture & Native Development','15 hrs','Android & iOS ecosystems; Native Android architecture (Activities, Fragments, Views); Kotlin language basics.',1,'active','2026-11-06 22:42:13'),(58,16,'K72T001M15',2,'Cross-Platform Frameworks (Flutter / React Native)','10 hrs','Component lifecycles; State management; Flutter widgets; Responsive mobile styling.',2,'active','2026-11-06 22:42:13'),(59,16,'K72T001M15',3,'Local Storage, REST API Integration & Deployment','12 hrs','Room / SQLite database; RESTful API consumption; App testing, code signing & Play Store publishing.',3,'active','2026-11-06 22:42:13'),(60,17,'EMPM01',1,'Information Systems & Data Documentation','25 hrs','Information documentation standards; Manuals, enterprise procedures; Legal data compliance.',1,'active','2026-11-06 22:42:13'),(61,18,'EMPM03',1,'Work Scheduling & Task Management','25 hrs','Work Breakdown Structure (WBS); Gantt charts; Resource allocation; Performance tracking.',1,'active','2026-11-06 22:42:13');
/*!40000 ALTER TABLE `module_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_documents`
--

DROP TABLE IF EXISTS `student_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `doc_type` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_size_bytes` int(11) NOT NULL DEFAULT 0,
  `file_mime` varchar(100) NOT NULL DEFAULT 'application/pdf',
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `verified_by_user_id` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_doc` (`student_id`,`doc_type`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_documents`
--

LOCK TABLES `student_documents` WRITE;
/*!40000 ALTER TABLE `student_documents` DISABLE KEYS */;
INSERT INTO `student_documents` VALUES (1,4,'ol_certificate','G.C.E. O/L Certificate','C:\\xampp\\htdocs\\nvti baddegama diploma\\storage\\uploads\\documents\\7badb852ac62f7fdec346017298c16c8_1794131205.jpg','images.jpg',25937,'image/jpeg','verified',NULL,1,'2026-11-08 15:17:08','2026-11-08 09:46:45','2026-11-08 09:47:08');
/*!40000 ALTER TABLE `student_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_profiles`
--

DROP TABLE IF EXISTS `student_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `student_code` varchar(50) NOT NULL,
  `mis_number` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `nic` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `guardian_name` varchar(150) DEFAULT NULL,
  `guardian_address` text DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `student_code` (`student_code`),
  CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_profiles`
--

LOCK TABLES `student_profiles` WRITE;
/*!40000 ALTER TABLE `student_profiles` DISABLE KEYS */;
INSERT INTO `student_profiles` VALUES (1,4,'NVTI-ST-2026-001','MIS/ICT/2026/001','0771234567','0771234567','200125409876','No. 45, Galle Road, Baddegama','0712233445','K. Sunimal Madushanka','No. 45, Galle Road, Baddegama','0719876543',1,1),(2,5,'NVTI-ST-2026-002','MIS/ICT/2026/002','0772345678','0772345678','200358901234','Baddegama, Galle',NULL,'0',NULL,'0771234567',1,1),(3,6,'NVTI-ST-2026-003','MIS/ICT/2026/003','0713456789','0713456789','200214567890','Baddegama, Galle',NULL,'0',NULL,'0771234567',1,1),(4,7,'NVTI-ST-2026-004','MIS/ICT/2026/004','0764567890','0764567890','200378901234','Baddegama, Galle',NULL,'0',NULL,'0771234567',1,1),(5,8,'NVTI-ST-2026-005','MIS/ICT/2026/005','0785678901','0785678901','200123456789','Baddegama, Galle',NULL,'0',NULL,'0771234567',1,1),(6,9,'NVTI-ST-2026-006','MIS/ICT/2026/006','0706789012','0706789012','200389012345','Baddegama, Galle',NULL,'0',NULL,'0771234567',1,1),(7,10,'NVTI-ST-2026-007','MIS/ICT/2026/007','0727890123','0727890123','200234567890','Baddegama, Galle',NULL,'0',NULL,'0771234567',1,1),(8,11,'NVTI-ST-2026-008','MIS/ICT/2026/008','0758901234','0758901234','200390123456','Baddegama, Galle',NULL,'0',NULL,'0771234567',1,1);
/*!40000 ALTER TABLE `student_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(30) NOT NULL,
  `subject_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `color_theme` varchar(50) DEFAULT 'blue',
  `icon` varchar(50) DEFAULT NULL,
  `credits` int(11) DEFAULT 4,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject_code` (`subject_code`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,'K72T001U03','Implement database management system','Relational database modeling, ER diagrams, normalization, SQL implementation, and administration.','blue','bi-database-fill-gear',4,'active','2026-11-08 06:32:47'),(2,'K72T001U04','Develop and test software','Full-stack software development with PHP, OOP principles, testing methodologies, and deployment.','dark','bi-code-slash',4,'active','2026-11-08 06:32:47'),(3,'K72T001U08','Manage computer networks with security measures and cloud environments','Network administration, TCP/IP, routing, network security protocols, firewall config, and cloud environments.','indigo','bi-hdd-network-fill',3,'active','2026-11-08 06:32:47'),(5,'K72T001U02','Analyze requirements and design functionality of software solution','System architecture, UML diagramming, functional specifications, and software UI/UX design.','blue',NULL,4,'active','2026-11-08 06:32:47'),(6,'K72T001U05','Create design for print media','Graphic design principles, vector & raster artwork, typography, and prepress production.','teal','bi-brush-fill',3,'active','2026-11-08 06:32:47'),(7,'K72T001U06','Design and develop multimedia','Audio/video editing, animation, multimedia authoring, and interactive digital content.','maroon','bi-camera-reels-fill',3,'active','2026-11-08 06:32:47'),(8,'K72T001U07','Design, develop and host websites','Web design standards, HTML5, CSS3, JavaScript, web hosting, domain DNS, and server setup.','charcoal','bi-globe-americas',4,'active','2026-11-08 06:32:47'),(9,'K72T001U09','Design and develop mobile app','Mobile application design patterns, responsive frameworks, and native/hybrid app development.','blue',NULL,4,'active','2026-11-08 06:32:47'),(10,'EMPU01','Manage workplace information','Information gathering, storage, data protection, and records management in professional settings.','blue',NULL,2,'active','2026-11-08 06:32:47'),(11,'EMPU02','Manage workplace communication','Workplace oral, written, presentation skills, technical report writing, and meeting facilitation.','blue',NULL,2,'active','2026-11-08 06:32:47'),(12,'EMPU03','Plan work to be performed in the workplace','Work scheduling, project task planning, prioritization, time management, and team collaboration.','blue',NULL,2,'active','2026-11-08 06:32:47'),(13,'K72T001U10','Cloud Computing & DevOps','Cloud Architecture, Docker & AWS Systems','teal','bi-cloud-check-fill',4,'active','2026-11-08 06:32:47'),(14,'K72T001M01','User requirements for software solution','Semester 1 • Technical Module','blue','bi-file-earmark-check-fill',4,'active','2026-11-13 14:00:00'),(15,'K72T001M02','System analysis and design','Semester 1 • Technical Module','indigo','bi-diagram-3-fill',4,'active','2026-11-13 14:00:00'),(16,'K72T001M03','Mathematics For Computing','Semester 1 • Technical Module','teal','bi-calculator-fill',4,'active','2026-11-13 14:00:00'),(17,'K72T001M04','Digital Electronics and Computer Architecture','Semester 1 • Technical Module','dark','bi-cpu-fill',4,'active','2026-11-13 14:00:00'),(18,'K72T001M05','Object Oriented Programming Concepts','Semester 1 • Technical Module','maroon','bi-code-slash',4,'active','2026-11-13 14:00:00'),(19,'K72T001M06','Data Structures and Algorithms','Semester 1 • Technical Module','blue','bi-bezier2',4,'active','2026-11-13 14:00:00'),(20,'K72T001M07','Web Site Design and Hosting','Semester 1 • Technical Module','charcoal','bi-globe-americas',4,'active','2026-11-13 14:00:00'),(21,'K72T001M08','Creative design for print media','Semester 1 • Technical Module','amber','bi-brush-fill',4,'active','2026-11-13 14:00:00'),(22,'EMPM02','Workplace Communications Management','Semester 1 • Employability Module','purple','bi-chat-quote-fill',4,'active','2026-11-13 14:00:00'),(23,'K72T001M09','Software Testing & Quality Assurance','Semester 2 • Technical Module','dark','bi-check-all',4,'active','2026-11-13 14:00:00'),(24,'K72T001M10','Web Application Development','Semester 2 • Technical Module','blue','bi-laptop-fill',4,'active','2026-11-13 14:00:00'),(25,'K72T001M11','Database Management Systems','Semester 2 • Technical Module','indigo','bi-database-fill-gear',4,'active','2026-11-13 14:00:00'),(26,'K72T001M12','Multimedia production','Semester 2 • Technical Module','maroon','bi-camera-reels-fill',4,'active','2026-11-13 14:00:00'),(27,'K72T001M13','Computer Networks','Semester 2 • Technical Module','teal','bi-hdd-network-fill',4,'active','2026-11-13 14:00:00'),(28,'K72T001M14','Cyber Security and Cloud computing','Semester 2 • Technical Module','dark','bi-shield-lock-fill',4,'active','2026-11-13 14:00:00'),(29,'K72T001M15','Developing Mobile App','Semester 2 • Technical Module','blue','bi-phone-fill',4,'active','2026-11-13 14:00:00'),(30,'EMPM01','Workplace Information Management','Semester 2 • Employability Module','purple','bi-info-circle-fill',4,'active','2026-11-13 14:00:00'),(31,'EMPM03','Planning and scheduling work at workplace','Semester 2 • Employability Module','amber','bi-calendar-range-fill',4,'active','2026-11-13 14:00:00');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `student_comment` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `is_late` tinyint(1) DEFAULT 0,
  `marks` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL,
  `status` enum('submitted','graded','resubmit_required') DEFAULT 'submitted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_student_assignment` (`assignment_id`,`student_id`),
  KEY `student_id` (`student_id`),
  KEY `graded_by` (`graded_by`),
  CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submissions`
--

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_assignments`
--

DROP TABLE IF EXISTS `teacher_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `class_subject_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_teacher_subject` (`teacher_id`,`class_subject_id`),
  KEY `class_subject_id` (`class_subject_id`),
  CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_assignments`
--

LOCK TABLES `teacher_assignments` WRITE;
/*!40000 ALTER TABLE `teacher_assignments` DISABLE KEYS */;
INSERT INTO `teacher_assignments` VALUES (1,2,1,'2026-11-06 11:57:15'),(2,3,3,'2026-11-06 11:57:15'),(3,2,2,'2026-11-06 11:57:15'),(5,2,5,'2026-11-08 10:49:52'),(6,2,6,'2026-11-08 10:49:52'),(7,2,7,'2026-11-08 10:49:52'),(8,2,8,'2026-11-08 11:03:16'),(9,2,9,'2026-11-13 14:00:00'),(10,2,10,'2026-11-13 14:00:00'),(11,2,11,'2026-11-13 14:00:00'),(12,2,12,'2026-11-13 14:00:00'),(13,2,13,'2026-11-13 14:00:00'),(14,2,14,'2026-11-13 14:00:00'),(15,2,15,'2026-11-13 14:00:00'),(16,2,16,'2026-11-13 14:00:00'),(17,2,17,'2026-11-13 14:00:00'),(18,2,18,'2026-11-13 14:00:00'),(19,2,19,'2026-11-13 14:00:00'),(20,2,20,'2026-11-13 14:00:00'),(21,2,21,'2026-11-13 14:00:00'),(22,2,22,'2026-11-13 14:00:00'),(23,2,23,'2026-11-13 14:00:00'),(24,2,24,'2026-11-13 14:00:00'),(25,2,25,'2026-11-13 14:00:00'),(26,2,26,'2026-11-13 14:00:00');
/*!40000 ALTER TABLE `teacher_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_profiles`
--

DROP TABLE IF EXISTS `teacher_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `teacher_code` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `preferred_slot` varchar(50) DEFAULT 'Morning (09:00 - 12:00)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `teacher_code` (`teacher_code`),
  CONSTRAINT `teacher_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_profiles`
--

LOCK TABLES `teacher_profiles` WRITE;
/*!40000 ALTER TABLE `teacher_profiles` DISABLE KEYS */;
INSERT INTO `teacher_profiles` VALUES (1,2,'NVTI-TCH-001','0771234567','Senior Instructor & NVQ Level 5 Assessor','Database Management Systems & Computer Networks','Morning (09:00 - 12:00)'),(2,3,'NVTI-TCH-002','0719876543','Senior Instructor (Software Development)','Software Engineering & Web Application Development','Afternoon (13:00 - 16:00)');
/*!40000 ALTER TABLE `teacher_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL DEFAULT 'student',
  `status` enum('active','pending','suspended') NOT NULL DEFAULT 'active',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `email_2` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'NVTI System Administrator','admin@nvti.edu.lk','$2y$10$sSByduCO59jN3m3I8m.4gebf0Fme0tt2F1ddPC6L5kiAxOJB36jaS','admin','active',NULL,'2026-11-06 11:57:15','2026-11-06 11:57:15'),(2,'U.G.P.K. Nishantha','teacher1@nvti.edu.lk','$2y$10$vvNdpPl3f5zgycPrUOyQ0.H3iYdIleDbnAxwYD2./ZZMwimCuRhka','teacher','active','assets/images/teachers/nishantha.jpg','2026-11-06 11:57:15','2026-11-06 14:38:30'),(3,'Gayan sir','teacher2@nvti.edu.lk','$2y$10$vvNdpPl3f5zgycPrUOyQ0.H3iYdIleDbnAxwYD2./ZZMwimCuRhka','teacher','active','assets/images/teachers/7eb65fc39521977aa4f8_1793961294.jpg','2026-11-06 11:57:15','2026-11-06 16:04:54'),(4,'Kasun Madushanka (Student)','student@nvti.edu.lk','$2y$10$Dl968L.fnbOts/69YsKpweo3/d0exi2K.ZFzH4h0iJGrF6WoS46i2','student','active','assets/images/students/b45c033a78cbc4ae5bb6_1793976133.jpg','2026-11-06 11:57:15','2026-11-06 20:12:13'),(5,'Nimali Priyadarshani','nimali@nvti.edu.lk','$2y$10$M8ZbXFp9opKHK/pOC/ysnOaw3xCjEkQAIu7r.16eD5oFsoQreqwem','student','active',NULL,'2026-11-06 20:29:57','2026-11-06 20:29:57'),(6,'Chathura Lakshan','chathura@nvti.edu.lk','$2y$10$M8ZbXFp9opKHK/pOC/ysnOaw3xCjEkQAIu7r.16eD5oFsoQreqwem','student','active',NULL,'2026-11-06 20:29:57','2026-11-06 20:29:57'),(7,'Dilani Sewwandi','dilani@nvti.edu.lk','$2y$10$M8ZbXFp9opKHK/pOC/ysnOaw3xCjEkQAIu7r.16eD5oFsoQreqwem','student','active',NULL,'2026-11-06 20:29:57','2026-11-06 20:29:57'),(8,'Tharindu Sandaruwan','tharindu@nvti.edu.lk','$2y$10$M8ZbXFp9opKHK/pOC/ysnOaw3xCjEkQAIu7r.16eD5oFsoQreqwem','student','active',NULL,'2026-11-06 20:29:57','2026-11-06 20:29:57'),(9,'Ishara Hansamali','ishara@nvti.edu.lk','$2y$10$M8ZbXFp9opKHK/pOC/ysnOaw3xCjEkQAIu7r.16eD5oFsoQreqwem','student','active',NULL,'2026-11-06 20:29:57','2026-11-06 20:29:57'),(10,'Kaveen De Silva','kaveen@nvti.edu.lk','$2y$10$M8ZbXFp9opKHK/pOC/ysnOaw3xCjEkQAIu7r.16eD5oFsoQreqwem','student','active',NULL,'2026-11-06 20:29:57','2026-11-06 20:29:57'),(11,'Rashmi Madhubhashini','rashmi@nvti.edu.lk','$2y$10$M8ZbXFp9opKHK/pOC/ysnOaw3xCjEkQAIu7r.16eD5oFsoQreqwem','student','active',NULL,'2026-11-06 20:29:57','2026-11-08 10:12:04'),(14,'U.G.P.K. Nishantha','nishantha@nvti.edu.lk','$2y$10$bwl6d/tSFpbx4XzHrrDnR.bMJXBgv3l2DQJcS4Q62H4zW6wygqARm','admin','active',NULL,'2026-11-09 15:08:41','2026-11-09 15:08:41'),(15,'Gayan Sir','gayan@nvti.edu.lk','$2y$10$bwl6d/tSFpbx4XzHrrDnR.bMJXBgv3l2DQJcS4Q62H4zW6wygqARm','admin','active',NULL,'2026-11-09 15:08:41','2026-11-09 15:08:41');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 19:40:47
