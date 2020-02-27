CREATE TABLE `users` (
	`Id` INT(11) NOT NULL AUTO_INCREMENT,
	`Name` VARCHAR(100) NOT NULL,
	`Surname` VARCHAR(100) NOT NULL,
	`Email` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`Id`),
	UNIQUE INDEX `Email` (`Email`)
)
COLLATE="utf8_unicode_ci"
ENGINE=InnoDB;