<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\ContentNegotiation\MediaTypeFormatters\HtmlMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\PlainTextMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\ContentNegotiation\MediaTypeFormatters\XmlMediaTypeFormatter;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\RequestBuilder;
use Aphiria\Net\Http\StreamBody;
use Aphiria\Net\Uri;
use Aphiria\Reflection\TypeResolver;
use InvalidArgumentException;

/**
 * Defines a negotiated request builder
 */
class NegotiatedRequestBuilder extends RequestBuilder
{
    /**
     * @param IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher The media type formatter matcher
     * @param string $defaultContentType The default content type to use for bodies
     * @param string $defaultAccept The default Accept header value
     */
    public function __construct(
        private readonly IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher = new MediaTypeFormatterMatcher([
            new JsonMediaTypeFormatter(),
            new XmlMediaTypeFormatter(),
            new HtmlMediaTypeFormatter(),
            new PlainTextMediaTypeFormatter()
        ]),
        private readonly string $defaultContentType = 'application/json',
        string $defaultAccept = '*/*'
    ) {
        parent::__construct();

        $this->headers->add('Accept', $defaultAccept);
    }

    /**
     * @inheritdoc
     * @param mixed $body The body to set
     * @throws SerializationException Thrown if the body could not be serialized
     */
    public function withBody(mixed $body): static
    {
        $new = clone $this;

        if ($body === null) {
            $new->body = null;
        } elseif ($body instanceof IBody) {
            $new->body = $body;
        } elseif (\is_array($body) || \is_object($body) || \is_scalar($body)) {
            $type = TypeResolver::resolveType($body);

            if (!$new->headers->containsKey('Content-Type')) {
                $new->headers->add('Content-Type', $this->defaultContentType);
            }

            // Grab the media type formatter from a dummy request that has the same headers
            $mediaTypeFormatterMatch = $new->mediaTypeFormatterMatcher->getBestRequestMediaTypeFormatterMatch(
                $type,
                new Request($new->method ?? 'GET', $new->uri ?? new Uri('http://localhost'), $new->headers)
            );

            if ($mediaTypeFormatterMatch === null) {
                throw new InvalidArgumentException("No media type formatter available for $type");
            }

            $encoding = $mediaTypeFormatterMatch->formatter->getDefaultEncoding();
            $bodyStream = new Stream(\fopen('php://temp', 'w+b'));
            $mediaTypeFormatterMatch->formatter->writeToStream($body, $bodyStream, $encoding);
            $new->body = new StreamBody($bodyStream);
            $new->headers->add('Content-Type', $mediaTypeFormatterMatch->mediaType);
        } else {
            throw new InvalidArgumentException('Body must either implement ' . IBody::class . ' or be an array, object, or scalar');
        }

        return $new;
    }
}
