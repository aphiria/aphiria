<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ContentNegotiation;

use InvalidArgumentException;
use Opulence\Net\Http\IHttpRequestMessage;

/**
 * Defines the interface for content negotiators to implement
 */
interface IContentNegotiator
{
    /**
     * Gets the negotiation result for the request body
     *
     * @param IHttpRequestMessage $request The request to negotiate with
     * @return ContentNegotiationResult The content negotiation result
     * @throws InvalidArgumentException Thrown if the Content-Type header was incorrectly formatted
     */
    public function negotiateRequestContent(IHttpRequestMessage $request): ContentNegotiationResult;

    /**
     * Gets the negotiation result for the response body
     *
     * @param IHttpRequestMessage $request The request to negotiate with
     * @return ContentNegotiationResult The content negotiation result
     * @throws InvalidArgumentException Thrown if the Accept header's media types were incorrectly formatted
     */
    public function negotiateResponseContent(IHttpRequestMessage $request): ContentNegotiationResult;
}
