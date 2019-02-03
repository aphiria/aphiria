<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\HttpStatusCodes;
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
