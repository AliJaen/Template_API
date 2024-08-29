<?php

class DatabaseConnection
{
    private $dbh;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->dbh = new PDO($dsn, DB_USER, DB_PASSWORD);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Retornar el error y detener la ejecución del código
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                "error" =>  "Internal server error",
                "detail" => $e->getMessage()
            ]);
            exit();
        }
    }

    public function getConnection()
    {
        return $this->dbh;
    }
}
