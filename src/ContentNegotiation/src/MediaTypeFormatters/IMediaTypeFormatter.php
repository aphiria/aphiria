<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
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
    /** @var string The default character encoding this formatter supports */
    public string $defaultEncoding { get; }
    /** @var string The default media type this formatter supports */
    public string $defaultMediaType { get; }
    /** @var list<string> The list of character encodings this formatter supports */
    public array $supportedEncodings { get; }
    /**
     * The list of media types this formatter supports
     * These media types are listed in the order of preference by the formatter
     *
     * @var list<string>
     */
    public array $supportedMediaTypes { get; }

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
