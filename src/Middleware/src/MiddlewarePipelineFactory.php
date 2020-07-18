<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

use Aphiria\Net\Http\IRequestHandler;

/**
 * Defines the factory for middleware pipelines
 */
final class MiddlewarePipelineFactory
{
    /**
     * Creates a pipeline that will execute middleware, ending with the execution of a controller action
     *
     * @param IMiddleware[] $middleware The list of middleware to add to the pipeline
     * @param IRequestHandler $terminalRequestHandler The request handler that is last in the pipeline (eg controller)
     * @return IRequestHandler The pipeline
     */
    public function createPipeline(array $middleware, IRequestHandler $terminalRequestHandler): IRequestHandler
    {
        if (\count($middleware) === 0) {
            return $terminalRequestHandler;
        }

        $next = $terminalRequestHandler;
        $curr = null;

        for ($i = \count($middleware) - 1;$i >= 0;$i--) {
            $curr = new MiddlewareRequestHandler($middleware[$i], $next);
            $next = $curr;
        }

        return $curr;
    }
}
