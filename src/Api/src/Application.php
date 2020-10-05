<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api;

use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;

/**
 * Defines the top-level request handler that makes up an application
 */
class Application implements IRequestHandler
{
    /**
     * @param IRequestHandler $router The request handler that will be the last to be executed in the middleware pipeline and performs routing
     * @param MiddlewareCollection $middleware The list of middleware to send requests and responses through
     */
    public function __construct(private IRequestHandler $router, private MiddlewareCollection $middleware)
    {
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
