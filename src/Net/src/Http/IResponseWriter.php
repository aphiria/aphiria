<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

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
     * @param IResponse $response The response to write
     * @throws RuntimeException Thrown if the output stream could not be written to
     */
    public function writeResponse(IResponse $response): void;
}
