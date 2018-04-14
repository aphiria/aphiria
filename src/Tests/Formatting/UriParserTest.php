<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Formatting;

use Opulence\Net\Formatting\UriParser;
use Opulence\Net\Uri;

/**
 * Tests the URI parser
 */
class UriParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var UriParser The URI parser to use in tests */
    private $parser;

    public function setUp(): void
    {
        $this->parser = new UriParser();
    }

    public function testParsingQueryStringParamWithMultipleValuesReturnsArrayOfValues(): void
    {
        $uri = new Uri('http://host.com?foo[]=bar&foo[]=baz');
        $values = $this->parser->parseQueryString($uri);
        $this->assertEquals(['bar', 'baz'], $values->get('foo'));
    }

    public function testParsingQueryStringParamWithoutValueReturnsFalse(): void
    {
        $uri = new Uri('http://host.com?foo=bar');
        $this->assertFalse($this->parser->parseQueryString($uri)->containsKey('baz'));
    }

    public function testParsingQueryStringParamWithSingleValueReturnsThatValue(): void
    {
        $uri = new Uri('http://host.com?foo=bar');
        $this->assertEquals('bar', $this->parser->parseQueryString($uri)->get('foo'));
    }

    public function testUrlEncodedValuesAreDecoded(): void
    {
        $uri = new Uri('http://host.com?foo=a%26w');
        $this->assertEquals('a&w', $this->parser->parseQueryString($uri)->get('foo'));
    }
}
