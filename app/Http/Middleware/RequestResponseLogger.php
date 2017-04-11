<?php

namespace App\Http\Middleware;

use Closure;
use Log;

class RequestResponseLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        if (function_exists('getallheaders')) {
            // @codeCoverageIgnoreStart
            $headers = json_decode(json_encode(getallheaders()), true);
            if (array_key_exists('Authorization', $headers)) {
                Log::info('requests', [
                    'method' => $request->method(),
                    'url' => $request->url(),
                    'request' => $request->all(),
                    'Authorization' => $headers['Authorization'],
                    'response' => $response
                ]);
            }
            // @codeCoverageIgnoreEnd
        }
        else {
            Log::info('requests', [
                'method' => $request->method(),
                'url' => $request->url(),
                'request' => $request->all(),
                'response' => $response
            ]);
        }

    }
}