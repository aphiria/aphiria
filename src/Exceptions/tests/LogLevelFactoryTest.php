<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Tests;

use Aphiria\Exceptions\LogLevelFactory;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LogLevelFactoryTest extends TestCase
{
    private LogLevelFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new LogLevelFactory();
    }

    public function testCreatingLogLevelDefaultsToErrorLogLevelIfExceptionHasNoCustomLogLevel(): void
    {
        $exception = new Exception();
        $this->assertSame(LogLevel::ERROR, $this->factory->createLogLevel($exception));
    }

    public function testCreatingLogLevelWithManyCustomErrorLogLevelUsesThem(): void
    {
        $exception = new Exception();
        $this->factory->registerManyLogLevelFactories([
            Exception::class => fn (Exception $ex) => LogLevel::EMERGENCY
        ]);
        $this->assertSame(LogLevel::EMERGENCY, $this->factory->createLogLevel($exception));
    }

    public function testCreatingLogLevelWithSingleCustomErrorLogLevelUsesIt(): void
    {
        $exception = new Exception();
        $this->factory->registerLogLevelFactory(Exception::class, fn (Exception $ex) => LogLevel::EMERGENCY);
        $this->assertSame(LogLevel::EMERGENCY, $this->factory->createLogLevel($exception));
    }
}
