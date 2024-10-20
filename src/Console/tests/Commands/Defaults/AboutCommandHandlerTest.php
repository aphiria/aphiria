<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Defaults;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;

class AboutCommandHandlerTest extends TestCase
{
    private CommandRegistry $commands;
    private AboutCommandHandler $handler;
    private IOutput&MockObject $output;

    protected function setUp(): void
    {
        $this->output = $this->createMock(IOutput::class);
        $this->commands = new CommandRegistry();
        $this->handler = new AboutCommandHandler($this->commands);
        $driver = new class () implements IDriver {
            public int $cliWidth = 3;
            public int $cliHeight = 2;

            public function readHiddenInput(IOutput $output): ?string
            {
                return null;
            }
        };
        $this->output->method(PropertyHook::get('driver'))
            ->willReturn($driver);
    }

    public function testCommandsAreAlphabeticallySortedByCategories(): void
    {
        $commandHandler1 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $commandHandler2 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(new Command('cat:foo', [], [], ''), $commandHandler1::class);
        $this->commands->registerCommand(new Command('ant:bar', [], [], ''), $commandHandler2::class);
        $body = '<comment>ant</comment>' . \PHP_EOL
            . '  <info>ant:bar</info>' . \PHP_EOL
            . '<comment>cat</comment>' . \PHP_EOL
            . '  <info>cat:foo</info>';
        $this->output->expects($this->once())
            ->method('writeln')
            ->with(self::compileOutput($body));
        $this->handler->handle(new Input('about', [], []), $this->output);
    }

    public function testCommandsAreAlphabeticallySortedWithinCategories(): void
    {
        $commandHandler1 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $commandHandler2 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(new Command('cat:foo', [], [], ''), $commandHandler1::class);
        $this->commands->registerCommand(new Command('cat:bar', [], [], ''), $commandHandler2::class);
        $body = '<comment>cat</comment>' . \PHP_EOL
            . '  <info>cat:bar</info>' . \PHP_EOL
            . '  <info>cat:foo</info>';
        $this->output->expects($this->once())
            ->method('writeln')
            ->with(self::compileOutput($body));
        $this->handler->handle(new Input('about', [], []), $this->output);
    }

    public function testHavingNoCommandsDisplaysMessageSayingSo(): void
    {
        $body = '  <info>No commands</info>';
        $this->output->expects($this->once())
            ->method('writeln')
            ->with(self::compileOutput($body));
        $this->handler->handle(new Input('about', [], []), $this->output);
    }

    public function testUncategorizedCommandsAreListedBeforeCategorizedCommands(): void
    {
        $commandHandler1 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $commandHandler2 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $commandHandler3 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(new Command('foo', [], [], ''), $commandHandler1::class);
        $this->commands->registerCommand(new Command('cat:bar', [], [], ''), $commandHandler2::class);
        // Test a command that should come before a previous uncategorized command
        $this->commands->registerCommand(new Command('baz', [], [], ''), $commandHandler3::class);
        $body = '  <info>baz    </info>' . \PHP_EOL
            . '  <info>foo    </info>' . \PHP_EOL
            . '<comment>cat</comment>' . \PHP_EOL
            . '  <info>cat:bar</info>';
        $this->output->expects($this->once())
            ->method('writeln')
            ->with(self::compileOutput($body));
        $this->handler->handle(new Input('about', [], []), $this->output);
    }

    /**
     * Compiles the expected output with the header
     *
     * @param string $body The body that's expected
     * @return string The compiled output
     */
    private static function compileOutput(string $body): string
    {
        $template = <<<EOF
---
<b>Aphiria</b>
---
{{body}}
EOF;
        return \str_replace('{{body}}', $body, $template);
    }
}
