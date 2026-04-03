<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateClientKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key        = $request->header('X-CLIENT-KEY');
        $configKey  = config('app.client_api_key');

        if(!$key || $key !== $configKey) {
            return response()->json([
                'success'   => false,
                'message'   => 'Invalid client key provided',  
                'error'     => 'Unauthorized'
            ], 401);
        }
        return $next($request);
    }
}
