<?php

require_once 'vendor\autoload.php';

use Pecee\SimpleRouter\SimpleRouter;
use Pecee\Http\Url;
use Pecee\Http\Response;
use Pecee\Http\Request;


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
        return 'Hello world';
    });

    /**
     * 
     * Login Page
     * 
     */
    SimpleRouter::get('/login', function () {
        return 'login';
    });

    /**
     * 
     * 
     * Main Page
     * 
     */
    /* 
    SimpleRouter::group(['middleware' => \Demo\Middleware\Auth::class], function () {
        SimpleRouter::get('/app', function() {
            return 'login';
        });
    });
    */

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

        /**
         * 
         * 
         * Protected Api
         * 
         * 
         */
        /*SimpleRouter::group(['middleware' => \Demo\Middleware\Auth::class], function () {
            SimpleRouter::get('/user/{id}', function($id) {
                return 'admin';
            });
        });*/

    });

});

SimpleRouter::start();