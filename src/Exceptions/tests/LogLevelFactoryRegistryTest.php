<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Tests;

use Aphiria\Exceptions\LogLevelFactoryRegistry;
use Aphiria\Net\Http\HttpException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Tests the exception log level factory registry
 */
class LogLevelFactoryRegistryTest extends TestCase
{
    private LogLevelFactoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new LogLevelFactoryRegistry();
    }

    public function testGettingFactoryForExceptionTypeThatDoesNotHaveFactoryReturnsNull(): void
    {
        $this->assertNull($this->registry->getFactory(InvalidArgumentException::class));
    }

    public function testGettingFactoryForExceptionTypeThatHasFactoryReturnsTheFactory(): void
    {
        $expectedFactory = fn (HttpException $ex) => LogLevel::ERROR;
        $this->registry->registerFactory(InvalidArgumentException::class, $expectedFactory);
        $this->assertSame($expectedFactory, $this->registry->getFactory(InvalidArgumentException::class));
    }

    public function testRegisteringMultipleFactoriesStoresFactoriesByExceptionType(): void
    {
        $expectedFactory1 = fn (InvalidArgumentException $ex) => LogLevel::ERROR;
        $expectedFactory2 = fn (HttpException $ex) => LogLevel::ERROR;
        $this->registry->registerManyFactories([
            InvalidArgumentException::class => $expectedFactory1,
            HttpException::class => $expectedFactory2
        ]);
        $this->assertSame($expectedFactory1, $this->registry->getFactory(InvalidArgumentException::class));
        $this->assertSame($expectedFactory2, $this->registry->getFactory(HttpException::class));
    }
}
