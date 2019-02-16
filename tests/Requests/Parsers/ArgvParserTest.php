<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Requests\Parsers;

use Aphiria\Console\Requests\Parsers\ArgvParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the argv parser
 */
class ArgvParserTest extends TestCase
{
    /** @var ArgvParser The parser to use in tests */
    private $parser;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->parser = new ArgvParser();
    }

    /**
     * Tests that backslashes are respected
     */
    public function testBackslashesAreRespected(): void
    {
        $request = $this->parser->parse(['apex', 'foo', 'bar\\baz']);
        $this->assertEquals(['bar\\baz'], $request->getArgumentValues());
    }

    /**
     * Tests parsing arguments and options
     */
    public function testParsingArgumentsAndOptions(): void
    {
        $request = $this->parser->parse(['apex', 'foo', 'bar', '-r', '--name=dave']);
        $this->assertEquals('foo', $request->getCommandName());
        $this->assertEquals(['bar'], $request->getArgumentValues());
        $this->assertNull($request->getOptionValue('r'));
        $this->assertEquals('dave', $request->getOptionValue('name'));
    }

    /**
     * Tests parsing a null string
     */
    public function testParsingNullString(): void
    {
        $_SERVER['argv'] = ['apex', 'foo', 'bar', '-r', '--name=dave'];
        $request = $this->parser->parse(null);
        $this->assertEquals('foo', $request->getCommandName());
        $this->assertEquals(['bar'], $request->getArgumentValues());
        $this->assertNull($request->getOptionValue('r'));
        $this->assertEquals('dave', $request->getOptionValue('name'));
    }

    /**
     * Tests parsing option with no value
     */
    public function testParsingOptionWithNoValue(): void
    {
        $request = $this->parser->parse(['apex', 'foo', '--name']);
        $this->assertNull($request->getOptionValue('name'));
    }

    /**
     * Tests passing in an invalid input type
     */
    public function testPassingInvalidInputType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->parse('foo');
    }
}
