<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Tests;

use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\RequestContext;

/**
 * Tests the request context
 */
class RequestContextTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestContext The context to use in tests */
    private $context;
    /** @var IHttpRequestMessage The request to use in tests */
    private $request;
    /** @var ContentNegotiationResult The request content negotiation result to use in tests */
    private $requestContentNegotiationResult;
    /** @var ContentNegotiationResult The response content negotiation result to use in tests */
    private $responseContentNegotiationResult;

    public function setUp(): void
    {
        $this->request = $this->createMock(IHttpRequestMessage::class);
        $this->requestContentNegotiationResult = new ContentNegotiationResult(
            $this->createMock(IMediaTypeFormatter::class),
            null,
            null,
            null
        );
        $this->responseContentNegotiationResult = new ContentNegotiationResult(
            $this->createMock(IMediaTypeFormatter::class),
            null,
            null,
            null
        );
        $this->context = new RequestContext(
            $this->request,
            $this->requestContentNegotiationResult,
            $this->responseContentNegotiationResult
        );
    }

    public function testGettingRequestReturnsSameOneSetInConstructor(): void
    {
        $this->assertSame($this->request, $this->context->getRequest());
    }

    public function testGettingRequestContentNegotiationResultReturnsSameOneSetInConstructor(): void
    {
        $this->assertSame($this->requestContentNegotiationResult, $this->context->getRequestContentNegotiationResult());
    }

    public function testGettingResponseContentNegotiationResultReturnsSameOneSetInConstructor(): void
    {
        $this->assertSame(
            $this->responseContentNegotiationResult,
            $this->context->getResponseContentNegotiationResult()
        );
    }
}
