<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */

    private const MAX_REQUESTS = 100;
    private const DECAY_SECONDS = 300; // 5 minutes
    public function handle(Request $request, Closure $next): Response
    {
        $key    = 'rate_limit:'. $request->ip();
        $data   = Cache::get($key, ['count' => 0, 'start_time' => now()->timestamp]); 

        if(now()->timestamp - $data['start_time'] > self::DECAY_SECONDS)
        {
            $data = ['count' => 0, 'start_time' => now()->timestamp];
        }

        if($data['count'] >= self::MAX_REQUESTS){
            return response()-> json([
                'success'   => false,
                'status'    => 429,
                'message'   => 'Too many requests. Please try again later.',
                'retry_after_seconds' => self::DECAY_SECONDS - (now()->timestamp - $data['start_time'])
            ]);
        }

        $data['count']++;
        Cache::put($key, $data, self::DECAY_SECONDS);

        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', self::MAX_REQUESTS);
        $response->headers->set('X-RateLimit-Remaining', max(0, self::MAX_REQUESTS - $data['count']));
        $response->headers->set('X-RateLimit-Reset', $data['start_time'] + self::DECAY_SECONDS);

        return $response;
    }
}
