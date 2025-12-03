<?php

namespace App\Http\Middleware;

use App\Facades\Response as FacadesResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class idempotency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->input('idempotency') ?? $request->header('Idempotency');
        if (!$key) {
           throw new HttpException(419,'Idempotency key is required');
        }
        if (Cache::has($key)) {
           
            throw new HttpException(409,Cache::get($key) );
        }

        Cache::put($key, 'Duplicate request detected', 120);
        return $next($request);
    }
}
