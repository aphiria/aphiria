<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests\Attributes;

use Aphiria\Middleware\Attributes\ExcludeMiddleware;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExcludeMiddlewareTest extends TestCase
{
    public function testEmptyClassNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be set');
        /**
         * @psalm-suppress UndefinedClass Intentionally testing an empty string
         * @psalm-suppress ArgumentTypeCoercion Ditto
         */
        new ExcludeMiddleware('');
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
        $excludeMiddlewareAttribute = new ExcludeMiddleware($middleware::class, ['foo' => 'bar']);
        $this->assertSame($middleware::class, $excludeMiddlewareAttribute->className);
        $this->assertSame(['foo' => 'bar'], $excludeMiddlewareAttribute->parameters);
    }
}