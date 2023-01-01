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

use InvalidArgumentException;

/**
 * Defines the interface for HTTP response messages to implement
 */
interface IResponse extends IHttpMessage
{
    /**
     * Gets the reason phrase of the response
     *
     * @return string|null The reason phrase if one is set, otherwise null
     */
    public function getReasonPhrase(): ?string;

    /**
     * Gets the HTTP status code of the response
     *
     * @return HttpStatusCode The HTTP status code of the response
     */
    public function getStatusCode(): HttpStatusCode;

    /**
     * Sets the HTTP status code of the response
     *
     * @param HttpStatusCode|int $statusCode The HTTP status code of the response
     * @param string|null $reasonPhrase The reason phrase if there is one, otherwise null
     * @throws InvalidArgumentException Thrown if the status code was invalid
     */
    public function setStatusCode(HttpStatusCode|int $statusCode, ?string $reasonPhrase = null): void;
}
