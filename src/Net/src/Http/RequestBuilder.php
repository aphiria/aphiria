<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\Collections\HashTable;
use Aphiria\Collections\IDictionary;
use Aphiria\Collections\KeyValuePair;
use Aphiria\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatters\HtmlMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\PlainTextMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\ContentNegotiation\MediaTypeFormatters\XmlMediaTypeFormatter;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Uri;
use Aphiria\Reflection\TypeResolver;
use InvalidArgumentException;
use LogicException;

/**
 * Defines a request builder
 */
class RequestBuilder
{
    /** @var IMediaTypeFormatterMatcher The media type formatter matcher */
    private IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher;
    /** @var string|null The HTTP method */
    private ?string $method = null;
    /** @var Uri|null The URI of the request */
    private ?Uri $uri = null;
    /** @var Headers The request headers */
    private Headers $headers;
    /** @var IBody The request body */
    private ?IBody $body = null;
    /** @var IDictionary The request properties */
    private IDictionary $properties;
    /** @var string The protocol version */
    private string $protocolVersion = '1.1';
    /** @var string The request target type */
    private string $requestTargetType = RequestTargetTypes::ORIGIN_FORM;

    /**
     * @param IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher The media type formatter matcher
     * @param string $defaultContentType The default Content-Type header value
     * @param string $defaultAccept The default Accept header value
     */
    public function __construct(
        IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher = null,
        string $defaultContentType = 'application/json',
        string $defaultAccept = '*/*'
    ) {
        $this->mediaTypeFormatterMatcher = $mediaTypeFormatterMatcher ?? new MediaTypeFormatterMatcher([
            new JsonMediaTypeFormatter(),
            new XmlMediaTypeFormatter(),
            new HtmlMediaTypeFormatter(),
            new PlainTextMediaTypeFormatter()
        ]);
        $this->headers = new Headers([
            new KeyValuePair('Content-Type', $defaultContentType),
            new KeyValuePair('Accept', $defaultAccept)
        ]);
        $this->properties = new HashTable();
    }

    /**
     * Builds the request
     *
     * @return IRequest The built request
     * @throws LogicException Thrown if required properties on the request builder were not set
     * @throws InvalidArgumentException Thrown if the request was invalid
     */
    public function build(): IRequest
    {
        if ($this->method === null) {
            throw new LogicException('Method is not set');
        }

        if ($this->uri === null) {
            throw new LogicException('URI is not set');
        }

        return new Request(
            $this->method,
            $this->uri,
            $this->headers,
            $this->body,
            $this->properties,
            $this->protocolVersion,
            $this->requestTargetType
        );
    }

    /**
     * Sets a body tha
     *
     * @param IBody|mixed $body The request body or the raw body that will be converted to a request
     * @return self For chaining
     * @throws InvalidArgumentException Thrown if the body type was not supported
     * @throws SerializationException Thrown if the body could not be serialized
     */
    public function withBody($body): self
    {
        $new = clone $this;

        if ($body === null) {
            $new->body = null;
        } elseif ($body instanceof IBody) {
            $new->body = $body;
        } elseif (\is_array($body) || \is_object($body) || \is_scalar($body)) {
            $type = TypeResolver::resolveType($body);

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

    /**
     * Adds a header to the request
     *
     * @param string $name The name of the header to add
     * @param array|string $values The value(s) to add
     * @param bool $append Whether or not to append the value(s) if the header already exists
     * @return self For chaining
     */
    public function withHeader(string $name, $values, bool $append = false): self
    {
        $new = clone $this;
        $new->headers->add($name, $values, $append);

        return $new;
    }

    /**
     * Adds a header to the request
     *
     * @param array $headers The mapping of header names to values
     * @return self For chaining
     */
    public function withManyHeaders(array $headers): self
    {
        $new = clone $this;

        foreach ($headers as $name => $value) {
            $new->headers->add($name, $value);
        }

        return $new;
    }

    /**
     * Sets the HTTP method
     *
     * @param string $method The HTTP method
     * @return self For chaining
     */
    public function withMethod(string $method): self
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * Adds a property to the request
     *
     * @param string $name The name of the property to add
     * @param mixed $value The value of the property
     * @return self For chaining
     */
    public function withProperty(string $name, $value): self
    {
        $new = clone $this;
        $new->properties->add($name, $value);

        return $new;
    }

    /**
     * Sets the protocol version of the request
     *
     * @param string $protocolVersion
     * @return self For chaining
     */
    public function withProtocolVersion(string $protocolVersion): self
    {
        $new = clone $this;
        $new->protocolVersion = $protocolVersion;

        return $new;
    }

    /**
     * Sets the target type of the request
     *
     * @param string $requestTargetType
     * @return self For chaining
     */
    public function withRequestTargetType(string $requestTargetType): self
    {
        $new = clone $this;
        $new->requestTargetType = $requestTargetType;

        return $new;
    }

    /**
     * Sets the request URI
     *
     * @param string|Uri $uri The request URI
     * @return self For chaining
     * @throws InvalidArgumentException Thrown if the URI was not a string nor URI
     */
    public function withUri($uri): self
    {
        $new = clone $this;

        if ($uri instanceof Uri) {
            $new->uri = $uri;
        } elseif (\is_string($uri)) {
            $new->uri = new Uri($uri);
        } else {
            throw new InvalidArgumentException('URI must be instance of ' . Uri::class . ' or string');
        }

        return $new;
    }
}
