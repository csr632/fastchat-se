CREATE TABLE IF NOT EXISTS `fastchat_db`.`users` (
  `userName` VARCHAR(16) NOT NULL,
  `password` VARCHAR(32) NOT NULL,
  `email` VARCHAR(32) NOT NULL,
  `nickname` VARCHAR(16) NOT NULL DEFAULT 'default nickname',
  `gender` ENUM('male', 'female') NOT NULL,
  PRIMARY KEY (`userName`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `fastchat_db`.`friendship` (
  `userName` VARCHAR(16) NOT NULL,
  `friendName` VARCHAR(16) NOT NULL,
  PRIMARY KEY (`userName`, `friendName`),
  INDEX `fk_friendship_2_idx` (`friendName` ASC),
  CONSTRAINT `fk_friendship_1`
    FOREIGN KEY (`userName`)
    REFERENCES `fastchat_db`.`users` (`userName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_friendship_2`
    FOREIGN KEY (`friendName`)
    REFERENCES `fastchat_db`.`users` (`userName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `fastchat_db`.`friendRequest` (
  `from` VARCHAR(16) NOT NULL,
  `to` VARCHAR(16) NOT NULL,
  `time` DATETIME NOT NULL,
  `state` ENUM('rejected', 'accepted', 'pending') NOT NULL,
  PRIMARY KEY (`from`, `to`),
  INDEX `fk_friendRequest_2_idx` (`to` ASC),
  CONSTRAINT `fk_friendRequest_1`
    FOREIGN KEY (`from`)
    REFERENCES `fastchat_db`.`users` (`userName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_friendRequest_2`
    FOREIGN KEY (`to`)
    REFERENCES `fastchat_db`.`users` (`userName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
