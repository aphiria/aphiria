<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpHeaders;

/**
 * Tests the response class
 */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests getting and setting the body
     */
    public function testGettingAndSettingBody() : void
    {
        $body1 = $this->createMock(IHttpBody::class);
        $response = new Response(200, null, $body1);
        $this->assertSame($body1, $response->getBody());
        $body2 = $this->createMock(IHttpBody::class);
        $response->setBody($body2);
        $this->assertSame($body2, $response->getBody());
    }
    
    /**
     * Tests getting and setting the status code
     */
    public function testGettingAndSettingStatusCode() : void
    {
        $response = new Response(201);
        $this->assertEquals(201, $response->getStatusCode());
        $response->setStatusCode(202);
        $this->assertEquals(202, $response->getStatusCode());
    }
    
    /**
     * Tests getting headers
     */
    public function testGettingHeaders() : void
    {
        $headers = $this->createMock(IHttpHeaders::class);
        $response = new Response(200, $headers);
        $this->assertSame($headers, $response->getHeaders());
    }
}
