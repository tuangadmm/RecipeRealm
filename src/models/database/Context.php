<?php

namespace Src\models\database;

use PDO;

class Context
{
    private mixed $conn;

    public function __construct()
    {
        try {
            $this->conn = new PDO(
                'mysql:host=' . Configs::SERVER_NAME . ';' . 'dbname=' . Configs::DB_NAME,
                Configs::USERNAME,
                Configs::PASSWORD
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (\PDOException  $e) {
            echo 'Connection failed: ' . $e->getMessage();
            die();
        }
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }

    public function closeConnection(): void
    {
        $this->conn = null;
    }

}