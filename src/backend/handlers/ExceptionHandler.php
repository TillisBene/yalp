<?php

namespace handlers;

use Pecee\Http\Request;
use Pecee\SimpleRouter\Handlers\IExceptionHandler;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;
use Pecee\Http\Response;
use PDOException;

class ExceptionHandler implements IExceptionHandler
{
    public function handleError(Request $request, \Exception $error): void
    {
        // Add debugging
        error_log('Exception handler triggered for path: ' . $request->getUrl()->getPath());
        error_log('Exception type: ' . get_class($error));
        error_log('Exception message: ' . $error->getMessage());
        
        $response = new Response($request);

        /* You can use the exception handler to format errors depending on the request and type. */
        if ($request->getUrl()->contains('/api')) {
            $response->json([
                'error' => $error->getMessage(),
                'code'  => $error->getCode(),
            ]);
            return;
        }
        
        if (!$request->getUrl()->contains('/api') && 
            !in_array($request->getUrl()->getPath(), ['/', '/login', '/register', '/create-account'])) {
            $response->redirect('/login');
            return;
        }

        if ($error instanceof PDOException){
            $response->json([
                'error' => 'DB - Error - '. $error,
                'code' => 404
            ]);
            return;
        }

        /* Other handlers */
        if ($error instanceof NotFoundHttpException) {
            // Render custom 404-page
            $response->json([
                'error' => 'Page not found',
                'code' => 404
            ]);
            return;
        }

        throw $error;
    }
}