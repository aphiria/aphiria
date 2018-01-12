<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\Net\Http\HttpStatusCodes;

/**
 * Tests the HTTP status codes
 */
class HttpStatusCodesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that an existing status code returns the default status text
     */
    public function testExistingStatusCodeReturnsDefaultStatusText() : void
    {
        $this->assertEquals('OK', HttpStatusCodes::getDefaultReasonPhrase(200));
    }

    /**
     * Tests that a non-existent status code returns a null default status text
     */
    public function testNonExistentStatusCodeReturnsNullStatusText() : void
    {
        $this->assertNull(HttpStatusCodes::getDefaultReasonPhrase(-1));
    }
}
