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

use Aphiria\Exceptions\LogLevelRegistry;
use Aphiria\Net\Http\HttpException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Tests the exception log level registry
 */
class LogLevelRegistryTest extends TestCase
{
    private LogLevelRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new LogLevelRegistry();
    }

    public function testGettingLogLevelForExceptionTypeThatDoesNotHaveFactoryReturnsNull(): void
    {
        $this->assertNull($this->registry->getLogLevel(new InvalidArgumentException));
    }

    public function testGettingLogLevelForExceptionTypeThatHasFactoryReturnsTheResultOfTheFactory(): void
    {
        $expectedFactory = fn (InvalidArgumentException $ex) => LogLevel::EMERGENCY;
        $this->registry->registerLogLevelFactory(InvalidArgumentException::class, $expectedFactory);
        $this->assertSame(LogLevel::EMERGENCY, $this->registry->getLogLevel(new InvalidArgumentException));
    }

    public function testRegisteringMultipleFactoriesStoresFactoriesByExceptionType(): void
    {
        $expectedFactory1 = fn (InvalidArgumentException $ex) => LogLevel::EMERGENCY;
        $expectedFactory2 = fn (HttpException $ex) => LogLevel::DEBUG;
        $this->registry->registerManyLogLevelFactories([
            InvalidArgumentException::class => $expectedFactory1,
            HttpException::class => $expectedFactory2
        ]);
        $this->assertSame(LogLevel::EMERGENCY, $this->registry->getLogLevel(new InvalidArgumentException));
        $this->assertSame(LogLevel::DEBUG, $this->registry->getLogLevel(new HttpException(200)));
    }
}
