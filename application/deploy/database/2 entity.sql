CREATE TABLE IF NOT EXISTS `entity` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Unique ID of the entity",
  `title` TEXT NOT NULL COMMENT "Entity title",
  `link` TEXT NOT NULL COMMENT "Entity link",
  `picture` TEXT "Photo filename",
  `description` TEXT NOT NULL COMMENT "Entity desciption",
  `cached` TIMESTAMP COMMENT "When this entity was cached?",
  `updated` TIMESTAMP COMMENT "When this entity was updated?",
  `status` INT UNSIGNED NOT NULL COMMENT "Status of entity",
  `feed` TEXT NOT NULL COMMENT "Entity feed url",
  INDEX (`id`),
  PRIMARY KEY (`id`),
  UNIQUE (`id`)
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ENGINE=InnoDB;


