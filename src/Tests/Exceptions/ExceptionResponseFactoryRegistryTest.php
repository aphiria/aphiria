<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Exceptions;

use InvalidArgumentException;
use Opulence\Api\Exceptions\ExceptionResponseFactoryRegistry;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\IHttpRequestMessage;

/**
 * Tests the exception response factory registry
 */
class ExceptionResponseFactoryRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ExceptionResponseFactoryRegistry The registry to use in tests */
    private $registry;

    public function setUp(): void
    {
        $this->registry = new ExceptionResponseFactoryRegistry();
    }

    public function testGettingFactoryForExceptionTypeThatDoesNotHaveFactoryReturnsNull(): void
    {
        $this->assertNull($this->registry->getFactory(InvalidArgumentException::class));
    }

    public function testGettingFactoryForExceptionTypeThatHasFactoryReturnsTheFactory(): void
    {
        $expectedFactory = function (HttpException $ex, ?IHttpRequestMessage $request) {
            // Don't do anything
        };
        $this->registry->registerFactory(InvalidArgumentException::class, $expectedFactory);
        $this->assertSame($expectedFactory, $this->registry->getFactory(InvalidArgumentException::class));
    }

    public function testRegisteringMultipleFactoriesStoresFactoriesByExceptionType(): void
    {
        $expectedFactory1 = function (InvalidArgumentException $ex, ?IHttpRequestMessage $request) {
            // Don't do anything
        };
        $expectedFactory2 = function (HttpException $ex, ?IHttpRequestMessage $request) {
            // Don't do anything
        };
        $this->registry->registerFactories([
            InvalidArgumentException::class => $expectedFactory1,
            HttpException::class => $expectedFactory2
        ]);
        $this->assertSame($expectedFactory1, $this->registry->getFactory(InvalidArgumentException::class));
        $this->assertSame($expectedFactory2, $this->registry->getFactory(HttpException::class));
    }
}
