<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\MediaTypeFormatters;

use Aphiria\IO\Streams\IStream;

/**
 * Defines the interface for media type formatters to implement
 */
interface IMediaTypeFormatter
{
    /**
     * Gets whether or not the formatter can read the input type
     *
     * @param string $type The type to check (best to use TypeResolver::resolveType())
     * @return bool True if this formatter can read the input type, otherwise false
     */
    public function canReadType(string $type): bool;

    /**
     * Gets whether or not the formatter can write the input type
     *
     * @param string $type The type to check (best to use TypeResolver::resolveType())
     * @return bool True if this formatter can write the input type, otherwise false
     */
    public function canWriteType(string $type): bool;

    /**
     * Gets the default character encoding this formatter supports
     *
     * @return string The default character encoding
     */
    public function getDefaultEncoding(): string;

    /**
     * Gets the default media type this formatter supports
     *
     * @return string The default media type
     */
    public function getDefaultMediaType(): string;

    /**
     * Gets the list of character encodings this formatter supports
     *
     * @return list<string> The list of supported character encodings
     */
    public function getSupportedEncodings(): array;

    /**
     * Gets the list of media types this formatter supports
     * These media types are listed in the order of preference by the formatter
     *
     * @return list<string> The list of supported media types
     */
    public function getSupportedMediaTypes(): array;

    /**
     * Reads content from a string and converts it to the input type
     *
     * @param IStream $stream The stream to read from
     * @param string $type The type to convert to (best to use TypeResolver::resolveType())
     * @return int|float|bool|string|object|array The converted content
     * @throws SerializationException Thrown if the content could not be read and converted to the input type
     */
    public function readFromStream(IStream $stream, string $type): int|float|bool|string|object|array;

    /**
     * Writes the input object to the input stream
     *
     * @param int|float|bool|string|object|array $value The value to write
     * @param IStream $stream The stream to write to
     * @param string|null $encoding The character encoding to use, or null if using the default one
     * @throws SerializationException Thrown if the content could not be converted to the input type and written
     */
    public function writeToStream(int|float|bool|string|object|array $value, IStream $stream, ?string $encoding): void;
}
