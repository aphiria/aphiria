<?php
namespace Opulence\Router\Middleware;

use Closure;

/**
 * Defines the interface for route middleware to implement
 */
interface IMiddleware
{
    public function handle($request, Closure $next);
}
