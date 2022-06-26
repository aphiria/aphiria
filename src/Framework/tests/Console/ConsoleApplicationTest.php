<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console;

use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\StatusCode;
use Aphiria\Framework\Console\ConsoleApplication;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConsoleApplicationTest extends TestCase
{
    private ConsoleApplication $app;
    private ICommandBus&MockObject $consoleGateway;

    protected function setUp(): void
    {
        $this->consoleGateway = $this->createMock(ICommandBus::class);
        $this->app = new ConsoleApplication($this->consoleGateway, []);
    }

    public function testRunReturnsStatusCodeValueIfGatewayReturnsInt(): void
    {
        $this->consoleGateway->method('handle')
            ->willReturn(100);
        $this->assertSame(100, $this->app->run());
    }

    public function testRunReturnsStatusCodeValueIfGatewayReturnsEnum(): void
    {
        $this->consoleGateway->method('handle')
            ->willReturn(StatusCode::Fatal);
        $this->assertSame(StatusCode::Fatal->value, $this->app->run());
    }

    public function testUnhandledExceptionsAreRethrownAsRuntimeExceptions(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to run the application');
        $this->consoleGateway->method('handle')
            ->willThrowException(new Exception());
        $this->app->run();
    }
}
