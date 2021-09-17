# ************************************************************
# Sequel Ace SQL dump
# Version 3038
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: 127.0.01 (MySQL 8.0.26)
# Database: laravel
# Generation Time: 2021-09-17 21:23:31 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table circles
# ------------------------------------------------------------

LOCK TABLES `circles` WRITE;
/*!40000 ALTER TABLE `circles` DISABLE KEYS */;

INSERT INTO `circles` (`id`, `name`, `created_at`, `updated_at`, `protocol_id`, `token_name`, `team_sel_text`, `alloc_text`, `telegram_id`, `logo`, `vouching`, `min_vouches`, `nomination_days_limit`, `vouching_text`, `discord_webhook`, `default_opt_in`, `team_selection`, `only_giver_vouch`)
VALUES
	(1,'testcircle','2021-09-17 21:16:31','2021-09-17 21:16:31',1,'GIVE',NULL,NULL,NULL,NULL,0,2,14,NULL,NULL,0,1,1);

/*!40000 ALTER TABLE `circles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table profiles
# ------------------------------------------------------------

LOCK TABLES `profiles` WRITE;
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;

INSERT INTO `profiles` (`id`, `avatar`, `background`, `skills`, `bio`, `telegram_username`, `discord_username`, `twitter_username`, `github_username`, `medium_username`, `website`, `address`, `created_at`, `updated_at`, `admin_view`, `ann_power`, `chat_id`)
VALUES
	(1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266','2021-09-17 21:17:20','2021-09-17 21:17:20',0,0,NULL);

/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table protocols
# ------------------------------------------------------------

LOCK TABLES `protocols` WRITE;
/*!40000 ALTER TABLE `protocols` DISABLE KEYS */;

INSERT INTO `protocols` (`id`, `name`, `created_at`, `updated_at`, `telegram_id`)
VALUES
	(1,'testprotocol','2021-09-17 21:16:13','2021-09-17 21:16:20',NULL);

/*!40000 ALTER TABLE `protocols` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `name`, `address`, `give_token_received`, `give_token_remaining`, `role`, `non_receiver`, `circle_id`, `created_at`, `updated_at`, `bio`, `epoch_first_visit`, `non_giver`, `deleted_at`, `starting_tokens`, `fixed_non_receiver`)
VALUES
	(1,'','0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266',0,100,1,1,1,'2021-09-17 21:16:55','2021-09-17 21:17:09',NULL,1,0,NULL,100,0);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
