<?php
namespace Middleware;

use Pecee\Http\Middleware\BaseCsrfVerifier;

class CsrfVerifier extends BaseCsrfVerifier 
{
    protected array $except = [
        // Empty array means no routes are excluded from CSRF verification
    ];
}
