<?php

namespace handlers;

use Pecee\Http\Request;
use Pecee\SimpleRouter\Handlers\IExceptionHandler;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;

class ExceptionHandler implements IExceptionHandler
{
	public function handleError(Request $request, \Exception $error): void
	{

		/* You can use the exception handler to format errors depending on the request and type. */

		if ($request->getUrl()->contains('/api')) {

			response()->json([
				'error' => $error->getMessage(),
				'code'  => $error->getCode(),
			]);

		}

		/* The router will throw the NotFoundHttpException on 404 */
		if($error instanceof NotFoundHttpException) {

			// Render custom 404-page
			$request->setRewriteCallback('Demo\Controllers\PageController@notFound');
			return;
			
		}
		
		/* Other error 
		if($error instanceof MyCustomException) {

			$request->setRewriteRoute(
				// Add new route based on current url (minus query-string) and add custom parameters.
				(new RouteUrl(url(null, null, []), 'PageController@error'))->setParameters(['exception' => $error])
			);
			return;
			
		}*/

		throw $error;

	}

}