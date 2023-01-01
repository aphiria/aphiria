<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\Collections\HashTable;
use Aphiria\Collections\IDictionary;
use Aphiria\Net\Uri;
use LogicException;

/**
 * Defines a request builder
 */
class RequestBuilder
{
    /** @var Headers The request headers */
    protected readonly Headers $headers;
    /** @var IDictionary<string, mixed> The request properties */
    protected readonly IDictionary $properties;
    /** @var string|null The HTTP method */
    protected ?string $method = null;
    /** @var Uri|null The URI of the request */
    protected ?Uri $uri = null;
    /** @var IBody|null The request body if one is set, otherwise null */
    protected ?IBody $body = null;
    /** @var string The protocol version */
    protected string $protocolVersion = '1.1';
    /** @var RequestTargetType The request target type */
    protected RequestTargetType $requestTargetType;

    public function __construct()
    {
        $this->headers = new Headers();
        /** @var HashTable<string, mixed> properties */
        $this->properties = new HashTable();
        $this->requestTargetType = RequestTargetType::OriginForm;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function withBody(?IBody $body): static
    {
        $new = clone $this;
        $new->body = $body;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withHeader(string $name, string|int|float|array $values, bool $append = false): static
    {
        $new = clone $this;
        $new->headers->add($name, $values, $append);

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withManyHeaders(array $headers): static
    {
        $new = clone $this;

        /** @var string|int|float $value */
        foreach ($headers as $name => $value) {
            $new->headers->add($name, $value);
        }

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withMethod(string $method): static
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withProperty(string $name, mixed $value): static
    {
        $new = clone $this;
        $new->properties->add($name, $value);

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withProtocolVersion(string $protocolVersion): static
    {
        $new = clone $this;
        $new->protocolVersion = $protocolVersion;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withRequestTargetType(RequestTargetType $requestTargetType): static
    {
        $new = clone $this;
        $new->requestTargetType = $requestTargetType;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withUri(string|Uri $uri): static
    {
        $new = clone $this;

        if ($uri instanceof Uri) {
            $new->uri = $uri;
        } else {
            $new->uri = new Uri($uri);
        }

        return $new;
    }
}
