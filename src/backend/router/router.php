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
use utils\TextFormatter;
use handlers\MailHandler;
use utils\InputValidator;
use utils\GetDevice as Device;

SessionManager::createSession();

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
    
        if (SessionManager::isAuthenticated()) {
            error_log('User is authenticated, redirecting to /app');
            SimpleRouter::response()->redirect('/app');
            return;
        }else{
            SessionManager::createSession();
        }
    
        include 'src/backend/templates/app/login.php';
    });

    /**
     * 
     * Create Account Page
     * 
     */
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

        /* 
         * 
         * User main page
         * 
         */
        SimpleRouter::get('/app', function () {
            //SessionManager::createSession();

            require_once 'src/backend/templates/app/app.php';
        });
    });


    //-----------------------------------------------------
    //
    // API
    //
    //-----------------------------------------------------

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
            return;
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

            $request = new Request();
            $response = new Response($request);

            //SessionManager::createSession();

            $data = [
                'email' => utils\Sanitizer::email($request->getInputHandler()->value('email', 'email')),
                'password' => utils\Sanitizer::cleanString($request->getInputHandler()->value('password', 'password'))
            ];

            // Validate input
            $validator = new InputValidator();
            if (!$validator->validateLogin($data)) {
                $response->json([
                    'type' => 'error',
                    'message' => $validator->getErrors()
                ]);
                return;
            }

            if (SessionManager::isAuthenticated()) {
                $response->json([
                    'type' => 'success',
                    'message' => 'Already logged in',
                ]);
                return;
            }

            $database = Connection::getInstance();

            try {
                $database->pdo->beginTransaction();

                $user = $database->get("users", [
                    "user_id",
                    "email",
                    "username",
                    "login_code",
                    "first_login",
                    "password_hash"
                ], [
                    "email" => $data['email']
                ]);

                if (!$user) {
                    throw new \Exception('Invalid credentials');
                }

                if (!password_verify($data['password'], $user["password_hash"])) {
                    throw new \Exception('Invalid credentials');
                }

                $sessionId = session_id();

                // Update user's current session
                $database->update("users", [
                    "current_session" => $sessionId
                ], [
                    "user_id" => $user["user_id"]
                ]);

                if ($user["first_login"]) {
                    // First login - needs verification
                    SessionManager::addToSession([
                        'authenticated' => false,
                        'user_id' => $user["user_id"],
                        'email' => $user["email"],
                        'username' => $user["username"],
                        'login_code' => $user["login_code"],
                        'has_to_verify' => $user["first_login"]
                    ]);

                    $database->pdo->commit();

                    $response->json([
                        'type' => 'verify',
                        'message' => 'Please verify your account',
                        'has_to_verify' => true,
                    ]);
                    return;
                }

                // Already verified - set authenticated and session data
                SessionManager::addToSession([
                    'authenticated' => true,
                    'user_id' => $user["user_id"],
                    'email' => $user["email"],
                    'username' => $user["username"],
                    'login_code' => $user["login_code"],
                    'has_to_verify' => false
                ]);

                // Device handling
                $deviceInfo = new Device();
                $deviceInfo->setUserId($user["user_id"]);
                $deviceInfo->setDeviceName($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
                $deviceInfo->setDeviceType('web');
                $deviceInfo->setLastIp($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
                $deviceInfo->setUserAgent($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
                $deviceInfo->setRefreshToken(CookieGenerator::set('refresh_token', CodeGenerator::generate(), 2592000));

                // Before the final response in login endpoint
                error_log("LOGIN SUCCESS - SESSION ID: " . session_id());
                error_log("SESSION DATA AT LOGIN: " . print_r($_SESSION, true));

                $existingDevice = $database->get("devices", ["device_id"], [
                    "user_id" => $user["user_id"],
                    "user_agent" => $deviceInfo->getUserAgent(),
                    "last_ip" => $deviceInfo->getLastIp()
                ]);

                if (!$existingDevice) {
                    $database->insert("devices", $deviceInfo->toArray());

                    $mailHandler = new MailHandler();
                    $emailTemplate = "New device login detected:<br><br>" .
                        "Device: " . $deviceInfo->getDeviceName() . "<br>" .
                        "IP Address: " . $deviceInfo->getLastIp() . "<br>" .
                        "Time: " . $deviceInfo->getLastLogin() . "<br><br>" .
                        "If this wasn't you, please secure your account immediately.";

                    try {
                        $mailHandler->sendMail($user["email"], 'YALP | New Device Login', $emailTemplate);
                    } catch (\Throwable $th) {
                        error_log('Email Error: '.$th);
                    }
                } else {
                    $database->update("devices", [
                        "last_login" => $deviceInfo->getLastLogin(),
                        "last_ip" => $deviceInfo->getLastIp()
                    ], [
                        "device_id" => $existingDevice["device_id"]
                    ]);
                }

                $database->pdo->commit();

                $response->json([
                    'type' => 'success',
                    'message' => 'Login Correct',
                    'has_to_verify' => false,
                    'redirect' => '/app'
                ]);
                return;

            } catch (\Exception $e) {
                $database->pdo->rollBack();
                $response->json([
                    'type' => 'error',
                    'message' => 'Account does not Exist'
                ]);
                return;
            }
        })->name('login');

        /**
         * 
         * 
         * Create Account
         * 
         */
        SimpleRouter::post('/create-account', function () {
            $request = new Request();
            $response = new Response($request);

            //SessionManager::createSession();

            // Collect all input data
            $data = [
                'email' => Sanitizer::email($request->getInputHandler()->value('email')),
                'username' => Sanitizer::cleanString($request->getInputHandler()->value('username')),
                'password' => Sanitizer::cleanString($request->getInputHandler()->value('password')),
                'confirmPassword' => Sanitizer::cleanString($request->getInputHandler()->value('confirmPassword'))
            ];

            // Validate input
            $validator = new InputValidator();
            if (!$validator->validateRegistration($data)) {
                $response->json([
                    'type' => 'error',
                    'message' => $validator->getErrors()
                ]);
                return;
            }

            $database = Connection::getInstance();

            try {
                $database->pdo->beginTransaction();

                $existingUser = $database->get("users", ["email"], ["email" => $data['email']]);
                if ($existingUser) {
                    $response->json([
                        'type' => 'error',
                        'success' => false,
                        'message' => 'Email already registered'
                    ]);
                    return;
                }

                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

                $result = $database->insert("users", [
                    "email" => $data['email'],
                    "password_hash" => $hashedPassword,
                    "username" => $data['username'],
                    "login_code" => CodeGenerator::generate()
                ]);

                $database->pdo->commit();

                if ($result->rowCount() > 0) {
                    try {
                        $loginCode = $database->get("users", "login_code", ["email" => $data['email']]);

                        // Replace with html template
                        $emailTemplate = "Welcome to Yalp!<br><br>" .
                            "Your verification code is: <strong>" . $loginCode . "</strong><br><br>" .
                            "This code is required on login.<br><br>" .
                            "If you didn't create an account, please ignore this email." . "<br><br>" .
                            "Now, let's get Yalping!";

                        $mailHandler = new MailHandler();
                        $emailResult = $mailHandler->sendMail($data['email'], 'YALP | Verify your email', $emailTemplate);

                        if ($emailResult) {
                            $response->json([
                                'type' => 'success',
                                'success' => true,
                                'message' => 'Account created successfully',
                                'redirect' => '/login'
                            ]);
                            return;
                        } else {
                            $response->json([
                                'type' => 'warning',
                                'success' => true,
                                'message' => 'Account created but verification email failed to send',
                                'redirect' => '/login'
                            ]);
                            return;
                        }
                    } catch (\Exception $e) {
                        error_log('Email sending failed: ' . $e->getMessage()); 
                        $response->json([
                            'type' => 'warning',
                            'success' => true,
                            'message' => 'Account created but verification email failed to send',
                            'redirect' => '/login'
                        ]);
                        return;
                    }
                } else {
                    $response->json([
                        'type' => 'error',
                        'success' => false,
                        'message' => 'Failed to create account'
                    ]);
                    return;
                }
            } catch (\Exception $e) {
                $database->pdo->rollBack();
                error_log('Database error: ' . $e->getMessage());
                $response->json([
                    'type' => 'error',
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
                return;
            }
        });

        /**
         * 
         * 
         * Validate Email
         * 
         */
        SimpleRouter::post('/verify-email/{code}', function ($code) {
            
            //SessionManager::createSession();

            $request = new Request();
            $response = new Response($request);
            $database = Connection::getInstance();
            
            error_log('Session data: ' . print_r($_SESSION, true));
            
            $code = Sanitizer::cleanString($code);
            $code = TextFormatter::removeSpaces($code);
            $code = TextFormatter::toUpperCase($code);
            
            // Get email from request instead of session
            $email = Sanitizer::email($request->getInputHandler()->value('email'));
            
            if (empty($email)) {

                $email = SessionManager::getFromSession('email');

                if (empty($email)) {
                    $response->json([
                        'type' => 'error',
                        'message' => 'Email is required',
                    ]);
                    return;
                }
            }
            
            $user = $database->get("users", [
                "login_code",
            ], [
                "email" => $email
            ]);

            if (!$user) {
                $response->json([
                    'type' => 'error',
                    'message' => 'User not found',
                    //'data' => $request->getInputHandler()->all(),
                ]);
                return;
            }

            if ($user["login_code"] !== $code) {
                $response->json([
                    'type' => 'error',
                    'message' => 'Invalid code',
                ]);
                return;
            } else {
                // Update database first
                try {
                    // Change authenticated status
                    SessionManager::changeValueInSession('authenticated', true);

                    $result = $database->update("users", [
                        "first_login" => false
                    ], [
                        "email" => Sanitizer::email($email)
                    ]);

                    if ($result->rowCount() > 0) {
                        // Only send success response after successful update
                        $response->json([
                            'type' => 'success',
                            'message' => 'Email verified',
                            'redirect' => '/app'
                        ]);
                    } else {
                        $response->json([
                            'type' => 'error',
                            'message' => 'Failed to update user status'
                        ]);
                    }
                } catch (\Throwable $th) {
                    error_log('Error updating first_login: ' . $th->getMessage());
                    $response->json([
                        'type' => 'error',
                        'message' => 'Failed to update user'
                    ]);
                    return;
                }
            }
        });

        /**
         * 
         * 
         * Protected Api
         * 
         */
        SimpleRouter::group(['middleware' => Middleware\AuthMiddleware::class], function () {

            SimpleRouter::get('/logout', function () {
                SessionManager::resetSession();
                SessionManager::closeSession();
                SimpleRouter::response()->redirect('/');
            });
        });
    });
});

SimpleRouter::start();