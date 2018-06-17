<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\ResponseFactories;

use InvalidArgumentException;
use Opulence\Api\ResponseFactories\FoundResponseFactory;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Uri;

/**
 * Tests the found response factory
 */
class FoundResponseFactoryTest extends ResponseFactoryTestCase
{
    public function testCreatingResponseAcceptsUriInstance(): void
    {
        $responseFactory = new FoundResponseFactory(new Uri('http://foo.com'));
        $response = $responseFactory->createResponse($this->createBasicRequestContext());
        $this->assertEquals('http://foo.com', $response->getHeaders()->getFirst('Location'));
    }

    public function testCreatingResponseSetsLocationHeader(): void
    {
        $responseFactory = new FoundResponseFactory('http://foo.com');
        $response = $responseFactory->createResponse($this->createBasicRequestContext());
        $this->assertEquals('http://foo.com', $response->getHeaders()->getFirst('Location'));
    }

    public function testCreatingResponseUsesCorrectStatusCode(): void
    {
        $responseFactory = new FoundResponseFactory('http://foo.com');
        $response = $responseFactory->createResponse($this->createBasicRequestContext());
        $this->assertEquals(HttpStatusCodes::HTTP_FOUND, $response->getStatusCode());
    }

    public function testCreatingResponseWithInvalidUriThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FoundResponseFactory(false);
    }
}
