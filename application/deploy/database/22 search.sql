CREATE TABLE IF NOT EXISTS `search` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Unique ID of the search query",
  `query` TEXT NOT NULL COMMENT "Search query",
	INDEX (`id`),
	PRIMARY KEY (`id`),
	UNIQUE (`id`)
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ENGINE=InnoDB;


