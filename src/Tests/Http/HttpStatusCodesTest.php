<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\Net\Http\HttpStatusCodes;
use PHPUnit\Framework\TestCase;

/**
 * Tests the HTTP status codes
 */
class HttpStatusCodesTest extends TestCase
{
    public function testExistingStatusCodeReturnsDefaultStatusText(): void
    {
        $this->assertEquals('OK', HttpStatusCodes::getDefaultReasonPhrase(200));
    }

    public function testNonExistentStatusCodeReturnsNullStatusText(): void
    {
        $this->assertNull(HttpStatusCodes::getDefaultReasonPhrase(-1));
    }
}
