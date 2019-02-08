<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http\ContentNegotiation;

use Aphiria\Net\Http\IHttpRequestMessage;
use InvalidArgumentException;

/**
 * Defines the interface for content negotiators to implement
 */
interface IContentNegotiator
{
    /**
     * Gets the list of acceptable response media types for a particular type
     *
     * @param string $type The type to check for (best to use TypeResolver::resolveType())
     * @return array The list of acceptable media types
     */
    public function getAcceptableResponseMediaTypes(string $type): array;

    /**
     * Gets the negotiation result for the request body
     *
     * @param string $type The type to negotiate (best to use TypeResolver::resolveType())
     * @param IHttpRequestMessage $request The request to negotiate with
     * @return ContentNegotiationResult The content negotiation result
     * @throws InvalidArgumentException Thrown if the Content-Type header was incorrectly formatted
     */
    public function negotiateRequestContent(string $type, IHttpRequestMessage $request): ContentNegotiationResult;

    /**
     * Gets the negotiation result for the response body
     *
     * @param string $type The type to negotiate
     * @param IHttpRequestMessage $request The request to negotiate with
     * @return ContentNegotiationResult The content negotiation result
     * @throws InvalidArgumentException Thrown if the Accept header's media types were incorrectly formatted
     */
    public function negotiateResponseContent(string $type, IHttpRequestMessage $request): ContentNegotiationResult;
}
