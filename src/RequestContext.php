<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api;

use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Routing\Matchers\MatchedRoute;

/**
 * Defines the request context
 */
class RequestContext
{
    /** @var IHttpRequestMessage The request in the context */
    private $request;
    /** @var ContentNegotiationResult|null The request content negotiation result */
    private $requestContentNegotiationResult;
    /** @var ContentNegotiationResult|null The response content negotiation result */
    private $responseContentNegotiationResult;
    /** @var MatchedRoute The matched route in the context */
    private $matchedRoute;

    /**
     * @param IHttpRequestMessage $request The request in the context
     * @param ContentNegotiationResult|null $requestContentNegotiationResult The request content negotiation result
     * @param ContentNegotiationResult|null $responseContentNegotiationResult The response content negotiation result
     * @param MatchedRoute $matchedRoute The matched route in the context
     */
    public function __construct(
        IHttpRequestMessage $request,
        ?ContentNegotiationResult $requestContentNegotiationResult,
        ?ContentNegotiationResult $responseContentNegotiationResult,
        MatchedRoute $matchedRoute
    ) {
        $this->request = $request;
        $this->requestContentNegotiationResult = $requestContentNegotiationResult;
        $this->responseContentNegotiationResult = $responseContentNegotiationResult;
        $this->matchedRoute = $matchedRoute;
    }

    /**
     * Gets the matched route
     *
     * @return MatchedRoute The matched route
     */
    public function getMatchedRoute(): MatchedRoute
    {
        return $this->matchedRoute;
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
     * @return ContentNegotiationResult|null The request content negotiation result if there was one, otherwise null
     */
    public function getRequestContentNegotiationResult(): ?ContentNegotiationResult
    {
        return $this->requestContentNegotiationResult;
    }

    /**
     * Gets the response content negotiation result
     *
     * @return ContentNegotiationResult|null The response content negotiation result if there was one, otherwise null
     */
    public function getResponseContentNegotiationResult(): ?ContentNegotiationResult
    {
        return $this->responseContentNegotiationResult;
    }
}
