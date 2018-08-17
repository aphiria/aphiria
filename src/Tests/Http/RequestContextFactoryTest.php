<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\RequestContextFactory;

/**
 * Tests the request context
 */
class RequestContextFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestContextFactory The factory to use in tests */
    private $factory;
    /** @var IContentNegotiator|\PHPUnit_Framework_MockObject_MockObject The mock content negotiator to use in tests */
    private $contentNegotiator;

    public function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->factory = new RequestContextFactory($this->contentNegotiator);
    }

    public function testCreateRequestContextUsesContentNegotiator(): void
    {
        $expectedRequest = $this->createMock(IHttpRequestMessage::class);
        $expectedRequestResult = new ContentNegotiationResult(null, null, null, null);
        $expectedResponseResult = new ContentNegotiationResult(null, null, null, null);
        $this->contentNegotiator->expects($this->once())
            ->method('negotiateRequestContent')
            ->with($expectedRequest)
            ->willReturn($expectedRequestResult);
        $this->contentNegotiator->expects($this->once())
            ->method('negotiateResponseContent')
            ->with($expectedRequest)
            ->willReturn($expectedResponseResult);
        $requestContext = $this->factory->createRequestContext($expectedRequest);
        $this->assertSame($expectedRequest, $requestContext->getRequest());
        $this->assertSame($expectedRequestResult, $requestContext->getRequestContentNegotiationResult());
        $this->assertSame($expectedResponseResult, $requestContext->getResponseContentNegotiationResult());
    }
}
