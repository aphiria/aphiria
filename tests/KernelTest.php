<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests;

use Aphiria\Console\Commands\CommandCollection;
use Aphiria\Console\Commands\Compilers\Compiler as CommandCompiler;
use Aphiria\Console\Kernel;
use Aphiria\Console\Requests\Parsers\StringParser;
use Aphiria\Console\Responses\Compilers\Compiler as ResponseCompiler;
use Aphiria\Console\Responses\Compilers\Lexers\Lexer;
use Aphiria\Console\Responses\Compilers\Parsers\Parser;
use Aphiria\Console\StatusCodes;
use Aphiria\Console\Tests\Commands\Mocks\HappyHolidayCommand;
use Aphiria\Console\Tests\Commands\Mocks\SimpleCommand;
use Aphiria\Console\Tests\Responses\Mocks\Response;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console kernel
 */
class KernelTest extends TestCase
{
    /** @var CommandCompiler The command compiler */
    private $compiler;
    /** @var CommandCollection The list of commands */
    private $commands;
    /** @var StringParser The request parser */
    private $parser;
    /** @var Response The response to use in tests */
    private $response;
    /** @var Kernel The kernel to use in tests */
    private $kernel;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->compiler = new CommandCompiler();
        $this->commands = new CommandCollection($this->compiler);
        $this->commands->add(new SimpleCommand('mockcommand', 'Mocks a command'));
        $this->commands->add(new HappyHolidayCommand());
        $this->parser = new StringParser();
        $this->response = new Response(new ResponseCompiler(new Lexer(), new Parser()));
        $this->kernel = new Kernel(
            $this->parser,
            $this->compiler,
            $this->commands
        );
    }

    /**
     * Tests handling an exception
     */
    public function testHandlingException(): void
    {
        ob_start();
        $status = $this->kernel->handle("unclosed quote '", $this->response);
        ob_end_clean();
        $this->assertEquals(StatusCodes::FATAL, $status);
    }

    /**
     * Tests handling a help command
     */
    public function testHandlingHelpCommand(): void
    {
        // Try with command name
        ob_start();
        $status = $this->kernel->handle('help holiday', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with command name with no argument
        ob_start();
        $status = $this->kernel->handle('help', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with short name
        ob_start();
        $status = $this->kernel->handle('holiday -h', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with long name
        ob_start();
        $status = $this->kernel->handle('holiday --help', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }

    /**
     * Tests handling help command with non-existent command
     */
    public function testHandlingHelpCommandWithNonExistentCommand(): void
    {
        ob_start();
        $status = $this->kernel->handle('help fake', $this->response);
        ob_end_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    /**
     * Tests handling command with arguments and options
     */
    public function testHandlingHolidayCommand(): void
    {
        // Test with short option
        ob_start();
        $status = $this->kernel->handle('holiday birthday -y', $this->response);
        $this->assertEquals('Happy birthday!', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);

        // Test with long option
        ob_start();
        $status = $this->kernel->handle('holiday Easter --yell=no', $this->response);
        $this->assertEquals('Happy Easter', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    /**
     * Tests handling in a missing command
     */
    public function testHandlingMissingCommand(): void
    {
        ob_start();
        $status = $this->kernel->handle('fake', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }

    /**
     * Tests handling in a simple command
     */
    public function testHandlingSimpleCommand(): void
    {
        ob_start();
        $status = $this->kernel->handle('mockcommand', $this->response);
        $this->assertEquals('foo', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    /**
     * Tests handling a version command
     */
    public function testHandlingVersionCommand(): void
    {
        // Try with short name
        ob_start();
        $status = $this->kernel->handle('-v', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with long name
        ob_start();
        $status = $this->kernel->handle('--version', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }
}
