CREATE TABLE IF NOT EXISTS `entitycache` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT "Unique ID of the cached entity",
  `entity` INT UNSIGNED NOT NULL COMMENT "Entity ID of the entity in original table",
  `title` TEXT NOT NULL COMMENT "Entity title",
  `tags` TEXT NOT NULL COMMENT "Entity tags devided by coma",
  `description` TEXT NOT NULL COMMENT "Entity description devided by coma",
  FULLTEXT (`title`, `tags`, `description`)
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ENGINE=MyISAM;