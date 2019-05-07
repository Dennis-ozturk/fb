<?php
class DB
{
    private $username = "root";
    private $password = "";
    private $host = "localhost";
    private $dbname = "book_system";
    private $charset = "utf8";

    protected $conn;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
        } catch (PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function connect()
    {
        return $this->conn;
    }
}
