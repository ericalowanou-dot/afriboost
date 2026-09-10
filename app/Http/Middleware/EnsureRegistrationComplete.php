<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && $user->isCreator()
            && ! $user->hasCompletedRegistration()
            && ! $request->routeIs('register.step2', 'register.step2.store', 'logout')
        ) {
            return redirect()->route('register.step2');
        }

        return $next($request);
    }
}
