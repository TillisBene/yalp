<?php

namespace App\Database;

require 'vendor/autoload.php';

use Medoo\Medoo;

class Connection
{
    private static ?Medoo $instance = null;

    public static function getInstance(): Medoo
    {
        if (self::$instance === null) {
            self::$instance = new Medoo([
                'type' => 'mariadb',
                'host' => 'localhost',
                'database' => 'yalp',
                'username' => 'root',
                'password' => ''
            ]);
        }

        return self::$instance;
    }
}
