<?php

class DatabaseConnection
{
    private $host = 'localhost';
    private $dbname = 'password_manager';
    private $username = 'root';
    private $password = '';
    private $pdo = null;

    public function __construct()
    {
        $dsn = "mysql:host=".$this->host.";dbname=".$this->dbname.";charset=utf8mb4";
        $this->pdo = new PDO($dsn, $this->username, $this->password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
