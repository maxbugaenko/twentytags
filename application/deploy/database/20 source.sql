CREATE TABLE IF NOT EXISTS `source` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Unique ID of the alert",
  `entity` INT UNSIGNED NOT NULL COMMENT "Entity Id",
  `provider` varchar(100) NOT NULL COMMENT "Alerts source",
  `source` TEXT NOT NULL COMMENT "Source technical setup",
	INDEX (`entity`),
	PRIMARY KEY (`id`),
	UNIQUE (`id`),
	FOREIGN KEY (`entity`) REFERENCES `entity` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ENGINE=InnoDB;


