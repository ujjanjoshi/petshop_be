<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DatabaseConnectionOne
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        config(['database.default' => 'mysql_pet_shop']);
        config()->set('database.default', 'mysql_pet_shop');
        config(['mail.default' => 'petShop_smtp']);
        config()->set('mail.default', 'petShop_smtp');
        
        return $next($request);
    }
}
