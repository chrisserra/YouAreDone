
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
  `confidence_score` tinyint(4) NOT NULL DEFAULT 3,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`candidate_flag_source_id`),
  UNIQUE KEY `uq_candidate_flag_sources_flag_source` (`candidate_flag_id`,`source_id`),
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
  UNIQUE KEY `uq_candidate_flags_candidate_flag` (`candidate_id`,`flag_id`),
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
  `source_priority` tinyint(4) NOT NULL DEFAULT 2,
  `supports_field` varchar(100) DEFAULT NULL,
  `source_name` varchar(255) NOT NULL,
  `source_title` varchar(255) NOT NULL DEFAULT 'Candidate Source',
  `source_url` varchar(1000) NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `retrieved_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_checked_at` datetime DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `raw_content` mediumtext DEFAULT NULL,
  `content_hash` char(64) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`source_id`),
  UNIQUE KEY `uq_candidate_sources_candidate_field_url` (`candidate_id`,`supports_field`,`source_url`) USING HASH,
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
  `primary_party_code` varchar(10) DEFAULT NULL,
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
  UNIQUE KEY `uq_elections_race_type_party` (`race_id`,`election_type_id`,`primary_party_code`),
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
DROP TABLE IF EXISTS `vw_candidate_flag_coverage_gaps`;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_flag_coverage_gaps`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_candidate_flag_coverage_gaps` AS SELECT 
 1 AS `candidate_id`,
 1 AS `full_name`,
 1 AS `source_count`,
 1 AS `flag_count`,
 1 AS `flag_source_count`,
 1 AS `supported_field_count`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_candidate_flag_coverage_gaps_smart`;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_flag_coverage_gaps_smart`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_candidate_flag_coverage_gaps_smart` AS SELECT 
 1 AS `candidate_id`,
 1 AS `full_name`,
 1 AS `source_count`,
 1 AS `flag_count`,
 1 AS `flag_source_count`,
 1 AS `flag_eligible_supported_field_count`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_candidate_rankings_ui`;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_rankings_ui`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_candidate_rankings_ui` AS SELECT 
 1 AS `candidate_id`,
 1 AS `full_name`,
 1 AS `green_flag_count`,
 1 AS `red_flag_count`,
 1 AS `total_flag_count`,
 1 AS `score_final`,
 1 AS `rank_position`,
 1 AS `score_tier`,
 1 AS `badge_class`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_candidate_scores`;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_candidate_scores` AS SELECT 
 1 AS `candidate_id`,
 1 AS `full_name`,
 1 AS `green_flag_count`,
 1 AS `red_flag_count`,
 1 AS `score_total`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_candidate_scores_final`;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores_final`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_candidate_scores_final` AS SELECT 
 1 AS `candidate_id`,
 1 AS `full_name`,
 1 AS `green_flag_count`,
 1 AS `red_flag_count`,
 1 AS `total_flag_count`,
 1 AS `score_total`,
 1 AS `score_normalized`,
 1 AS `score_hybrid`,
 1 AS `weighted_green_score`,
 1 AS `weighted_red_score`,
 1 AS `weighted_score_total`,
 1 AS `score_final`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_candidate_scores_hybrid`;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores_hybrid`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_candidate_scores_hybrid` AS SELECT 
 1 AS `candidate_id`,
 1 AS `full_name`,
 1 AS `green_flag_count`,
 1 AS `red_flag_count`,
 1 AS `total_flag_count`,
 1 AS `score_total`,
 1 AS `score_normalized`,
 1 AS `score_hybrid`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_candidate_scores_normalized`;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores_normalized`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_candidate_scores_normalized` AS SELECT 
 1 AS `candidate_id`,
 1 AS `full_name`,
 1 AS `green_flag_count`,
 1 AS `red_flag_count`,
 1 AS `total_flag_count`,
 1 AS `score_total`,
 1 AS `score_normalized`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_candidate_scores_weighted`;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores_weighted`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_candidate_scores_weighted` AS SELECT 
 1 AS `candidate_id`,
 1 AS `full_name`,
 1 AS `weighted_green_score`,
 1 AS `weighted_red_score`,
 1 AS `total_flag_count`,
 1 AS `weighted_score_total`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_candidate_source_refresh_queue`;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_source_refresh_queue`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_candidate_source_refresh_queue` AS SELECT 
 1 AS `source_id`,
 1 AS `candidate_id`,
 1 AS `full_name`,
 1 AS `source_type`,
 1 AS `source_priority`,
 1 AS `supports_field`,
 1 AS `source_name`,
 1 AS `source_title`,
 1 AS `source_url`,
 1 AS `last_checked_at`,
 1 AS `days_since_checked`*/;
SET character_set_client = @saved_cs_client;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_flag_coverage_gaps`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cserraco_master`@`24.16.58.27` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_candidate_flag_coverage_gaps` AS select `c`.`candidate_id` AS `candidate_id`,`c`.`full_name` AS `full_name`,count(distinct `cs`.`source_id`) AS `source_count`,count(distinct `cf`.`candidate_flag_id`) AS `flag_count`,count(distinct `cfs`.`candidate_flag_source_id`) AS `flag_source_count`,count(distinct `cs`.`supports_field`) AS `supported_field_count` from (((`candidates` `c` join `candidate_sources` `cs` on(`cs`.`candidate_id` = `c`.`candidate_id`)) left join `candidate_flags` `cf` on(`cf`.`candidate_id` = `c`.`candidate_id`)) left join `candidate_flag_sources` `cfs` on(`cfs`.`candidate_flag_id` = `cf`.`candidate_flag_id`)) group by `c`.`candidate_id`,`c`.`full_name` having count(distinct `cs`.`supports_field`) > count(distinct `cf`.`candidate_flag_id`) or count(distinct `cf`.`candidate_flag_id`) > count(distinct `cfs`.`candidate_flag_source_id`) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_flag_coverage_gaps_smart`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cserraco_master`@`24.16.58.27` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_candidate_flag_coverage_gaps_smart` AS select `c`.`candidate_id` AS `candidate_id`,`c`.`full_name` AS `full_name`,count(distinct `cs`.`source_id`) AS `source_count`,count(distinct `cf`.`candidate_flag_id`) AS `flag_count`,count(distinct `cfs`.`candidate_flag_source_id`) AS `flag_source_count`,count(distinct case when `cs`.`supports_field` not in ('campaign_website','candidate_profile','ballot_status') then `cs`.`supports_field` end) AS `flag_eligible_supported_field_count` from (((`candidates` `c` join `candidate_sources` `cs` on(`cs`.`candidate_id` = `c`.`candidate_id`)) left join `candidate_flags` `cf` on(`cf`.`candidate_id` = `c`.`candidate_id`)) left join `candidate_flag_sources` `cfs` on(`cfs`.`candidate_flag_id` = `cf`.`candidate_flag_id`)) group by `c`.`candidate_id`,`c`.`full_name` having count(distinct case when `cs`.`supports_field` not in ('campaign_website','candidate_profile','ballot_status') then `cs`.`supports_field` end) > count(distinct `cf`.`candidate_flag_id`) or count(distinct `cf`.`candidate_flag_id`) > count(distinct `cfs`.`candidate_flag_source_id`) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_rankings_ui`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cserraco_master`@`24.16.58.27` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_candidate_rankings_ui` AS select `ranked`.`candidate_id` AS `candidate_id`,`ranked`.`full_name` AS `full_name`,`ranked`.`green_flag_count` AS `green_flag_count`,`ranked`.`red_flag_count` AS `red_flag_count`,`ranked`.`total_flag_count` AS `total_flag_count`,`ranked`.`score_final` AS `score_final`,`ranked`.`rank_position` AS `rank_position`,case when `ranked`.`total_flag_count` = 0 then 'Unscored' when `ranked`.`score_final` >= 5 then 'Top Progressive' when `ranked`.`score_final` >= 2 then 'Strong' when `ranked`.`score_final` > -2 then 'Mixed Record' when `ranked`.`score_final` > -5 then 'Concerning' else 'Primary This Candidate' end AS `score_tier`,case when `ranked`.`total_flag_count` = 0 then 'tier-unscored' when `ranked`.`score_final` >= 5 then 'tier-top' when `ranked`.`score_final` >= 2 then 'tier-strong' when `ranked`.`score_final` > -2 then 'tier-mixed' when `ranked`.`score_final` > -5 then 'tier-concerning' else 'tier-primary' end AS `badge_class` from (select `v`.`candidate_id` AS `candidate_id`,`v`.`full_name` AS `full_name`,`v`.`green_flag_count` AS `green_flag_count`,`v`.`red_flag_count` AS `red_flag_count`,`v`.`total_flag_count` AS `total_flag_count`,`v`.`score_total` AS `score_total`,`v`.`score_normalized` AS `score_normalized`,`v`.`score_hybrid` AS `score_hybrid`,`v`.`weighted_green_score` AS `weighted_green_score`,`v`.`weighted_red_score` AS `weighted_red_score`,`v`.`weighted_score_total` AS `weighted_score_total`,`v`.`score_final` AS `score_final`,row_number() over ( order by `v`.`score_final` desc,`v`.`total_flag_count` desc,`v`.`green_flag_count` desc,`v`.`candidate_id`) AS `rank_position` from `vw_candidate_scores_final` `v`) `ranked` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cserraco_master`@`24.16.58.27` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_candidate_scores` AS select `c`.`candidate_id` AS `candidate_id`,`c`.`full_name` AS `full_name`,count(distinct case when `f`.`flag_color` = 'green' then `cf`.`flag_id` end) AS `green_flag_count`,count(distinct case when `f`.`flag_color` = 'red' then `cf`.`flag_id` end) AS `red_flag_count`,count(distinct case when `f`.`flag_color` = 'green' then `cf`.`flag_id` end) * 3 - count(distinct case when `f`.`flag_color` = 'red' then `cf`.`flag_id` end) * 3 AS `score_total` from ((`candidates` `c` left join `candidate_flags` `cf` on(`cf`.`candidate_id` = `c`.`candidate_id`)) left join `flags` `f` on(`f`.`flag_id` = `cf`.`flag_id`)) group by `c`.`candidate_id`,`c`.`full_name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores_final`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cserraco_master`@`24.16.58.27` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_candidate_scores_final` AS select `h`.`candidate_id` AS `candidate_id`,`h`.`full_name` AS `full_name`,`h`.`green_flag_count` AS `green_flag_count`,`h`.`red_flag_count` AS `red_flag_count`,`h`.`total_flag_count` AS `total_flag_count`,`h`.`score_total` AS `score_total`,`h`.`score_normalized` AS `score_normalized`,`h`.`score_hybrid` AS `score_hybrid`,`w`.`weighted_green_score` AS `weighted_green_score`,`w`.`weighted_red_score` AS `weighted_red_score`,`w`.`weighted_score_total` AS `weighted_score_total`,case when `h`.`total_flag_count` = 0 then 0 else sign(`w`.`weighted_score_total`) * abs(`h`.`score_hybrid`) * abs(`w`.`weighted_score_total` / `h`.`total_flag_count`) end AS `score_final` from (`vw_candidate_scores_hybrid` `h` join `vw_candidate_scores_weighted` `w` on(`w`.`candidate_id` = `h`.`candidate_id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores_hybrid`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cserraco_master`@`24.16.58.27` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_candidate_scores_hybrid` AS select `s`.`candidate_id` AS `candidate_id`,`s`.`full_name` AS `full_name`,`s`.`green_flag_count` AS `green_flag_count`,`s`.`red_flag_count` AS `red_flag_count`,`s`.`total_flag_count` AS `total_flag_count`,`s`.`score_total` AS `score_total`,`s`.`score_normalized` AS `score_normalized`,case when `s`.`total_flag_count` = 0 then 0 else `s`.`score_normalized` * log(1 + `s`.`total_flag_count`) end AS `score_hybrid` from `vw_candidate_scores_normalized` `s` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores_normalized`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cserraco_master`@`24.16.58.27` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_candidate_scores_normalized` AS select `c`.`candidate_id` AS `candidate_id`,`c`.`full_name` AS `full_name`,count(distinct case when `f`.`flag_color` = 'green' then `cf`.`flag_id` end) AS `green_flag_count`,count(distinct case when `f`.`flag_color` = 'red' then `cf`.`flag_id` end) AS `red_flag_count`,count(distinct `cf`.`flag_id`) AS `total_flag_count`,count(distinct case when `f`.`flag_color` = 'green' then `cf`.`flag_id` end) * 3 - count(distinct case when `f`.`flag_color` = 'red' then `cf`.`flag_id` end) * 3 AS `score_total`,case when count(distinct `cf`.`flag_id`) = 0 then 0 else (count(distinct case when `f`.`flag_color` = 'green' then `cf`.`flag_id` end) * 3 - count(distinct case when `f`.`flag_color` = 'red' then `cf`.`flag_id` end) * 3) / count(distinct `cf`.`flag_id`) end AS `score_normalized` from ((`candidates` `c` left join `candidate_flags` `cf` on(`cf`.`candidate_id` = `c`.`candidate_id`)) left join `flags` `f` on(`f`.`flag_id` = `cf`.`flag_id`)) group by `c`.`candidate_id`,`c`.`full_name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_scores_weighted`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cserraco_master`@`24.16.58.27` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_candidate_scores_weighted` AS select `c`.`candidate_id` AS `candidate_id`,`c`.`full_name` AS `full_name`,sum(case when `f`.`flag_color` = 'green' then coalesce(`cfs`.`confidence_score`,3) / 3 else 0 end) AS `weighted_green_score`,sum(case when `f`.`flag_color` = 'red' then coalesce(`cfs`.`confidence_score`,3) / 3 else 0 end) AS `weighted_red_score`,count(distinct `cf`.`flag_id`) AS `total_flag_count`,sum(case when `f`.`flag_color` = 'green' then coalesce(`cfs`.`confidence_score`,3) / 3 else 0 end) - sum(case when `f`.`flag_color` = 'red' then coalesce(`cfs`.`confidence_score`,3) / 3 else 0 end) AS `weighted_score_total` from (((`candidates` `c` left join `candidate_flags` `cf` on(`cf`.`candidate_id` = `c`.`candidate_id`)) left join `flags` `f` on(`f`.`flag_id` = `cf`.`flag_id`)) left join `candidate_flag_sources` `cfs` on(`cfs`.`candidate_flag_id` = `cf`.`candidate_flag_id`)) group by `c`.`candidate_id`,`c`.`full_name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_candidate_source_refresh_queue`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cserraco_master`@`24.16.58.27` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_candidate_source_refresh_queue` AS select `cs`.`source_id` AS `source_id`,`cs`.`candidate_id` AS `candidate_id`,`c`.`full_name` AS `full_name`,`cs`.`source_type` AS `source_type`,`cs`.`source_priority` AS `source_priority`,`cs`.`supports_field` AS `supports_field`,`cs`.`source_name` AS `source_name`,`cs`.`source_title` AS `source_title`,`cs`.`source_url` AS `source_url`,`cs`.`last_checked_at` AS `last_checked_at`,timestampdiff(DAY,`cs`.`last_checked_at`,current_timestamp()) AS `days_since_checked` from (`candidate_sources` `cs` join `candidates` `c` on(`c`.`candidate_id` = `cs`.`candidate_id`)) where timestampdiff(DAY,`cs`.`last_checked_at`,current_timestamp()) >= case when `cs`.`source_priority` = 1 then 30 when `cs`.`source_priority` = 2 then 60 when `cs`.`source_priority` = 3 then 120 when `cs`.`source_priority` = 4 then 180 end */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

