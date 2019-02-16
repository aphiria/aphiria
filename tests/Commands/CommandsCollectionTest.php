<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands;

use InvalidArgumentException;
use Aphiria\Console\Commands\CommandCollection;
use Aphiria\Console\Commands\Compilers\Compiler as CommandCompiler;
use Aphiria\Console\Responses\Compilers\Compiler;
use Aphiria\Console\Responses\Compilers\Lexers\Lexer;
use Aphiria\Console\Responses\Compilers\Parsers\Parser;
use Aphiria\Console\Responses\SilentResponse;
use Aphiria\Console\Tests\Commands\Mocks\HappyHolidayCommand;
use Aphiria\Console\Tests\Commands\Mocks\SimpleCommand;
use Aphiria\Console\Tests\Responses\Mocks\Response;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command collection class
 */
class CommandsCollectionTest extends TestCase
{
    /** @var CommandCollection The list of commands to test */
    private $collection;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->collection = new CommandCollection(new CommandCompiler());
    }

    /**
     * Tests adding a command
     */
    public function testAdd(): void
    {
        $command = new SimpleCommand('foo', 'The foo command');
        $this->collection->add($command);
        $this->assertSame($command, $this->collection->get('foo'));
    }

    /**
     * Tests adding a command that already exists
     */
    public function testAddingDuplicateNames(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->collection->add(new SimpleCommand('foo', 'The foo command'));
        $this->collection->add(new SimpleCommand('foo', 'The foo command copy'));
    }

    /**
     * Tests calling a command
     */
    public function testCallingCommand(): void
    {
        $this->collection->add(new HappyHolidayCommand());
        $response = new Response(new Compiler(new Lexer(), new Parser()));
        ob_start();
        $this->collection->call('holiday', $response, ['Easter'], ['-y']);
        $this->assertEquals('Happy Easter!', ob_get_clean());
    }

    /**
     * Tests trying to call a non-existent command
     */
    public function testCallingNonExistentCommand(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->collection->call('fake', new SilentResponse(), [], []);
    }

    /**
     * Tests checking if a command exists
     */
    public function testCheckingIfCommandExists(): void
    {
        $this->collection->add(new SimpleCommand('foo', 'The foo command'));
        $this->assertTrue($this->collection->has('foo'));
        $this->assertFalse($this->collection->has('bar'));
    }

    /**
     * Tests getting all commands
     */
    public function testGettingAll(): void
    {
        $fooCommand = new SimpleCommand('foo', 'The foo command');
        $barCommand = new SimpleCommand('bar', 'The bar command');
        $this->collection->add($fooCommand);
        $this->collection->add($barCommand);
        $this->assertEquals([$fooCommand, $barCommand], $this->collection->getAll());
    }

    /**
     * Tests getting a command that does not exists
     */
    public function testGettingCommandThatDoesNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->collection->get('foo');
    }

    /**
     * Tests overwriting a command that already exists
     */
    public function testOverwritingExistingCommand(): void
    {
        $originalCommand = new SimpleCommand('foo', 'The foo command');
        $overwritingCommand = new SimpleCommand('foo', 'The foo command copy');
        $this->collection->add($originalCommand);
        $this->collection->add($overwritingCommand, true);
        $this->assertSame($overwritingCommand, $this->collection->get('foo'));
    }
}
