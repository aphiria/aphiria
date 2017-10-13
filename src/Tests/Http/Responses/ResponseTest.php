<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Responses;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\Responses\Response;
use Opulence\Net\Http\Responses\ResponseStatusCodes;

/**
 * Tests the response class
 */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the default reason phrase is set
     */
    public function testDefaultReasonPhraseIsSet() : void
    {
        $response = new Response(200);
        $this->assertEquals(ResponseStatusCodes::getDefaultReasonPhrase(200), $response->getReasonPhrase());
    }

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
        $headers = new HttpHeaders();
        $response = new Response(200, $headers);
        $this->assertSame($headers, $response->getHeaders());
    }

    /**
     * Tests getting the protocol version
     */
    public function testGettingProtocolVersion() : void
    {
        $response = new Response(200, null, null, '2.0');
        $this->assertEquals('2.0', $response->getProtocolVersion());
    }
}
