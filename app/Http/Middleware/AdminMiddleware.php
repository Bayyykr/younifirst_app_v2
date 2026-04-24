<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        if (auth()->check()) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Anda tidak memiliki hak akses sebagai admin.',
            ]);
        }

        return redirect()->route('login');
    }
}
