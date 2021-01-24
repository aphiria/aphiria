<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

/**
 * Defines a multipart body part
 */
class MultipartBodyPart
{
    /**
     * @param Headers $headers The headers of this body part
     * @param IBody|null $body The body of this body part if one is set, otherwise null
     */
    public function __construct(private Headers $headers, private ?IBody $body)
    {
    }

    /**
     * Gets the multipart body part as a string
     * Note: This can be used in raw HTTP messages
     *
     * @return string The body part as a string
     */
    public function __toString(): string
    {
        return "{$this->headers}\r\n\r\n" . ($this->body === null ? '' : (string)$this->body);
    }

    /**
     * Gets the body of this body part
     *
     * @return IBody|null The body of this body part if one is set, otherwise null
     */
    public function getBody(): ?IBody
    {
        return $this->body;
    }

    /**
     * Gets the headers of this body part
     *
     * @return Headers The headers of this body part
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }
}
