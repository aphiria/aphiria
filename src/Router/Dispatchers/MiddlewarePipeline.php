<?php
namespace Opulence\Router\Dispatchers;

use Opulence\Pipelines\Pipeline;
use Opulence\Pipelines\PipelineException;
use Opulence\Router\RouteException;

/**
 * Defines the middleware dispatcher that sends requests through a pipeline
 */
class MiddlewarePipeline implements IMiddlewarePipeline
{
    /**
     * @inheritdoc
     */
    public function send($request, array $middleware, callable $controller)
    {
        try {
            $response = (new Pipeline)
                ->send($request)
                ->through($middleware, "handle")
                ->then($controller)
                ->execute();

            return $response ?? new Response();
        } catch (PipelineException $ex) {
            throw new RouteException("Failed to send request through middleware pipeline", 0, $ex);
        }
    }
}