<?php
namespace Opulence\Router\Dispatchers;

use Closure;

/**
 * Defines the interface for middleware pipelines to implement
 */
interface IMiddlewarePipeline
{
    public function send($request, array $middleware, Closure $controller);
}
