<?php

namespace App\Database;

require_once __DIR__ . '/../../../vendor/autoload.php';

require_once __DIR__ . '/connection.php';

try {
    $database = Connection::getInstance();
    
    // Try to execute a simple query
    $result = $database->query("SELECT 1")->fetch();
    
    if ($result) {
        echo "Database connection successful!\n";
    }
    
} catch (\Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}