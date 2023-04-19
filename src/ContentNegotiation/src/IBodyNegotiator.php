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

use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;

/**
 * Defines the interface for HTTP body negotiators to implement
 *
 * TODO: Should this be IBodyResolver or something similar?
 */
interface IBodyNegotiator
{
    /**
     * Negotiates the request body and returns it as the input type
     *
     * @param IRequest $request The request whose body we want to negotiate
     * @param string $type The type to deserialize the request body to
     * @return float|object|int|bool|array|string|null The negotiated request body
     * TODO: We need an exception in case content negotiate failed (might need to move FailedContentNegotiationException from Api to this project)
     */
    public function negotiateRequestBody(IRequest $request, string $type): float|object|int|bool|array|string|null;

    /**
     * Negotiates the response body and returns it as the input type
     *
     * @param IRequest $request The request that was used to create the response
     * @param IResponse $response The response whose body we want to negotiate
     * @param string $type The type to deserialize the response body to
     * @return float|object|int|bool|array|string|null The negotiated response body
     * TODO: We need an exception in case content negotiate failed (might need to move FailedContentNegotiationException from Api to this project)
     */
    public function negotiateResponseBody(IRequest $request, IResponse $response, string $type): float|object|int|bool|array|string|null;
}
