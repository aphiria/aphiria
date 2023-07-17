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

use Aphiria\Net\Uri;
use InvalidArgumentException;
use LogicException;

/**
 * Defines a request builder
 */
interface IRequestBuilder
{
    /**
     * Builds the request
     *
     * @return IRequest The built request
     * @throws LogicException Thrown if required properties on the request builder were not set
     * @throws InvalidArgumentException Thrown if the request was invalid
     */
    public function build(): IRequest;

    /**
     * Sets a body
     *
     * @param IBody|null $body The request body to use, or null if not using a body
     * @return static For chaining
     * @throws InvalidArgumentException Thrown if the body type was not supported
     */
    public function withBody(?IBody $body): static;

    /**
     * Adds a header to the request
     *
     * @param string $name The name of the header to add
     * @param string|int|float|list<string|int|float> $values The value(s) to add
     * @param bool $append Whether or not to append the value(s) if the header already exists
     * @return static For chaining
     */
    public function withHeader(string $name, string|int|float|array $values, bool $append = false): static;

    /**
     * Adds many headers to the request
     *
     * @param array<string, string|int|float|list<string|int|float>> $headers The mapping of header names to values
     * @return static For chaining
     */
    public function withManyHeaders(array $headers): static;

    /**
     * Sets the HTTP method
     *
     * @param string $method The HTTP method
     * @return static For chaining
     */
    public function withMethod(string $method): static;

    /**
     * Adds a property to the request
     *
     * @param string $name The name of the property to add
     * @param mixed $value The value of the property
     * @return static For chaining
     */
    public function withProperty(string $name, mixed $value): static;

    /**
     * Sets the protocol version of the request
     *
     * @param string $protocolVersion
     * @return static For chaining
     */
    public function withProtocolVersion(string $protocolVersion): static;

    /**
     * Sets the target type of the request
     *
     * @param RequestTargetType $requestTargetType
     * @return static For chaining
     */
    public function withRequestTargetType(RequestTargetType $requestTargetType): static;

    /**
     * Sets the request URI
     *
     * @param string|Uri $uri The request URI
     * @return static For chaining
     * @throws InvalidArgumentException Thrown if the URI was not a string nor URI
     */
    public function withUri(string|Uri $uri): static;
}
