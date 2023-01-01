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

/**
 * Defines a multipart body part
 */
readonly class MultipartBodyPart
{
    /**
     * @param Headers $headers The headers of this body part
     * @param IBody|null $body The body of this body part if one is set, otherwise null
     */
    public function __construct(public Headers $headers, public ?IBody $body)
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
}
