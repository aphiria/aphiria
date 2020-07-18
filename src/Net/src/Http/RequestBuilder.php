<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\Collections\HashTable;
use Aphiria\Collections\IDictionary;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use LogicException;

/**
 * Defines a request builder
 */
class RequestBuilder implements IRequestBuilder
{
    /** @var string|null The HTTP method */
    protected ?string $method = null;
    /** @var Uri|null The URI of the request */
    protected ?Uri $uri = null;
    /** @var Headers The request headers */
    protected Headers $headers;
    /** @var IBody The request body */
    protected ?IBody $body = null;
    /** @var IDictionary The request properties */
    protected IDictionary $properties;
    /** @var string The protocol version */
    protected string $protocolVersion = '1.1';
    /** @var string The request target type */
    protected string $requestTargetType = RequestTargetTypes::ORIGIN_FORM;

    public function __construct()
    {
        $this->headers = new Headers();
        $this->properties = new HashTable();
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
    public function withBody(?IBody $body): self
    {
        $new = clone $this;
        $new->body = $body;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withHeader(string $name, $values, bool $append = false): self
    {
        $new = clone $this;
        $new->headers->add($name, $values, $append);

        return $new;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function withMethod(string $method): self
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withProperty(string $name, $value): self
    {
        $new = clone $this;
        $new->properties->add($name, $value);

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withProtocolVersion(string $protocolVersion): self
    {
        $new = clone $this;
        $new->protocolVersion = $protocolVersion;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withRequestTargetType(string $requestTargetType): self
    {
        $new = clone $this;
        $new->requestTargetType = $requestTargetType;

        return $new;
    }

    /**
     * @inheritdoc
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
