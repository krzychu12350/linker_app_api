<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandleNotFound
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'message' => 'The requested route or resource could not be found.',
                'status' => 404
            ], 404);
        }
    }
}
