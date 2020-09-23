<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
    /** @var IMediaTypeFormatterMatcher The media type formatter matcher */
    private IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher;
    /** @var string The default content type to use for bodies */
    private string $defaultContentType;

    /**
     * @param IMediaTypeFormatterMatcher|null $mediaTypeFormatterMatcher The media type formatter matcher, or null if using the default one
     * @param string $defaultContentType The default content type to use for bodies
     * @param string $defaultAccept The default Accept header value
     */
    public function __construct(
        IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher = null,
        string $defaultContentType = 'application/json',
        string $defaultAccept = '*/*'
    ) {
        parent::__construct();

        $this->mediaTypeFormatterMatcher = $mediaTypeFormatterMatcher ?? new MediaTypeFormatterMatcher([
                new JsonMediaTypeFormatter(),
                new XmlMediaTypeFormatter(),
                new HtmlMediaTypeFormatter(),
                new PlainTextMediaTypeFormatter()
            ]);
        $this->defaultContentType = $defaultContentType;
        $this->headers->add('Accept', $defaultAccept);
    }

    /**
     * @inheritdoc
     * @param IBody|object|array|mixed $body The body to set
     * @throws SerializationException Thrown if the body could not be serialized
     */
    public function withBody($body): NegotiatedRequestBuilder
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

            $encoding = $mediaTypeFormatterMatch->getFormatter()->getDefaultEncoding();
            $bodyStream = new Stream(\fopen('php://temp', 'w+b'));
            $mediaTypeFormatterMatch->getFormatter()->writeToStream($body, $bodyStream, $encoding);
            $new->body = new StreamBody($bodyStream);
            $new->headers->add('Content-Type', $mediaTypeFormatterMatch->getMediaType());
        } else {
            throw new InvalidArgumentException('Body must either implement ' . IBody::class . ' or be an array, object, or scalar');
        }

        return $new;
    }
}
