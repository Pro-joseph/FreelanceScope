<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->role !== UserRole::Admin, 403, 'Accès admin requis.');

        return $next($request);
    }
}
