<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Formatting\ResponseHeaderParser;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\Cookie;
use PHPUnit\Framework\TestCase;

class ResponseHeaderParserTest extends TestCase
{
    private ResponseHeaderParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ResponseHeaderParser();
    }

    public function testParsingCookiesParsesAllAvailableParametersIntoCookies(): void
    {
        $headers = new Headers([
            new KeyValuePair('Set-Cookie', 'foo=value; Max-Age=3600; Domain=example.com; Path=/path; HttpOnly; Secure; SameSite=strict')
        ]);
        $expectedCookies = [
            new Cookie('foo', 'value', 3600, '/path', 'example.com', true, true, Cookie::SAME_SITE_STRICT)
        ];
        $this->assertEquals($expectedCookies, $this->parser->parseCookies($headers));
    }

    public function testParsingCookiesParsesMultipleCookies(): void
    {
        $headers = new Headers();
        $headers->add('Set-Cookie', 'foo=value1; Max-Age=3600; Domain=example1.com; Path=/path1; HttpOnly; Secure; SameSite=strict');
        $headers->add('Set-Cookie', 'bar=value2; Max-Age=7200; Domain=example2.com; Path=/path2; HttpOnly; Secure; SameSite=strict', true);
        $expectedCookies = [
            new Cookie('foo', 'value1', 3600, '/path1', 'example1.com', true, true, Cookie::SAME_SITE_STRICT),
            new Cookie('bar', 'value2', 7200, '/path2', 'example2.com', true, true, Cookie::SAME_SITE_STRICT)
        ];
        $this->assertEquals($expectedCookies, $this->parser->parseCookies($headers));
    }

    public function testParsingCookiesWithoutAnySetReturnsEmptyList(): void
    {
        $this->assertEmpty($this->parser->parseCookies(new Headers()));
    }
}
