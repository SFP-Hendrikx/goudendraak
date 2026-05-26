<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KassaAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('kassa_logged_in')) {
            return redirect()->route('kassa.index');
        }

        return $next($request);
    }
}
