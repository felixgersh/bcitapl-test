<?php

define('DB_NAME', 'users_catalyst_test');
define('TABLE_NAME', 'users');

class DatabaseEngine
{
    private $conn;

    public function __construct($params)
    {
        $mysqli = @new mysqli($params['-h'], $params['-u'], $params['-p'], DB_NAME);
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
        $sql = 'SELECT COUNT(1) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = "'.DB_NAME.'" AND TABLE_NAME = "'.TABLE_NAME.'"';
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
        $ddlFilename = 'users.ddl';
        $sql = @file_get_contents($ddlFilename);
        if ($sql === false) {
            throw new Exception("cannot read $ddlFilename");
        }
        $this->queryDb($sql);
    }

    public function getConnection()
    {
        return $this->conn;
    }

}
