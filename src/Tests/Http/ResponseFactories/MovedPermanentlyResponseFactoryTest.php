<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\ResponseFactories;

use InvalidArgumentException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\ResponseFactories\MovedPermanentlyResponseFactory;
use Opulence\Net\Uri;

/**
 * Tests the moved permanently response factory
 */
class MovedPermanentlyResponseFactoryTest extends ResponseFactoryTestCase
{
    public function testCreatingResponseAcceptsUriInstance(): void
    {
        $responseFactory = new MovedPermanentlyResponseFactory(new Uri('http://foo.com'));
        $response = $responseFactory->createResponse($this->createBasicRequestContext());
        $this->assertEquals('http://foo.com', $response->getHeaders()->getFirst('Location'));
    }

    public function testCreatingResponseSetsLocationHeader(): void
    {
        $responseFactory = new MovedPermanentlyResponseFactory('http://foo.com');
        $response = $responseFactory->createResponse($this->createBasicRequestContext());
        $this->assertEquals('http://foo.com', $response->getHeaders()->getFirst('Location'));
    }

    public function testCreatingResponseUsesCorrectStatusCode(): void
    {
        $responseFactory = new MovedPermanentlyResponseFactory('http://foo.com');
        $response = $responseFactory->createResponse($this->createBasicRequestContext());
        $this->assertEquals(HttpStatusCodes::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
    }

    public function testCreatingResponseWithInvalidUriThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MovedPermanentlyResponseFactory(false);
    }
}
