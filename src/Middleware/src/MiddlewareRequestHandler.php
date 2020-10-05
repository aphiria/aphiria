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

use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;

/**
 * Defines the middleware request handler
 */
final class MiddlewareRequestHandler implements IRequestHandler
{
    /**
     * @param IMiddleware $middleware The middleware that will handle the request
     * @param IRequestHandler $next The next request handler
     */
    public function __construct(private IMiddleware $middleware, private IRequestHandler $next)
    {
    }

    /**
     * @inheritdoc
     */
    public function handle(IRequest $request): IResponse
    {
        return $this->middleware->handle($request, $this->next);
    }
}
