<?php

class DatabaseEngine
{
    private $conn;
    private $databaseName = 'users_catalyst_test';

    public function __construct($params)
    {
        $mysqli = @new mysqli($params['-h'], $params['-u'], $params['-p'], $this->databaseName);
        if ($mysqli->connect_error) {
            throw new Exception($params['-h'].' connect error ('.$mysqli->connect_errno.'): '.$mysqli->connect_error);
        }
        $mysqli->set_charset('utf8');
        $this->conn = $mysqli;
    }
    
    public function close()
    {
        if (!empty($this->conn)) {
            $this->conn->close();
        }
    }
    
    public function queryDb($sql)
    {
        $mysql_result = $this->conn->query($sql);
        if ($mysql_result === false) {
            throw new Exception("invalid query: '$sql', error: ".$this->conn->error);
        }
        return $mysql_result;
    }
    
    public function getTableExistence()
    {
        $sql = "SELECT COUNT(1) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$this->databaseName}' AND TABLE_NAME = 'users'";
        $row = $this->queryDb($sql)->fetch_row();
        if (empty($row)) {
            throw new Exception('cannot get information about table existence, something went wrong');
        }
        return $row[0] == 1;
    }
    
    public function getRecordsCount()
    {
        $sql = "SELECT COUNT(1) FROM users";
        $row = $this->queryDb($sql)->fetch_row();
        if (empty($row)) {
            throw new Exception("cannot get record count from table 'users'");
        }
        return $row[0];
    }
    
    public function dropTable()
    {
        $sql = 'DROP TABLE users';
        $this->queryDb($sql);
    }
    
    public function createTable()
    {
        $sql = '
CREATE TABLE `users` (
	`Id` INT(11) NOT NULL AUTO_INCREMENT,
	`Name` VARCHAR(100) NOT NULL,
	`Surname` VARCHAR(100) NOT NULL,
	`Email` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`Id`),
	UNIQUE INDEX `Email` (`Email`)
)
COLLATE="utf8_unicode_ci"
ENGINE=InnoDB';
        $this->queryDb($sql);
    }

}