<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DatabaseConnectionTwo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        config(['database.default' => 'mysql']);
        config()->set('database.default', 'mysql');
        config(['mail.default' => 'smtp']);
        config()->set('mail.default', 'smtp');
        return $next($request);
    }
}
