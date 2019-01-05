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

/**
 * Tests the HTTP status codes
 */
class HttpStatusCodesTest extends \PHPUnit\Framework\TestCase
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
