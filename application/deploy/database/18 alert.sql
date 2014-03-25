CREATE TABLE IF NOT EXISTS `alert` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Unique ID of the alert",
  `entity` INT UNSIGNED NOT NULL COMMENT "Entity Id",
  `type` varchar(15) NOT NULL COMMENT "Alert type. Could be video or text",
  `title` TEXT NOT NULL COMMENT "Alert title",
  `source` varchar(255) NOT NULL COMMENT "Alert source",
  `link` varchar(255) NOT NULL COMMENT "Alert link",
  `body` varchar(255) NOT NULL COMMENT "Alert body",
  `picture` TEXT COMMENT "Alert picture",
  `added` TIMESTAMP COMMENT "When this alert was added?",
  `hash` TEXT NOT NULL COMMENT "Hash of alert",
	INDEX (`entity`),
	PRIMARY KEY (`id`),
	UNIQUE (`id`),
	FOREIGN KEY (`entity`) REFERENCES `entity` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ENGINE=InnoDB;


