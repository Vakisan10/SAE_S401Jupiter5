<?php
// core/Database.php

class Database {

    private static ?Database $instance = null;
    public PDO $bd;

    private function __construct() {
        require_once __DIR__ . '/../../config/database.php';

        try {
            $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
            if (defined('Pdo\Mysql::ATTR_INIT_COMMAND')) {
                $options[\Pdo\Mysql::ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
            } else {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
            }
            $this->bd = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            die("❌ Erreur BD : " . $e->getMessage());
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function prepare(string $sql): \PDOStatement {
        return $this->bd->prepare($sql);
    }
}
