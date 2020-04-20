<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api;

use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;

/**
 * Defines the top-level request handler that makes up an application
 */
class Application implements IRequestHandler
{
    /** @var IRequestHandler The request handler that will be the last to be executed in the middleware pipeline and performs routing */
    private IRequestHandler $router;
    /** @var MiddlewareCollection The list of middleware */
    private MiddlewareCollection $middleware;

    /**
     * @param IRequestHandler $router The request handler that will be the last to be executed in the middleware pipeline and performs routing
     * @param MiddlewareCollection $middleware
     */
    public function __construct(IRequestHandler $router, MiddlewareCollection $middleware)
    {
        $this->router = $router;
        $this->middleware = $middleware;
    }

    /**
     * @inheritdoc
     */
    public function handle(IRequest $request): IResponse
    {
        $middlewarePipeline = (new MiddlewarePipelineFactory())->createPipeline($this->middleware->getAll(), $this->router);

        return $middlewarePipeline->handle($request);
    }
}
