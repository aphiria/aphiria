<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Defaults;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\HelpCommandHandler;
use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HelpCommandHandlerTest extends TestCase
{
    /** @var IOutput|MockObject */
    private IOutput $output;
    private HelpCommandHandler $handler;
    private CommandRegistry $commands;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->handler = new HelpCommandHandler($this->commands);
        $this->output = $this->createMock(IOutput::class);
    }

    public function testHandlingCommandNameThatIsNotRegisteredReturnsError(): void
    {
        $this->output->expects($this->once())
            ->method('writeln')
            ->with('<error>Command foo does not exist</error>');
        $this->assertSame(StatusCodes::ERROR, $this->handler->handle(new Input('help', ['command' => 'foo'], []), $this->output));
    }

    public function testHandlingCommandWithHelpTextIncludesIt(): void
    {
        $this->assertCliWidthIsCalled();
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [],
                'The description',
                'The help text'
            ),
            'Handler'
        );
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(self::compileOutput('foo', 'foo', 'The description', '  No arguments', '  No options', 'The help text'));
        $this->handler->handle(new Input('help', ['command' => 'foo'], []), $this->output);
    }

    public function testHandlingWithArgumentIncludesItInArgumentDescriptionAndParsedCommand(): void
    {
        $this->assertCliWidthIsCalled();
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg1', ArgumentTypes::REQUIRED, 'Arg1 description')],
                [],
                'The description'
            ),
            'Handler'
        );
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(self::compileOutput('foo', 'foo arg1', 'The description', '  <info>arg1</info> - Arg1 description', '  No options'));
        $this->handler->handle(new Input('help', ['command' => 'foo'], []), $this->output);
    }

    public function testHandlingWithoutCommandNameWritesMessageAboutSpecifyingACommandName(): void
    {
        $this->output->expects($this->once())
            ->method('writeln')
            ->with("<comment>Pass in the name of the command you'd like help with</comment>");
        $this->assertSame(StatusCodes::OK, $this->handler->handle(new Input('help', [], []), $this->output));
    }

    public function testHandlingWithNoArgumentsStillHasDefaultArgumentDescription(): void
    {
        $this->assertCliWidthIsCalled();
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [],
                'The description'
            ),
            'Handler'
        );
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(self::compileOutput('foo', 'foo', 'The description', '  No arguments', '  No options'));
        $this->handler->handle(new Input('help', ['command' => 'foo'], []), $this->output);
    }

    public function testHandlingCommandWithNoDescriptionStillHasDefaultDescription(): void
    {
        $this->assertCliWidthIsCalled();
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [],
                ''
            ),
            'Handler'
        );
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(self::compileOutput('foo', 'foo', 'No description', '  No arguments', '  No options'));
        $this->handler->handle(new Input('help', ['command' => 'foo'], []), $this->output);
    }

    public function testHandlingCommandWithNoHelpTextDoesNotIncludeHelpText(): void
    {
        $this->assertCliWidthIsCalled();
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [],
                ''
            ),
            'Handler'
        );
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(self::compileOutput('foo', 'foo', 'No description', '  No arguments', '  No options'));
        $this->handler->handle(new Input('help', ['command' => 'foo'], []), $this->output);
    }

    public function testHandlingWithNoOptionsStillHasDefaultArgumentDescription(): void
    {
        $this->assertCliWidthIsCalled();
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [],
                'The description'
            ),
            'Handler'
        );
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(self::compileOutput('foo', 'foo', 'The description', '  No arguments', '  No options'));
        $this->handler->handle(new Input('help', ['command' => 'foo'], []), $this->output);
    }

    public function testHandlingWithOptionsIncludesOptionDescriptionsAndOptionsInParsedCommand(): void
    {
        $this->assertCliWidthIsCalled();
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt1', null, OptionTypes::REQUIRED_VALUE, 'Opt1 description')],
                'The description'
            ),
            'Handler'
        );
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(self::compileOutput('foo', 'foo [--opt1]', 'The description', '  No arguments', '  <info>--opt1</info> - Opt1 description'));
        $this->handler->handle(new Input('help', ['command' => 'foo'], []), $this->output);
    }

    public function testHandlingWithOptionWithShortNameIncludesOptionShortNameInDescriptionsAndParsedCommand(): void
    {
        $this->assertCliWidthIsCalled();
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt1', 'o', OptionTypes::REQUIRED_VALUE, 'Opt1 description')],
                'The description'
            ),
            'Handler'
        );
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(self::compileOutput('foo', 'foo [--opt1|-o]', 'The description', '  No arguments', '  <info>--opt1|-o</info> - Opt1 description'));
        $this->handler->handle(new Input('help', ['command' => 'foo'], []), $this->output);
    }

    /**
     * Compiles the help text
     *
     * @param string $helpText The help text to compile
     * @return string The compiled help text
     */
    private static function compileHelpText(string $helpText): string
    {
        if ($helpText === '') {
            return '';
        }

        return PHP_EOL . '<comment>Help:</comment>' . PHP_EOL . '  ' . $helpText;
    }

    /**
     * Compiles the expected output with the header
     *
     * @param string $commandName The name of the command
     * @param string $parsedCommand The parsed command that is output
     * @param string $description The description of the command
     * @param string $arguments The list of arguments
     * @param string $options The list of options
     * @param string $helpText The optional help text
     * @return string The compiled output
     */
    private static function compileOutput(string $commandName, string $parsedCommand, string $description, string $arguments, string $options, string $helpText = ''): string
    {
        $template = <<<EOF
---
Command: <info>{{name}}</info>
---
<b>{{command}}</b>

<comment>Description:</comment>
  {{description}}
<comment>Arguments:</comment>
{{arguments}}
<comment>Options:</comment>
{{options}}{{helpText}}
EOF;

        return \str_replace(
            ['{{name}}', '{{command}}', '{{description}}', '{{arguments}}', '{{options}}', '{{helpText}}'],
            [$commandName, $parsedCommand, $description, $arguments, $options, self::compileHelpText($helpText)],
            $template
        );
    }

    /**
     * Asserts that the CLI width is called
     */
    private function assertCliWidthIsCalled(): void
    {
        $driver = $this->createMock(IDriver::class);
        $driver->expects($this->once())
            ->method('getCliWidth')
            ->willReturn(3);
        $this->output->expects($this->at(0))
            ->method('getDriver')
            ->willReturn($driver);
    }
}
