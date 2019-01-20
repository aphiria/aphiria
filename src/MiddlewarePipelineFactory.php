<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Middleware;

use Opulence\Net\Http\Handlers\IRequestHandler;

/**
 * Defines the factory for middleware pipelines
 */
class MiddlewarePipelineFactory
{
    /**
     * Creates a pipeline that will execute middleware, ending with the execution of a controller action
     *
     * @param IMiddleware[] $middleware The list of middleware to add to the pipeline
     * @param IRequestHandler $controllerRequestHandler The request handler that executes the controller action
     * @return IRequestHandler The pipeline
     */
    public function createPipeline(array $middleware, IRequestHandler $controllerRequestHandler): IRequestHandler
    {
        if (\count($middleware) === 0) {
            return $controllerRequestHandler;
        }

        $next = $controllerRequestHandler;
        $curr = null;

        for ($i = \count($middleware) - 1;$i >= 0;$i--) {
            $curr = new MiddlewareRequestHandler($middleware[$i], $next);
            $next = $curr;
        }

        return $curr;
    }
}