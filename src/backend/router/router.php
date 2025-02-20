<?php

require_once 'vendor\autoload.php';

use Pecee\SimpleRouter\SimpleRouter;
use Pecee\Http\Url;
use Pecee\Http\Response;
use Pecee\Http\Request;
use utils\SessionManager;
use database\Connection;
use database\MigrationRunner;
use utils\Sanitizer;


/**
 * 
 * 
 * Router
 * 
 * 
 */
SimpleRouter::group(['exceptionHandler' => \handlers\ExceptionHandler::class], function () {


    /**
     * 
     * Main Page
     * 
     */
    SimpleRouter::get('/', function () {

        include 'src/backend/templates/index_page.php';
    });

    /**
     * 
     * Login Page
     * 
     */
    SimpleRouter::get('/login', function () {

        //SessionManager::createSession();

        include 'src/backend/templates/app/login.php';
    });

    SimpleRouter::get('/create-account', function () {

        //SessionManager::createSession();

        include 'src/backend/templates/app/create-account.php';
    });

    /**
     * 
     * 
     * Main Page
     * 
     */
    SimpleRouter::group(['middleware' => Middleware\AuthMiddleware::class], function () {

        SimpleRouter::get('/app', function () {
            return 'login';
        });
    });

    /**
     * 
     * 
     * Api
     * 
     */
    SimpleRouter::group(['prefix' => '/api'], function () {

        /**
         * 
         * 
         * Public Api
         * 
         * 
         */
        SimpleRouter::get('/', function () {

            $request = new Request();
            $response = new Response($request);

            $response->json([
                'message' => 'Welcome to the API',
            ]);

        });

        SimpleRouter::post('/login', function () {

            $request = new Request();
            $response = new Response($request);

            $email = Sanitizer::email($request->getInputHandler()->value('email', 'email'));
            $request->getInputHandler()->value('password', 'password');

            $response->json([
                'message' => 'Login',
                'data' => $request->getInputHandler()->all(),
            ]);
        });

        SimpleRouter::post('/create-account', function () {

            $request = new Request();
            $response = new Response($request);

            

            $response->json(value: [
                'message' => 'Login',
                'data' => $request->getInputHandler()->all(),
            ]);
        });

        /**
         * 
         * 
         * Protected Api
         * 
         * 
         */
        SimpleRouter::group(['middleware' => Middleware\AuthMiddleware::class], function () {

            SimpleRouter::get('/app/{id}', function ($id) {
                $request = new Request();
                $response = new Response($request);

                $response->json([
                    'message' => 'Login',
                ]);
            });

        });
    });

});

SimpleRouter::start();