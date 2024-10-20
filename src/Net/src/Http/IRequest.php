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

use Aphiria\Collections\IDictionary;
use Aphiria\Net\Uri;

/**
 * Defines the interface for HTTP request messages to implement
 */
interface IRequest extends IHttpMessage
{
    /** @var string The HTTP method for the request */
    public string $method { get; }
    /** @var IDictionary<string, mixed> The properties of the request, which includes custom metadata the application can attach */
    public IDictionary $properties { get; }
    /** @var Uri The URI of the request */
    public Uri $uri { get; }
}
