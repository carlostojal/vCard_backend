<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('Unauthenticated');
        // return  "teste";
         // return response()->json(['error' => 'Unauthenticated'], 401);
    }

    protected function unauthenticated($request, array $guards)
    {
        // return response()->json(['error' => 'Unauthenticated for this route'], 401);
        throw new AuthenticationException('Unauthenticated.', $guards, $this->redirectTo($request));
         // return response()->json(['error' => 'Unauthenticated'], 401);
    }

}
