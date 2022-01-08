<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes\Mocks;

use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;

/**
 * Mocks middleware from the middleware library
 */
final class MiddlewareLibraryMiddleware implements IMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(IRequest $request, IRequestHandler $next): IResponse
    {
        return $next->handle($request);
    }
}
