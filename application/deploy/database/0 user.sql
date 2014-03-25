CREATE TABLE IF NOT EXISTS `user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Unique ID of the user",
  `email` VARBINARY(200) NOT NULL COMMENT "Unique user email",
  `password` VARBINARY(50) NOT NULL COMMENT "User password",
  `nickname` VARCHAR(50) DEFAULT 'user' NOT NULL COMMENT "Unique nickname for public",
  `name` VARCHAR(100) DEFAULT 'user' NOT NULL COMMENT "Name of the user",
  `admin` INT UNSIGNED COMMENT "Is admin",
  PRIMARY KEY (`id`),
  UNIQUE (`nickname`),
  UNIQUE (`email`)
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ENGINE=InnoDB;