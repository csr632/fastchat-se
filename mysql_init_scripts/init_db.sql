-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema fastchat_db
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema fastchat_db
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `fastchat_db` DEFAULT CHARACTER SET utf8 ;
USE `fastchat_db` ;

-- -----------------------------------------------------
-- Table `fastchat_db`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fastchat_db`.`users` ;

CREATE TABLE IF NOT EXISTS `fastchat_db`.`users` (
  `userName` VARCHAR(16) NOT NULL,
  `password` VARCHAR(32) NOT NULL,
  `email` VARCHAR(32) NOT NULL,
  `nickname` VARCHAR(16) NOT NULL,
  `gender` ENUM('male', 'female') NOT NULL,
  PRIMARY KEY (`userName`))
ENGINE = InnoDB;

CREATE UNIQUE INDEX `email_UNIQUE` ON `fastchat_db`.`users` (`email` ASC);


-- -----------------------------------------------------
-- Table `fastchat_db`.`friendships`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fastchat_db`.`friendships` ;

CREATE TABLE IF NOT EXISTS `fastchat_db`.`friendships` (
  `userName` VARCHAR(16) NOT NULL,
  `friendName` VARCHAR(16) NOT NULL,
  PRIMARY KEY (`userName`, `friendName`),
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

CREATE INDEX `fk_friendship_2_idx` ON `fastchat_db`.`friendships` (`friendName` ASC);


-- -----------------------------------------------------
-- Table `fastchat_db`.`friendRequests`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fastchat_db`.`friendRequests` ;

CREATE TABLE IF NOT EXISTS `fastchat_db`.`friendRequests` (
  `reqId` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `from` VARCHAR(16) NOT NULL,
  `to` VARCHAR(16) NOT NULL,
  `time` TIMESTAMP NOT NULL,
  `state` ENUM('rejected', 'accepted', 'pending') NOT NULL,
  `message` VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`reqId`),
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

CREATE INDEX `fk_friendRequest_1_idx` ON `fastchat_db`.`friendRequests` (`from` ASC);

CREATE INDEX `fk_friendRequest_2_idx` ON `fastchat_db`.`friendRequests` (`to` ASC);


-- -----------------------------------------------------
-- Table `fastchat_db`.`chats`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fastchat_db`.`chats` ;

CREATE TABLE IF NOT EXISTS `fastchat_db`.`chats` (
  `chatId` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `chatName` VARCHAR(16) NULL,
  `isGroup` TINYINT NOT NULL,
  PRIMARY KEY (`chatId`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fastchat_db`.`messages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fastchat_db`.`messages` ;

CREATE TABLE IF NOT EXISTS `fastchat_db`.`messages` (
  `messageId` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `chatId` BIGINT UNSIGNED NOT NULL,
  `content` TEXT NOT NULL,
  `from` VARCHAR(16) NOT NULL,
  PRIMARY KEY (`messageId`),
  CONSTRAINT `fk_messages_1`
    FOREIGN KEY (`chatId`)
    REFERENCES `fastchat_db`.`chats` (`chatId`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_messages_2`
    FOREIGN KEY (`from`)
    REFERENCES `fastchat_db`.`users` (`userName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_messages_1_idx` ON `fastchat_db`.`messages` (`chatId` ASC);

CREATE INDEX `fk_messages_2_idx` ON `fastchat_db`.`messages` (`from` ASC);


-- -----------------------------------------------------
-- Table `fastchat_db`.`inChat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fastchat_db`.`inChat` ;

CREATE TABLE IF NOT EXISTS `fastchat_db`.`inChat` (
  `userName` VARCHAR(16) NOT NULL,
  `chatId` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`userName`, `chatId`),
  CONSTRAINT `fk_inChat_1`
    FOREIGN KEY (`userName`)
    REFERENCES `fastchat_db`.`users` (`userName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inChat_2`
    FOREIGN KEY (`chatId`)
    REFERENCES `fastchat_db`.`chats` (`chatId`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_inChat_2_idx` ON `fastchat_db`.`inChat` (`chatId` ASC);


-- -----------------------------------------------------
-- Table `fastchat_db`.`groupInvitations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fastchat_db`.`groupInvitations` ;

CREATE TABLE IF NOT EXISTS `fastchat_db`.`groupInvitations` (
  `invId` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `chatId` BIGINT UNSIGNED NOT NULL,
  `from` VARCHAR(16) NOT NULL,
  `to` VARCHAR(16) NOT NULL,
  `message` VARCHAR(45) NOT NULL,
  `time` TIMESTAMP NOT NULL,
  `state` ENUM('rejected', 'accepted', 'pending') NOT NULL,
  PRIMARY KEY (`invId`),
  CONSTRAINT `fk_groupInvitation_1`
    FOREIGN KEY (`from`)
    REFERENCES `fastchat_db`.`users` (`userName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_groupInvitation_2`
    FOREIGN KEY (`to`)
    REFERENCES `fastchat_db`.`users` (`userName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_groupInvitation_3`
    FOREIGN KEY (`chatId`)
    REFERENCES `fastchat_db`.`chats` (`chatId`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_groupInvitation_1_idx` ON `fastchat_db`.`groupInvitations` (`from` ASC);

CREATE INDEX `fk_groupInvitation_2_idx` ON `fastchat_db`.`groupInvitations` (`to` ASC);

CREATE INDEX `fk_groupInvitation_3_idx` ON `fastchat_db`.`groupInvitations` (`chatId` ASC);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `fastchat_db`.`users`
-- -----------------------------------------------------
START TRANSACTION;
USE `fastchat_db`;
INSERT INTO `fastchat_db`.`users` (`userName`, `password`, `email`, `nickname`, `gender`) VALUES ('t1', '111111', '1@test.com', 'n1', 'male');
INSERT INTO `fastchat_db`.`users` (`userName`, `password`, `email`, `nickname`, `gender`) VALUES ('t2', '222222', '2@test.com', 'n2', 'female');
INSERT INTO `fastchat_db`.`users` (`userName`, `password`, `email`, `nickname`, `gender`) VALUES ('t3', '333333', '3@test.com', 'n3', 'female');

COMMIT;


-- -----------------------------------------------------
-- Data for table `fastchat_db`.`friendships`
-- -----------------------------------------------------
START TRANSACTION;
USE `fastchat_db`;
INSERT INTO `fastchat_db`.`friendships` (`userName`, `friendName`) VALUES ('t1', 't3');
INSERT INTO `fastchat_db`.`friendships` (`userName`, `friendName`) VALUES ('t3', 't1');
INSERT INTO `fastchat_db`.`friendships` (`userName`, `friendName`) VALUES ('t2', 't3');
INSERT INTO `fastchat_db`.`friendships` (`userName`, `friendName`) VALUES ('t3', 't2');

COMMIT;


-- -----------------------------------------------------
-- Data for table `fastchat_db`.`chats`
-- -----------------------------------------------------
START TRANSACTION;
USE `fastchat_db`;
INSERT INTO `fastchat_db`.`chats` (`chatId`, `chatName`, `isGroup`) VALUES (DEFAULT, NULL, false);
INSERT INTO `fastchat_db`.`chats` (`chatId`, `chatName`, `isGroup`) VALUES (DEFAULT, NULL, false);
INSERT INTO `fastchat_db`.`chats` (`chatId`, `chatName`, `isGroup`) VALUES (DEFAULT, '群聊1', true);
INSERT INTO `fastchat_db`.`chats` (`chatId`, `chatName`, `isGroup`) VALUES (DEFAULT, '群聊2', true);

COMMIT;


-- -----------------------------------------------------
-- Data for table `fastchat_db`.`messages`
-- -----------------------------------------------------
START TRANSACTION;
USE `fastchat_db`;
INSERT INTO `fastchat_db`.`messages` (`messageId`, `chatId`, `content`, `from`) VALUES (DEFAULT, 1, 'chat1 from t1', 't1');
INSERT INTO `fastchat_db`.`messages` (`messageId`, `chatId`, `content`, `from`) VALUES (DEFAULT, 1, 'chat1 from t3', 't3');
INSERT INTO `fastchat_db`.`messages` (`messageId`, `chatId`, `content`, `from`) VALUES (DEFAULT, 2, 'chat2 from t2', 't2');
INSERT INTO `fastchat_db`.`messages` (`messageId`, `chatId`, `content`, `from`) VALUES (DEFAULT, 2, 'chat3 from t3', 't3');

COMMIT;


-- -----------------------------------------------------
-- Data for table `fastchat_db`.`inChat`
-- -----------------------------------------------------
START TRANSACTION;
USE `fastchat_db`;
INSERT INTO `fastchat_db`.`inChat` (`userName`, `chatId`) VALUES ('t1', 1);
INSERT INTO `fastchat_db`.`inChat` (`userName`, `chatId`) VALUES ('t3', 1);
INSERT INTO `fastchat_db`.`inChat` (`userName`, `chatId`) VALUES ('t2', 2);
INSERT INTO `fastchat_db`.`inChat` (`userName`, `chatId`) VALUES ('t3', 2);
INSERT INTO `fastchat_db`.`inChat` (`userName`, `chatId`) VALUES ('t1', 3);
INSERT INTO `fastchat_db`.`inChat` (`userName`, `chatId`) VALUES ('t2', 3);
INSERT INTO `fastchat_db`.`inChat` (`userName`, `chatId`) VALUES ('t3', 3);
INSERT INTO `fastchat_db`.`inChat` (`userName`, `chatId`) VALUES ('t3', 4);

COMMIT;

