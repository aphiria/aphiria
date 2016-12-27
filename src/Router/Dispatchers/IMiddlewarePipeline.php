<?php
namespace Opulence\Router\Dispatchers;

/**
 * Defines the interface for middleware pipelines to implement
 */
interface IMiddlewarePipeline
{
    public function send($request, array $middleware, callable $controller);
}