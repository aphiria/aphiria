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
     * @param IMediaTypeFormatter[] $mediaTypeFormatters The list of media type formatters to match against
     * @return ContentNegotiationResult|null The content negotiation result if found, otherwise null
     * @throws InvalidArgumentException Thrown if the Content-Type header was incorrectly formatted or the formatter list is empty
     */
    public function negotiateRequestContent(
        IHttpRequestMessage $request,
        array $mediaTypeFormatters
    ): ?ContentNegotiationResult;

    /**
     * Gets the negotiation result for the response body
     *
     * @param IHttpRequestMessage $request The request to negotiate with
     * @param IMediaTypeFormatter[] $mediaTypeFormatters The list of media type formatters to match against
     * @param array $supportedLanguages The list of supported languages
     * @return ContentNegotiationResult|null The content negotiation result if found, otherwise null
     * @throws InvalidArgumentException Thrown if the Accept header's media types were incorrectly formatted or the formatter list is empty
     */
    public function negotiateResponseContent(
        IHttpRequestMessage $request,
        array $mediaTypeFormatters,
        array $supportedLanguages
    ): ?ContentNegotiationResult;
}
