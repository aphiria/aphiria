<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\IO\Streams\IStream;
use RuntimeException;

/**
 * Defines the interface for all HTTP message bodies to implement
 */
interface IBody
{
    /**
     * Reads the HTTP body as a string
     *
     * @return string The string
     */
    public function __toString(): string;

    /**
     * Gets the length of the HTTP body
     *
     * @return int|null The length if it could be computed, otherwise null
     */
    public function getLength(): ?int;

    /**
     * Reads the HTTP body as a stream
     *
     * @return IStream The stream
     * @throws RuntimeException Thrown if there was an error reading as a stream
     */
    public function readAsStream(): IStream;

    /**
     * Reads the HTTP body as a string
     *
     * @return string The string
     */
    public function readAsString(): string;

    /**
     * Writes the HTTP body to a stream
     *
     * @param IStream $stream The stream to write to
     * @throws RuntimeException Thrown if there was an error writing to the stream
     */
    public function writeToStream(IStream $stream): void;
}
