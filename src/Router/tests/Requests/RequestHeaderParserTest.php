<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Requests;

use Aphiria\Routing\Requests\RequestHeaderParser;
use PHPUnit\Framework\TestCase;

/**
 * Tests the request header parser
 */
class RequestHeaderParserTest extends TestCase
{
    /** @var array The $_SERVER super global to use */
    private static array $serverArray = [
        'NON_HEADER' => 'foo',
        'CONTENT_LENGTH' => 4,
        'CONTENT_TYPE' => 'foo',
        'HTTP_ACCEPT' => 'accept',
        'HTTP_ACCEPT_CHARSET' => 'accept_charset',
        'HTTP_ACCEPT_ENCODING' => 'accept_encoding',
        'HTTP_ACCEPT_LANGUAGE' => 'accept_language',
        'HTTP_CONNECTION' => 'connection',
        'HTTP_HOST' => 'host',
        'HTTP_REFERER' => 'referer',
        'HTTP_USER_AGENT' => 'user_agent'
    ];
    private RequestHeaderParser $headerParser;

    protected function setUp(): void
    {
        $this->headerParser = new RequestHeaderParser();
    }

    public function testParsingRawHeaderValuesReturnsCorrectValues(): void
    {
        $expectedHeaders = [];

        foreach (self::$serverArray as $key => $value) {
            if (\strpos(\strtoupper($key), 'HTTP_') === 0) {
                if (!\is_array($value)) {
                    $value = [$value];
                }

                $expectedHeaders[$this->normalizeName($key)] = $value;
            } elseif (\strpos(\strtoupper($key), 'CONTENT_') === 0) {
                if (!\is_array($value)) {
                    $value = [$value];
                }

                $expectedHeaders[$this->normalizeName($key)] = $value;
            }
        }

        $this->assertEquals($expectedHeaders, $this->headerParser->parseHeaders(self::$serverArray));
    }

    /**
     * Normalizes a name
     *
     * @param string $name The name to normalize
     * @return string The normalized name
     */
    private function normalizeName($name): string
    {
        $dashedName = \strtr($name, '_', '-');

        if (\strpos(\strtoupper($dashedName), 'HTTP-') === 0) {
            $dashedName = \substr($dashedName, 5);
        }

        return $dashedName;
    }
}
