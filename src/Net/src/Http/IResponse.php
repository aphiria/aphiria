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

use InvalidArgumentException;

/**
 * Defines the interface for HTTP response messages to implement
 */
interface IResponse extends IHttpMessage
{
    /** @var string|null The reason phrase of the response if one is set, otherwise null */
    public ?string $reasonPhrase;
    /** @var HttpStatusCode The HTTP status code of the response */
    public HttpStatusCode $statusCode;
}
