<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Formatting;

use Aphiria\Net\Formatting\UriParser;
use Aphiria\Net\Uri;
use Aphiria\Collections\ImmutableHashTable;
use PHPUnit\Framework\TestCase;

/**
 * Tests the URI parser
 */
class UriParserTest extends TestCase
{
    private UriParser $parser;

    protected function setUp(): void
    {
        $this->parser = new UriParser();
    }

    public function testParsingEmptyQueryStringReturnsEmptyDictionary(): void
    {
        $uri = new Uri('http://host.com');
        $this->assertEquals(new ImmutableHashTable([]), $this->parser->parseQueryString($uri));
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
