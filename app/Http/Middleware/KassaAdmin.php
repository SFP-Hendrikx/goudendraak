<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KassaAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('kassa_is_admin')) {
            return redirect()
                ->route('kassa.index')
                ->with('error', 'Je hebt geen rechten om gerechten te beheren.');
        }

        return $next($request);
    }
}
