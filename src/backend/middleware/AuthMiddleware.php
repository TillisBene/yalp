<?php

namespace Middleware;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use utils\SessionManager;
use Pecee\SimpleRouter\SimpleRouter;

class AuthMiddleware implements IMiddleware 
{
    public function handle(Request $request): void 
    {    
        
        // Don't check auth for login and register pages
        $path = $request->getUrl()->getPath();
        if ($path === '/login' || $path === '/register' || $path === '/create-account') {
            return;
        }
        
        if (!SessionManager::isAuthenticated()) {
            // Don't close the session before redirect
            SimpleRouter::response()->redirect('/login');
            return;
        }
    }
}