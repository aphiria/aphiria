<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Mocks;

use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;

/**
 * Mocks middleware that increments a header value for use in tests
 */
class MiddlewareThatIncrementsHeader implements IMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(IRequest $request, IRequestHandler $next): IResponse
    {
        $response = $next->handle($request);
        $currValues = [];

        // Keep appending an incrementing value to a header
        if ($response->headers->tryGet('Foo', $currValues)) {
            /** @var array<int, int> $currValues */
            $response->headers->add('Foo', $currValues[\count($currValues) - 1] + 1, true);
        } else {
            $response->headers->add('Foo', 1);
        }

        return $response;
    }
}
