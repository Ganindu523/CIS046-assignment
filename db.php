<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'puzzle_app');
define('DB_USER', 'root');       
define('DB_PASS', '');           
define('DB_CHARSET', 'utf8mb4');


 @return PDO
    @throws RuntimeException if connection fails
 
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         
            PDO::ATTR_EMULATE_PREPARES   => false,                   
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
           
            error_log("DB Connection failed: " . $e->getMessage());
            throw new RuntimeException("Database connection failed. Please try again later.");
        }
    }

    return $pdo;
}
