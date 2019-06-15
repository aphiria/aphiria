<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Exceptions;

use Aphiria\Api\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IHttpRequestMessage;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the exception response factory registry
 */
class ExceptionResponseFactoryRegistryTest extends TestCase
{
    private ExceptionResponseFactoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ExceptionResponseFactoryRegistry();
    }

    public function testGettingFactoryForExceptionTypeThatDoesNotHaveFactoryReturnsNull(): void
    {
        $this->assertNull($this->registry->getFactory(InvalidArgumentException::class));
    }

    public function testGettingFactoryForExceptionTypeThatHasFactoryReturnsTheFactory(): void
    {
        $expectedFactory = fn (HttpException $ex, ?IHttpRequestMessage $request) => null;
        $this->registry->registerFactory(InvalidArgumentException::class, $expectedFactory);
        $this->assertSame($expectedFactory, $this->registry->getFactory(InvalidArgumentException::class));
    }

    public function testRegisteringMultipleFactoriesStoresFactoriesByExceptionType(): void
    {
        $expectedFactory1 = fn (InvalidArgumentException $ex, ?IHttpRequestMessage $request) => null;
        $expectedFactory2 = fn (HttpException $ex, ?IHttpRequestMessage $request) => null;
        $this->registry->registerManyFactories([
            InvalidArgumentException::class => $expectedFactory1,
            HttpException::class => $expectedFactory2
        ]);
        $this->assertSame($expectedFactory1, $this->registry->getFactory(InvalidArgumentException::class));
        $this->assertSame($expectedFactory2, $this->registry->getFactory(HttpException::class));
    }
}
