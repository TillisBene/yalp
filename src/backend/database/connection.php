<?php

namespace database;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Medoo\Medoo;
use Dotenv\Dotenv;

class Connection
{
    private static ?Medoo $instance = null;

    public static function getInstance(): Medoo
    {
        if (self::$instance === null) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../..');
            $dotenv->load();

            self::$instance = new Medoo([
                'type' => $_ENV['DB_TYPE'] ?? '',
                'host' => $_ENV['DB_HOST'] ?? '',
                'database' => $_ENV['DB_NAME'] ?? '',
                'username' => $_ENV['DB_USER'] ?? '',
                'password' => $_ENV['DB_PASS'] ?? ''
            ]);
        }

        return self::$instance;
    }
}
