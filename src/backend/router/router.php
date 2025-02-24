<?php

require_once 'vendor\autoload.php';

use Pecee\SimpleRouter\SimpleRouter;
use Pecee\Http\Url;
use Pecee\Http\Response;
use Pecee\Http\Request;
use utils\SessionManager as SessionManager;
use utils\Sanitizer as Sanitizer;
use Medoo\Medoo;
use database\Connection;
use utils\CodeGenerator;
use utils\CookieGenerator;

/**
 * 
 * 
 * CSRF Token
 * 
 * 
 */
$csrfVerifier = new Middleware\CsrfVerifier;
SimpleRouter::csrfVerifier($csrfVerifier);

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

    /**
     * 
     * Create Account Page
     * 
     */
    SimpleRouter::get('/create-account', function () {
        SessionManager::createSession();

        include 'src/backend/templates/app/create-account.php';
    });

    /**
     * 
     * 
     * Main Page
     * 
     */
    SimpleRouter::group(['middleware' => Middleware\AuthMiddleware::class], function () {

        /* 
         * 
         * User main page
         * 
         */
        SimpleRouter::get('/app', function () {
            return 'login';
        });

        /* 
         * 
         * User verify account
         * 
         */
        SimpleRouter::get('/verify', function () {
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

        SimpleRouter::post('/get-csrf', function () {
            return SimpleRouter::router()->getCsrfVerifier()->getTokenProvider()->getToken();
        });

        /**
         * 
         * 
         * Login
         * 
         */
        SimpleRouter::post('/login', function () {

            SessionManager::createSession();

            $request = new Request();
            $response = new Response($request);

            $email = utils\Sanitizer::email($request->getInputHandler()->value('email', 'email'));
            $password = utils\Sanitizer::cleanString($request->getInputHandler()->value('password', 'password'));

            if ($email === null || $password === null) {
                $response->json([
                    'type' => 'error',
                    'message' => 'Invalid input',
                    'data' => $request->getInputHandler()->all(),
                ]);
            }
            if (SessionManager::isAuthenticated()) {
                $response->json([
                    'type' => 'success',
                    'message' => 'Already logged in',
                    'data' => $request->getInputHandler()->all(),
                ]);
            }

            $database = Connection::getInstance();

            $user = $database->get("users", [
                "id",
                "password_hash",
                "has_to_verify"
            ], [
                "email" => $email
            ]);

            if (!$user) {
                $response->json([
                    'type' => 'error',
                    'message' => 'User not found',
                    'data' => $request->getInputHandler()->all(),
                ]);
                return;
            }

            if(password_verify($password, $user["password_hash"])){
                $response->json([
                    'type' => 'success',
                    'message' => 'Login Correct',
                    'has_to_verify' => $user["has_to_verify"],
                    'data' => $request->getInputHandler()->all(),
                ]);

                SessionManager::addToSession([
                    'authenticated' => true,
                    'user_id' => $user["id"],
                    'email' => $email,
                    'username' => $user["username"],
                    'login_code' => $user["login_code"],
                    'has_to_verify' => $user["has_to_verify"]
                ]);

            }else{
                $response->json([
                    'type' => 'error',
                    'message' => 'Invalid password',
                    'data' => $request->getInputHandler()->all(),
                ]);
            }
        });

        /**
         * 
         * 
         * Create Account
         * 
         */
        SimpleRouter::post('/create-account', function () {

            $request = new Request();
            $response = new Response($request);

            SessionManager::createSession();

            $email = Sanitizer::email($request->getInputHandler()->value('email'));
            $username = Sanitizer::cleanString($request->getInputHandler()->value('username'));
            $password = Sanitizer::cleanString($request->getInputHandler()->value('password'));
            $confirmPassword = Sanitizer::cleanString($request->getInputHandler()->value('confirmPassword'));

            if ($email === null || $password === null || $username === null) {
                $response->json([
                    'type' => 'error',
                    'message' => 'Invalid input',
                    'data' => $request->getInputHandler()->all(),
                ]);
            }

            if ($password !== $confirmPassword) {
                $response->json([
                    'type' => 'error',
                    'message' => 'Passwords do not match',
                    'data' => $request->getInputHandler()->all(),
                ]);
            }

            $database = Connection::getInstance();

            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $result = $database->insert("users", [
                    "email" => $email,
                    "password_hash" => $hashedPassword,
                    "username" => $username,
                    "login_code" => CodeGenerator::generate()
                ]);

                if ($result->rowCount() > 0) {
                    $response->json([
                        'type' => 'success',
                        'success' => true,
                        'message' => 'Account created successfully',
                        'redirect' => '/login'
                    ]);

                } else {
                    $response->json([
                        'type' => 'error',
                        'success' => false,
                        'message' => 'Failed to create account'
                    ]);
                }
            } catch (\Exception $e) {
                $response->json([
                    'type' => 'error',
                    'success' => false,
                    'message' => 'Database error',
                    'error' => $e->getMessage()
                ]);
            }

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