CREATE TABLE IF NOT EXISTS `fastchat_db`.`users` (
  `userName` VARCHAR(16) NOT NULL,
  `password` VARCHAR(32) NOT NULL,
  `email` VARCHAR(32) NOT NULL,
  `nickname` VARCHAR(16) NOT NULL DEFAULT 'default nickname',
  `gender` ENUM('male', 'female') NOT NULL,
  PRIMARY KEY (`userName`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB
