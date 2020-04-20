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

/**
 * Defines the interface for all HTTP messages
 */
interface IHttpMessage
{
    /**
     * Gets the message as a string
     * Note: This string can be used as a raw HTTP message
     *
     * @return string The string representation of the message
     */
    public function __toString(): string;

    /**
     * Gets the body of the HTTP message
     *
     * @return IBody|null The body if there is one, otherwise null
     */
    public function getBody(): ?IBody;

    /**
     * Gets the headers of the HTTP message
     *
     * @return Headers The headers
     */
    public function getHeaders(): Headers;

    /**
     * Gets the protocol version (eg '1.1' or '2.0') from the HTTP message
     *
     * @return string The protocol version
     */
    public function getProtocolVersion(): string;

    /**
     * Sets the body of the HTTP message
     *
     * @param IBody $body The body
     */
    public function setBody(IBody $body): void;
}
