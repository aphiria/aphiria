<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http;

use RuntimeException;

/**
 * Defines the interface for response writers to implement
 */
interface IResponseWriter
{
    /**
     * Gets whether or not the headers have already been sent
     *
     * @return bool True if the headers have already been sent, otherwise false
     */
    public function headersAreSent(): bool;

    /**
     * Writes the response to the output stream
     *
     * @param IHttpResponseMessage $response The response to write
     * @throws RuntimeException Thrown if the output stream could not be written to
     */
    public function writeResponse(IHttpResponseMessage $response): void;
}
