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

use Aphiria\Api\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Net\Http\HttpException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Tests the exception log level factory registry
 */
class ExceptionLogLevelFactoryRegistryTest extends TestCase
{
    /** @var ExceptionLogLevelFactoryRegistry */
    private $registry;

    protected function setUp(): void
    {
        $this->registry = new ExceptionLogLevelFactoryRegistry();
    }

    public function testGettingFactoryForExceptionTypeThatDoesNotHaveFactoryReturnsNull(): void
    {
        $this->assertNull($this->registry->getFactory(InvalidArgumentException::class));
    }

    public function testGettingFactoryForExceptionTypeThatHasFactoryReturnsTheFactory(): void
    {
        $expectedFactory = function (HttpException $ex) {
            return LogLevel::ERROR;
        };
        $this->registry->registerFactory(InvalidArgumentException::class, $expectedFactory);
        $this->assertSame($expectedFactory, $this->registry->getFactory(InvalidArgumentException::class));
    }

    public function testRegisteringMultipleFactoriesStoresFactoriesByExceptionType(): void
    {
        $expectedFactory1 = function (InvalidArgumentException $ex) {
            return LogLevel::ERROR;
        };
        $expectedFactory2 = function (HttpException $ex) {
            return LogLevel::ERROR;
        };
        $this->registry->registerManyFactories([
            InvalidArgumentException::class => $expectedFactory1,
            HttpException::class => $expectedFactory2
        ]);
        $this->assertSame($expectedFactory1, $this->registry->getFactory(InvalidArgumentException::class));
        $this->assertSame($expectedFactory2, $this->registry->getFactory(HttpException::class));
    }
}
