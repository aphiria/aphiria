<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Defaults;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the about command handler
 */
class AboutCommandHandlerTest extends TestCase
{
    private AboutCommandHandler $handler;
    private CommandRegistry $commands;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->handler = new AboutCommandHandler($this->commands);
    }

    public function testCommandsAreAlphabeticallySortedByCategories(): void
    {
        $this->commands->registerCommand(new Command('cat:foo', [], [], ''), $this->createCommandHandlerFactory());
        $this->commands->registerCommand(new Command('ant:bar', [], [], ''), $this->createCommandHandlerFactory());
        /** @var IOutput|MockObject $output */
        $output = $this->createMock(IOutput::class);
        $body = '<comment>ant</comment>' . \PHP_EOL
            . '  <info>ant:bar</info>' . \PHP_EOL
            . '<comment>cat</comment>' . \PHP_EOL
            . '  <info>cat:foo</info>';
        $output->expects($this->once())
            ->method('writeln')
            ->with(self::compileOutput($body));
        $this->handler->handle(new Input('about', [], []), $output);
    }

    public function testCommandsAreAlphabeticallySortedWithinCategories(): void
    {
        $this->commands->registerCommand(new Command('cat:foo', [], [], ''), $this->createCommandHandlerFactory());
        $this->commands->registerCommand(new Command('cat:bar', [], [], ''), $this->createCommandHandlerFactory());
        /** @var IOutput|MockObject $output */
        $output = $this->createMock(IOutput::class);
        $body = '<comment>cat</comment>' . \PHP_EOL
            . '  <info>cat:bar</info>' . \PHP_EOL
            . '  <info>cat:foo</info>';
        $output->expects($this->once())
            ->method('writeln')
            ->with(self::compileOutput($body));
        $this->handler->handle(new Input('about', [], []), $output);
    }

    public function testUncategorizedCommandsAreListedBeforeCategorizedCommands(): void
    {
        $this->commands->registerCommand(new Command('foo', [], [], ''), $this->createCommandHandlerFactory());
        $this->commands->registerCommand(new Command('cat:bar', [], [], ''), $this->createCommandHandlerFactory());
        /** @var IOutput|MockObject $output */
        $output = $this->createMock(IOutput::class);
        $body = '  <info>foo    </info>' . \PHP_EOL
            . '<comment>cat</comment>' . \PHP_EOL
            . '  <info>cat:bar</info>';
        $output->expects($this->once())
            ->method('writeln')
            ->with(self::compileOutput($body));
        $this->handler->handle(new Input('about', [], []), $output);
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
-----------------------------
About <b>Aphiria</b>
-----------------------------
{{body}}
EOF;
        return \str_replace('{{body}}', $body, $template);
    }

    /**
     * Creates a closure that returns a mock command handler
     *
     * @return Closure The closure that creates a mock command handler
     */
    private function createCommandHandlerFactory(): Closure
    {
        return fn () => $this->createMock(ICommandHandler::class);
    }
}
