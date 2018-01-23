<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use Opulence\IO\Streams\IStream;
use RuntimeException;

/**
 * Defines the interface for media type formatters to implement
 */
interface IMediaTypeFormatter
{
    /**
     * Gets the list of character encodings this formatter supports
     *
     * @return array The list of supported character encodings
     */
    public function getSupportedEncodings() : array;

    /**
     * Gets the list of media types this formatter supports
     * These media types are listed in the order of preference by the formatter
     *
     * @return array The list of supported media types
     */
    public function getSupportedMediaTypes() : array;

    /**
     * Reads content from a string and converts it to the input type
     *
     * @param string $type The type to convert to
     * @param IStream $stream The stream to read from
     * @param bool $readAsArrayOfType Whether or not we're reading the stream content as an array of the input type
     * @return int|double|float|bool|string|object|array The converted content
     * @throws RuntimeException Thrown if the content could not be read and converted to the input type
     */
    public function readFromStream(string $type, IStream $stream, bool $readAsArrayOfType = false);

    /**
     * Writes the input object to the input stream
     *
     * @param int|double|float|bool|string|object|array $object The object to write
     * @param IStream $stream The stream to write to
     */
    public function writeToStream($object, IStream $stream) : void;
}
