<?php 

namespace handlers;

use Pecee\Http\Request;
use Pecee\SimpleRouter\Handlers\IExceptionHandler;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;
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