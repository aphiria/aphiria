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

use Aphiria\ExtensionMethods\IExtendable;

/**
 * Defines the interface for HTTP response messages to implement
 */
interface IResponse extends IHttpMessage, IExtendable
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
     * @return int The HTTP status code of the response
     */
    public function getStatusCode(): int;

    /**
     * Sets the HTTP status code of the response
     *
     * @param int $statusCode The HTTP status code of the response
     * @param string|null $reasonPhrase The reason phrase if there is one, otherwise null
     */
    public function setStatusCode(int $statusCode, ?string $reasonPhrase = null): void;
}
