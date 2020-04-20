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

use Aphiria\Collections\IDictionary;
use Aphiria\Net\Uri;

/**
 * Defines the interface for HTTP request messages to implement
 */
interface IRequest extends IHttpMessage
{
    /**
     * Gets the HTTP method for the request
     *
     * @return string The HTTP method
     */
    public function getMethod(): string;

    /**
     * Gets the properties of the request
     * These are custom pieces of metadata that the application can attach to the request
     *
     * @return IDictionary The collection of properties
     */
    public function getProperties(): IDictionary;

    /**
     * Gets the URI of the request
     *
     * @return Uri The URI
     */
    public function getUri(): Uri;
}
