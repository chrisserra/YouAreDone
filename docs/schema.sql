
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `candidate_flag_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidate_flag_sources` (
  `candidate_flag_source_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_flag_id` bigint(20) unsigned NOT NULL,
  `source_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`candidate_flag_source_id`),
  UNIQUE KEY `uq_candidate_flag_sources` (`candidate_flag_id`,`source_id`),
  KEY `ix_candidate_flag_sources_candidate_flag` (`candidate_flag_id`),
  KEY `ix_candidate_flag_sources_source` (`source_id`),
  CONSTRAINT `fk_candidate_flag_sources_candidate_flag` FOREIGN KEY (`candidate_flag_id`) REFERENCES `candidate_flags` (`candidate_flag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_candidate_flag_sources_source` FOREIGN KEY (`source_id`) REFERENCES `candidate_sources` (`source_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `candidate_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidate_flags` (
  `candidate_flag_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `flag_id` int(10) unsigned NOT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `weight_override` decimal(8,2) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`candidate_flag_id`),
  UNIQUE KEY `uq_candidate_flags` (`candidate_id`,`flag_id`),
  KEY `ix_candidate_flags_candidate` (`candidate_id`,`is_active`),
  KEY `ix_candidate_flags_flag` (`flag_id`,`is_active`),
  KEY `ix_candidate_flags_source` (`source_id`),
  CONSTRAINT `fk_cf_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cf_flag` FOREIGN KEY (`flag_id`) REFERENCES `flags` (`flag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cf_source` FOREIGN KEY (`source_id`) REFERENCES `candidate_sources` (`source_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `candidate_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidate_sources` (
  `source_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `election_id` bigint(20) unsigned DEFAULT NULL,
  `source_type` enum('official','campaign','news','ballotpedia','fec','state_filing','social','other') NOT NULL DEFAULT 'other',
  `supports_field` varchar(100) DEFAULT NULL,
  `source_name` varchar(255) NOT NULL,
  `source_title` varchar(500) DEFAULT NULL,
  `source_url` varchar(1000) NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `retrieved_at` datetime NOT NULL DEFAULT current_timestamp(),
  `excerpt` text DEFAULT NULL,
  `raw_content` mediumtext DEFAULT NULL,
  `content_hash` char(64) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`source_id`),
  KEY `ix_candidate_sources_candidate` (`candidate_id`,`retrieved_at`),
  KEY `ix_candidate_sources_election` (`election_id`),
  KEY `ix_candidate_sources_type` (`source_type`),
  CONSTRAINT `fk_candidate_sources_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_candidate_sources_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `candidate_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidate_updates` (
  `update_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `election_id` bigint(20) unsigned DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `update_type` enum('announcement','filing','endorsement','policy','controversy','campaign_status','result','general') NOT NULL DEFAULT 'general',
  `headline` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `source_date` date DEFAULT NULL,
  `sort_date` date NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`update_id`),
  KEY `fk_candidate_updates_source` (`source_id`),
  KEY `ix_candidate_updates_candidate_date` (`candidate_id`,`sort_date`),
  KEY `ix_candidate_updates_election_date` (`election_id`,`sort_date`),
  KEY `ix_candidate_updates_type` (`update_type`),
  CONSTRAINT `fk_candidate_updates_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_candidate_updates_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_candidate_updates_source` FOREIGN KEY (`source_id`) REFERENCES `candidate_sources` (`source_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidates` (
  `candidate_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `preferred_name` varchar(120) DEFAULT NULL,
  `party_code` varchar(20) DEFAULT NULL,
  `party_name` varchar(100) DEFAULT NULL,
  `website_url` varchar(500) DEFAULT NULL,
  `ballotpedia_url` varchar(500) DEFAULT NULL,
  `wikipedia_url` varchar(500) DEFAULT NULL,
  `x_url` varchar(500) DEFAULT NULL,
  `instagram_url` varchar(500) DEFAULT NULL,
  `facebook_url` varchar(500) DEFAULT NULL,
  `youtube_url` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `short_bio` text DEFAULT NULL,
  `summary_public` text DEFAULT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `score_total` decimal(8,2) NOT NULL DEFAULT 0.00,
  `green_flag_count` int(10) unsigned NOT NULL DEFAULT 0,
  `red_flag_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`candidate_id`),
  UNIQUE KEY `uq_candidates_slug` (`slug`),
  KEY `ix_candidates_name` (`last_name`,`first_name`),
  KEY `ix_candidates_party` (`party_code`),
  KEY `ix_candidates_status_score` (`status`,`score_total`),
  KEY `ix_candidates_full_name` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `election_candidate_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_candidate_flags` (
  `election_candidate_flag_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) unsigned NOT NULL,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `flag_id` int(10) unsigned NOT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `weight_override` decimal(8,2) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`election_candidate_flag_id`),
  UNIQUE KEY `uq_election_candidate_flags` (`election_id`,`candidate_id`,`flag_id`),
  KEY `ix_ecf_candidate` (`candidate_id`,`is_active`),
  KEY `ix_ecf_election` (`election_id`,`is_active`),
  KEY `ix_ecf_flag` (`flag_id`,`is_active`),
  KEY `ix_ecf_source` (`source_id`),
  CONSTRAINT `fk_ecf_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ecf_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ecf_flag` FOREIGN KEY (`flag_id`) REFERENCES `flags` (`flag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ecf_source` FOREIGN KEY (`source_id`) REFERENCES `candidate_sources` (`source_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `election_candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_candidates` (
  `election_candidate_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) unsigned NOT NULL,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `ballot_name` varchar(150) DEFAULT NULL,
  `party_code` varchar(20) DEFAULT NULL,
  `filing_status` enum('filed','qualified','withdrawn','removed','rumored','unknown') NOT NULL DEFAULT 'unknown',
  `ballot_status` enum('on_ballot','not_on_ballot','pending','unknown') NOT NULL DEFAULT 'unknown',
  `result_status` enum('pending','advanced','eliminated','won','lost','withdrawn','unknown') NOT NULL DEFAULT 'pending',
  `is_incumbent` tinyint(1) NOT NULL DEFAULT 0,
  `is_major_candidate` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `vote_count` bigint(20) unsigned DEFAULT NULL,
  `vote_percent` decimal(7,3) DEFAULT NULL,
  `notes_public` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`election_candidate_id`),
  UNIQUE KEY `uq_election_candidates` (`election_id`,`candidate_id`),
  KEY `ix_election_candidates_candidate` (`candidate_id`),
  KEY `ix_election_candidates_election_sort` (`election_id`,`sort_order`),
  KEY `ix_election_candidates_result` (`result_status`),
  CONSTRAINT `fk_election_candidates_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_election_candidates_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `election_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_types` (
  `election_type_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(60) NOT NULL,
  `name` varchar(100) NOT NULL,
  `decides_nominee` tinyint(1) NOT NULL DEFAULT 0,
  `decides_winner` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`election_type_id`),
  UNIQUE KEY `uq_election_types_slug` (`slug`),
  UNIQUE KEY `uq_election_types_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `elections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elections` (
  `election_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `race_id` bigint(20) unsigned NOT NULL,
  `event_id` bigint(20) unsigned NOT NULL,
  `election_type_id` smallint(5) unsigned NOT NULL,
  `election_date` date NOT NULL,
  `round_number` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') NOT NULL DEFAULT 'upcoming',
  `filing_deadline` date DEFAULT NULL,
  `early_voting_start` date DEFAULT NULL,
  `early_voting_end` date DEFAULT NULL,
  `certification_date` date DEFAULT NULL,
  `notes_public` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`election_id`),
  UNIQUE KEY `uq_elections_slug` (`slug`),
  UNIQUE KEY `uq_elections_race_type_date_round` (`race_id`,`election_type_id`,`election_date`,`round_number`),
  KEY `fk_elections_type` (`election_type_id`),
  KEY `ix_elections_date` (`election_date`),
  KEY `ix_elections_race_status` (`race_id`,`status`),
  KEY `ix_elections_event` (`event_id`),
  KEY `ix_elections_event_status` (`event_id`,`status`),
  CONSTRAINT `fk_elections_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_elections_race` FOREIGN KEY (`race_id`) REFERENCES `races` (`race_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_elections_type` FOREIGN KEY (`election_type_id`) REFERENCES `election_types` (`election_type_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `event_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `election_year` smallint(5) unsigned NOT NULL,
  `state_code` char(2) NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `state_slug` varchar(100) NOT NULL,
  `event_type_slug` varchar(60) NOT NULL,
  `event_type_name` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') NOT NULL DEFAULT 'upcoming',
  `notes_public` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `uq_events_state_type_date` (`state_code`,`event_type_slug`,`event_date`),
  UNIQUE KEY `uq_events_slug` (`slug`),
  KEY `ix_events_date` (`event_date`),
  KEY `ix_events_state_year` (`state_code`,`election_year`),
  KEY `ix_events_state_slug_year` (`state_slug`,`election_year`),
  KEY `ix_events_status_date` (`status`,`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `flag_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flag_categories` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flags` (
  `flag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `flag_color` enum('green','red') NOT NULL,
  `default_weight` decimal(8,2) NOT NULL DEFAULT 1.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`flag_id`),
  UNIQUE KEY `uq_flags_slug` (`slug`),
  KEY `ix_flags_active_sort` (`is_active`,`sort_order`,`name`),
  KEY `fk_flags_category` (`category_id`),
  CONSTRAINT `fk_flags_category` FOREIGN KEY (`category_id`) REFERENCES `flag_categories` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offices` (
  `office_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` enum('federal','state') NOT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`office_id`),
  UNIQUE KEY `uq_offices_slug` (`slug`),
  UNIQUE KEY `uq_offices_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `races`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `races` (
  `race_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `office_id` smallint(5) unsigned NOT NULL,
  `election_year` smallint(5) unsigned NOT NULL,
  `state_code` char(2) NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `state_slug` varchar(100) NOT NULL,
  `district_type` enum('statewide','congressional_district') NOT NULL DEFAULT 'statewide',
  `district_number` smallint(5) unsigned NOT NULL DEFAULT 0,
  `district_label` varchar(100) DEFAULT NULL,
  `seat_label` varchar(150) DEFAULT NULL,
  `is_special` tinyint(1) NOT NULL DEFAULT 0,
  `race_slug` varchar(255) NOT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `notes_public` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`race_id`),
  UNIQUE KEY `uq_races_slug` (`race_slug`),
  UNIQUE KEY `uq_races_office_year_state_district_special` (`office_id`,`election_year`,`state_code`,`district_type`,`district_number`,`is_special`),
  KEY `ix_races_state_year` (`state_code`,`election_year`),
  KEY `ix_races_state_slug_year` (`state_slug`,`election_year`),
  KEY `ix_races_office_year` (`office_id`,`election_year`),
  KEY `ix_races_status` (`status`),
  CONSTRAINT `fk_races_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

