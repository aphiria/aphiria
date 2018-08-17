<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;

/**
 * Defines the request context
 */
class RequestContext
{
    /** @var IHttpRequestMessage The request in the context */
    private $request;
    /** @var ContentNegotiationResult The request content negotiation result */
    private $requestContentNegotiationResult;
    /** @var ContentNegotiationResult The response content negotiation result */
    private $responseContentNegotiationResult;

    /**
     * @param IHttpRequestMessage $request The request in the context
     * @param ContentNegotiationResult $requestContentNegotiationResult The request content negotiation result
     * @param ContentNegotiationResult $responseContentNegotiationResult The response content negotiation result
     */
    public function __construct(
        IHttpRequestMessage $request,
        ContentNegotiationResult $requestContentNegotiationResult,
        ContentNegotiationResult $responseContentNegotiationResult
    ) {
        $this->request = $request;
        $this->requestContentNegotiationResult = $requestContentNegotiationResult;
        $this->responseContentNegotiationResult = $responseContentNegotiationResult;
    }

    /**
     * Gets the request
     *
     * @return IHttpRequestMessage The request
     */
    public function getRequest(): IHttpRequestMessage
    {
        return $this->request;
    }

    /**
     * Gets the request content negotiation result
     *
     * @return ContentNegotiationResult The request content negotiation result
     */
    public function getRequestContentNegotiationResult(): ContentNegotiationResult
    {
        return $this->requestContentNegotiationResult;
    }

    /**
     * Gets the response content negotiation result
     *
     * @return ContentNegotiationResult The response content negotiation result
     */
    public function getResponseContentNegotiationResult(): ContentNegotiationResult
    {
        return $this->responseContentNegotiationResult;
    }
}
