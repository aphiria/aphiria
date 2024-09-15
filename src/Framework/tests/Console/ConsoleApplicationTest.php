<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console;

use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\Framework\Console\ConsoleApplication;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConsoleApplicationTest extends TestCase
{
    private ConsoleApplication $app;
    private ICommandHandler&MockObject $consoleGateway;
    private Input $input;
    private IOutput&MockObject $output;

    protected function setUp(): void
    {
        $this->consoleGateway = $this->createMock(ICommandHandler::class);
        $this->input = new Input('foo', [], []);
        $this->output = $this->createMock(IOutput::class);
        $driver = new class () implements IDriver {
            public int $cliWidth = 3;
            public int $cliHeight = 2;

            public function readHiddenInput(IOutput $output): ?string
            {
                return null;
            }
        };
        $this->output->method('$driver::get')
            ->willReturn($driver);
        $this->app = new ConsoleApplication($this->consoleGateway, $this->input, $this->output);
    }

    public function testRunReturnsOkStatusCodeValueIfGatewayReturnsVoid(): void
    {
        $this->consoleGateway->method('handle')
            ->willReturn(null);
        $this->assertSame(StatusCode::Ok->value, $this->app->run());
    }

    public function testRunReturnsStatusCodeValueIfGatewayReturnsEnum(): void
    {
        $this->consoleGateway->method('handle')
            ->willReturn(StatusCode::Fatal);
        $this->assertSame(StatusCode::Fatal->value, $this->app->run());
    }

    public function testRunReturnsStatusCodeValueIfGatewayReturnsInt(): void
    {
        $this->consoleGateway->method('handle')
            ->willReturn(100);
        $this->assertSame(100, $this->app->run());
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
