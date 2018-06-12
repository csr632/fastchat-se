CREATE TABLE IF NOT EXISTS `fastchat_db`.`users` (
  `userName` VARCHAR(16) NOT NULL,
  `password` VARCHAR(32) NOT NULL,
  `email` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`userName`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB
