CREATE TABLE IF NOT EXISTS `userentity` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Unique ID of the alert",
  `user` INT UNSIGNED NOT NULL COMMENT "User Id",
  `entity` INT UNSIGNED NOT NULL COMMENT "Entity Id",
	INDEX (`user`, `entity`),
	PRIMARY KEY (`id`),
	UNIQUE (`id`),
	FOREIGN KEY (`entity`) REFERENCES `entity` (`id`),
	FOREIGN KEY (`user`) REFERENCES `user` (`id`)
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ENGINE=InnoDB;


