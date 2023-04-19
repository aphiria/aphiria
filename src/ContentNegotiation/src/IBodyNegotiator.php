<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;

/**
 * Defines the interface for HTTP body negotiators to implement
 */
interface IBodyNegotiator
{
    /**
     * Negotiates the request body and returns it as the input type
     *
     * @return float|object|int|bool|array|string|null The negotiated request body
     * @param IRequest $request The request whose body we want to negotiate
     * @param string $type The type to deserialize the request body to
     * @throws FailedContentNegotiationException Thrown if there was an error negotiating the request body
     * @throws SerializationException Thrown if there was an error deserializing the request body
     */
    public function negotiateRequestBody(string $type, IRequest $request): float|object|int|bool|array|string|null;

    /**
     * Negotiates the response body and returns it as the input type
     *
     * @param string $type The type to deserialize the response body to
     * @param IRequest $request The request that was used to create the response
     * @param IResponse $response The response whose body we want to negotiate
     * @return float|object|int|bool|array|string|null The negotiated response body
     * @throws FailedContentNegotiationException Thrown if there was an error negotiating the response body
     * @throws SerializationException Thrown if there was an error deserializing the response body
     */
    public function negotiateResponseBody(string $type, IRequest $request, IResponse $response): float|object|int|bool|array|string|null;
}
