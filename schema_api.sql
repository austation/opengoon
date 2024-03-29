-- OpenGoon API SQL Schema v1.2 --

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

DROP TABLE IF EXISTS `cloudsaves`;
CREATE TABLE IF NOT EXISTS `cloudsaves` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`name` VARCHAR(32) NOT NULL,
	`data` VARCHAR(10240), -- expect about 10KiB of savefile data. A savefile with one profile is about 2KiB as text, so extrapolating we can expect 6-7KiB for one with 3. This gives a good margin.
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `persistent`;
CREATE TABLE IF NOT EXISTS `persistent` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`key` VARCHAR(32) NOT NULL,
	`value` VARCHAR(1024) NOT NULL,
	PRIMARY KEY (`id`)
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
	`removed` BOOLEAN NOT NULL DEFAULT FALSE, -- tbh, we shouldn't just nuke bans from the face of the earth, because that's what I did. let's keep them around for admins to look at
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`akey` VARCHAR(32) NOT NULL,
	`server` VARCHAR(32) NOT NULL,
	`note` VARCHAR(512) NOT NULL,
	`timestamp` DATETIME NOT NULL DEFAULT NOW(), -- bans and stuff don't have this, but I thought it'd be useful to show times notes were added.
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `exp`;
CREATE TABLE IF NOT EXISTS `exp` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ckey` VARCHAR(32) NOT NULL,
	`type` VARCHAR(32) NOT NULL,
	`value` INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
