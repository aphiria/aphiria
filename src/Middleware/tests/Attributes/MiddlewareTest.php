<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests\Attributes;

use Aphiria\Middleware\Attributes\Middleware;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public function testEmptyClassNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be set');
        /**
         * @psalm-suppress UndefinedClass Intentionally testing an empty string
         * @psalm-suppress ArgumentTypeCoercion Ditto
         */
        new Middleware('');
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $middleware = new class () implements IMiddleware {
            /**
             * @inheritdoc
             */
            public function handle(IRequest $request, IRequestHandler $next): IResponse
            {
                return $next->handle($request);
            }
        };
        $middlewareAttribute = new Middleware($middleware::class, ['foo' => 'bar']);
        $this->assertSame($middleware::class, $middlewareAttribute->className);
        $this->assertSame(['foo' => 'bar'], $middlewareAttribute->parameters);
    }
}
