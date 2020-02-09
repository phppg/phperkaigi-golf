CREATE TABLE `errors` (
  `id` MEDIUMINT unsigned NOT NULL AUTO_INCREMENT,
  `time` DATETIME NOT NULL,
  `title` VARCHAR(191) NOT NULL,
  `trace` JSON,
  PRIMARY KEY (`id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="PHP Error traces";

CREATE TABLE `players` (
  `id` MEDIUMINT unsigned NOT NULL AUTO_INCREMENT,
  `fortee_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `display_name` VARCHAR(191) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `foree_name` (`fortee_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="PHP Golf players";

CREATE TABLE `passwords` (
  `id` MEDIUMINT unsigned NOT NULL AUTO_INCREMENT,
  `player_id` MEDIUMINT unsigned NOT NULL,
  `hash` VARCHAR(255) CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB COMMENT="PHP Golf player password hashes";

CREATE TABLE `saved_codes` (
  `id` MEDIUMINT unsigned NOT NULL AUTO_INCREMENT,
  `player_id` MEDIUMINT unsigned,
  `code` MEDIUMTEXT,
  `hole` VARCHAR(255),
  `hash` VARBINARY(32) COMMENT "SHA-256 Digest",
  `published` TINYINT(1),
  `ipaddr` VARBINARY(16),
  PRIMARY KEY (`id`),
  KEY `player_id` (`player_id`),
  KEY `hole` (`hole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="PHP Golf saved codes";
