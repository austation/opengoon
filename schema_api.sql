-- OpenGoon API SQL Schema v1 --

DROP TABLE IF EXISTS `player`;
CREATE TABLE IF NOT EXISTS `player` (
	`ckey` VARCHAR(32) NOT NULL,
	`ip` INT UNSIGNED NOT NULL,
	`compid` VARCHAR(32) NOT NULL,
	`connections` INT NOT NULL,
	`ua` VARCHAR(256) NOT NULL DEFAULT 'none',
	`byond_major` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	`byond_minor` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	`participations` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	`playtime` INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`ckey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `ip_history`;
CREATE TABLE IF NOT EXISTS `ip_history` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`ip` INT UNSIGNED NOT NULL,
	`count` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `compid_history`;
CREATE TABLE IF NOT EXISTS `compid_history` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`compid` VARCHAR(32) NOT NULL,
	`count` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Commented out for now, will probably remove completely, depending.
-- DROP TABLE IF EXISTS `participation`;
-- CREATE TABLE IF NOT EXISTS `participation` (
-- 	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
-- 	`ckey` VARCHAR(32) NOT NULL,
-- 	`mode` VARCHAR(32) NOT NULL,
-- 	`seen` SMALLINT UNSIGNED NOT NULL,
-- 	`seen_total` SMALLINT UNSIGNED NOT NULL,
-- 	PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `antag`;
CREATE TABLE IF NOT EXISTS `antag` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`role` VARCHAR(32) NOT NULL,
	`selected` SMALLINT UNSIGNED NOT NULL,
	`ignored` SMALLINT UNSIGNED NOT NULL,
	`seen` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `gauntlet`;
CREATE TABLE IF NOT EXISTS `gauntlet` (
	`ckey` VARCHAR(32) NOT NULL,
	`amount` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`ckey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `vpn_whitelist`;
CREATE TABLE IF NOT EXISTS `vpn_whitelist` (
	`ckey` VARCHAR(32) NOT NULL,
	`akey` VARCHAR(32) NOT NULL,
	PRIMARY KEY (`ckey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `jobbans`;
CREATE TABLE IF NOT EXISTS `jobbans` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`role` VARCHAR(32) NOT NULL,
	`akey` VARCHAR(32) NOT NULL,
	`server` VARCHAR(32) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `bans`;
CREATE TABLE IF NOT EXISTS `bans` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`ip` INT UNSIGNED NOT NULL,
	`compid` VARCHAR(32) NOT NULL,
	`reason` VARCHAR(256) NOT NULL,
	`oakey` VARCHAR(32) DEFAULT NULL,
	`akey` VARCHAR(32) NOT NULL,
	`timestamp` INT UNSIGNED NOT NULL,
	`previous` INT UNSIGNED NOT NULL DEFAULT '0',
	`chain` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	`server` VARCHAR(32) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`akey` VARCHAR(32) NOT NULL,
	`server` VARCHAR(32) NOT NULL,
	`note` VARCHAR(512) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
