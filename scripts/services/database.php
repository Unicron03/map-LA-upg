<?php

class Database {
    private PDO $pdo;
    private static Database $instance;

    private function __construct() {
        $this->pdo = new PDO("mysql:host=localhost;dbname=map-LA", $_SESSION["pdoUserName"], $_SESSION["pdoUserPassword"], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public static function get(): PDO {
        if ( ! isset( self::$instance ) )
            self::$instance = new Database();
        return self::$instance->pdo;
    }
}