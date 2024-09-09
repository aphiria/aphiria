<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

/**
 * Defines the interface for all HTTP messages
 */
interface IHttpMessage
{
    /** @var IBody|null The body of the HTTP message if there is one, otherwise null */
    public ?IBody $body { get; set; }
    /** @var Headers The headers of the HTTP message */
    public Headers $headers { get; }
    /** @var string The protocol version (eg '1.1' or '2.0') of the HTTP message */
    public string $protocolVersion { get; }

    /**
     * Gets the message as a string
     *
     * @return string The string representation of the message
     * @note This string can be used as a raw HTTP message
     */
    public function __toString(): string;
}
