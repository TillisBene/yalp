<?php 

namespace handlers;

use App\Database\Connection;

class LoginHandler
{
    public function tryLogin(string $username, string $password): bool
    {
        $db = Connection::getInstance();
        
        $user = $db->get('users', '*', ['username' => $username]);

        if ($user && password_verify($password, $user['password'])) {
            return true;
        }

        return false;
    }
}