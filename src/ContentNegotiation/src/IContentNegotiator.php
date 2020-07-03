<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\Net\Http\IRequest;
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
     * @param IRequest $request The request to negotiate with
     * @return ContentNegotiationResult The content negotiation result
     * @throws InvalidArgumentException Thrown if the Content-Type header was incorrectly formatted
     */
    public function negotiateRequestContent(string $type, IRequest $request): ContentNegotiationResult;

    /**
     * Gets the negotiation result for the response body
     *
     * @param string $type The type to negotiate
     * @param IRequest $request The request to negotiate with
     * @return ContentNegotiationResult The content negotiation result
     * @throws InvalidArgumentException Thrown if the Accept header's media types were incorrectly formatted
     */
    public function negotiateResponseContent(string $type, IRequest $request): ContentNegotiationResult;
}
