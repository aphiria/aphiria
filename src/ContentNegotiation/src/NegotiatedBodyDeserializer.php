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
 * Defines a body deserializer that uses content negotiation
 */
final class NegotiatedBodyDeserializer implements IBodyDeserializer
{
    /**
     * @param IContentNegotiator $contentNegotiator The content negotiator to use when deserializing message bodies
     */
    public function __construct(private readonly IContentNegotiator $contentNegotiator = new ContentNegotiator())
    {
    }

    /**
     * @inheritdoc
     */
    public function readRequestBodyAs(string $type, IRequest $request): float|object|int|bool|array|string|null
    {
        if (($body = $request->getBody()) === null) {
            if (\str_ends_with($type, '[]')) {
                return [];
            }

            return null;
        }

        $contentNegotiationResult = $this->contentNegotiator->negotiateRequestContent($type, $request);
        $mediaTypeFormatter = $contentNegotiationResult->formatter;

        if ($mediaTypeFormatter === null) {
            throw new FailedContentNegotiationException("No media type formatter available for $type");
        }

        return $mediaTypeFormatter->readFromStream($body->readAsStream(), $type);
    }

    /**
     * @inheritdoc
     */
    public function readResponseBodyAs(string $type, IRequest $request, IResponse $response): float|object|int|bool|array|string|null
    {
        if (($body = $response->getBody()) === null) {
            if (\str_ends_with($type, '[]')) {
                return [];
            }

            return null;
        }

        $contentNegotiationResult = $this->contentNegotiator->negotiateResponseContent($type, $request);
        $mediaTypeFormatter = $contentNegotiationResult->formatter;

        if ($mediaTypeFormatter === null) {
            throw new FailedContentNegotiationException("No media type formatter available for $type");
        }

        return $mediaTypeFormatter->readFromStream($body->readAsStream(), $type);
    }
}
