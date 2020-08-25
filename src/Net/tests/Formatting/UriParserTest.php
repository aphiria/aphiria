<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Formatting;

use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Net\Formatting\UriParser;
use Aphiria\Net\Uri;
use PHPUnit\Framework\TestCase;

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
        $this->assertSame('bar', $this->parser->parseQueryString($uri)->get('foo'));
    }

    public function testUrlEncodedValuesAreDecoded(): void
    {
        $uri = new Uri('http://host.com?foo=a%26w');
        $this->assertSame('a&w', $this->parser->parseQueryString($uri)->get('foo'));
    }
}
