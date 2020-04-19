<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Defaults;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use PHPUnit\Framework\TestCase;

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
        $this->commands->registerCommand(new Command('cat:foo', [], [], ''), 'Handler1');
        $this->commands->registerCommand(new Command('ant:bar', [], [], ''), 'Handler2');
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
        $this->commands->registerCommand(new Command('cat:foo', [], [], ''), 'Handler1');
        $this->commands->registerCommand(new Command('cat:bar', [], [], ''), 'Handler2');
        $output = $this->createMock(IOutput::class);
        $body = '<comment>cat</comment>' . \PHP_EOL
            . '  <info>cat:bar</info>' . \PHP_EOL
            . '  <info>cat:foo</info>';
        $output->expects($this->once())
            ->method('writeln')
            ->with(self::compileOutput($body));
        $this->handler->handle(new Input('about', [], []), $output);
    }

    public function testHavingNoCommandsDisplaysMessageSayingSo(): void
    {
        $output = $this->createMock(IOutput::class);
        $body = '  <info>No commands</info>';
        $output->expects($this->once())
            ->method('writeln')
            ->with(self::compileOutput($body));
        $this->handler->handle(new Input('about', [], []), $output);
    }

    public function testUncategorizedCommandsAreListedBeforeCategorizedCommands(): void
    {
        $this->commands->registerCommand(new Command('foo', [], [], ''), 'Handler1');
        $this->commands->registerCommand(new Command('cat:bar', [], [], ''), 'Handler2');
        // Test a command that should come before a previous uncategorized command
        $this->commands->registerCommand(new Command('baz', [], [], ''), 'Handler3');
        $output = $this->createMock(IOutput::class);
        $body = '  <info>baz    </info>' . \PHP_EOL
            . '  <info>foo    </info>' . \PHP_EOL
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
}
