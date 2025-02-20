<?php

namespace database;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/MigrationRunner.php';

try {
    $database = Connection::getInstance();
    
    // Try to execute a simple query
    $result = $database->query("SELECT 1")->fetch();
    
    if ($result) {
        error_log("Database connection successful!");

        // Run migrations
        $migrationRunner = new MigrationRunner();
        $migrationRunner->run();            
    } else {
        throw new \RuntimeException("Database connection failed!");
    }
} catch (\Exception $e) {
    error_log("Connection failed: " . $e->getMessage());
    throw $e;
}