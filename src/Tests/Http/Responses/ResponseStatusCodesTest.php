<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Responses;

use Opulence\Net\Http\Responses\ResponseStatusCodes;

/**
 * Tests the response status codes
 */
class ResponseStatusCodesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that an existing status code returns the default status text
     */
    public function testExistingStatusCodeReturnsDefaultStatusText() : void
    {
        $this->assertEquals('OK', ResponseStatusCodes::getDefaultReasonPhrase(200));
    }

    /**
     * Tests that a non-existent status code returns a null default status text
     */
    public function testNonExistentStatusCodeReturnsNullStatusText() : void
    {
        $this->assertNull(ResponseStatusCodes::getDefaultReasonPhrase(-1));
    }
}
