<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\IO\Streams;

use RuntimeException;

/**
 * Defines the interface for streams to implement
 */
interface IStream
{
    /**
     * @var bool Whether or not the stream is at the end of file
     * @throws RuntimeException Thrown if the stream is closed or EOF cannot be determined
     */
    public bool $isEof { get; }
    /** @var bool Whether or not the stream is readable */
    public bool $isReadable { get; }
    /** @var bool Whether or not the stream is seekable */
    public bool $isSeekable { get; }
    /** @var bool Whether or not the stream is writable */
    public bool $isWritable { get; }
    /**
     * @var int|null The length of the stream if it is knowable, otherwise null
     * @throws RuntimeException Thrown if the stream is closed
     */
    public ?int $length { get; }
    /**
     * @var int The current stream position
     * @throws RuntimeException Thrown if the stream is closed
     */
    public int $position { get; }

    /**
     * Rewinds the stream and reads it to the end as a string
     * This could result in a lot of data being loaded into memory
     *
     * @return string The entire stream as a string
     */
    public function __toString(): string;

    /**
     * Closes the stream
     *
     * @throws RuntimeException Thrown if the stream failed to be closed
     */
    public function close(): void;

    /**
     * Copies this stream to another
     *
     * @param IStream $stream The stream to copy to
     * @param int $bufferSize The buffer size to use when copying, if needed
     * @throws RuntimeException Thrown if the source stream is closed
     */
    public function copyToStream(IStream $stream, int $bufferSize = 8192): void;

    /**
     * Reads a chunk of the stream
     *
     * @param int $length The number of bytes to read
     * @return string The stream contents as a string
     * @throws RuntimeException Thrown if the stream is not readable
     */
    public function read(int $length): string;

    /**
     * Reads to the end of the stream
     *
     * @return string The stream contents as a string
     * @throws RuntimeException Thrown if the stream is not readable
     */
    public function readToEnd(): string;

    /**
     * Rewinds to the beginning of the stream
     *
     * @throws RuntimeException Thrown if the stream is not seekable
     */
    public function rewind(): void;

    /**
     * Seeks to a certain position in the stream
     *
     * @param int $offset The offset to seek to
     * @param int $whence How the position will be calculated from the offset (identical to fseek())
     * @throws RuntimeException Thrown if the stream is not seekable
     */
    public function seek(int $offset, int $whence = SEEK_SET): void;

    /**
     * Writes to the stream
     *
     * @param string $data The data to write
     * @throws RuntimeException Thrown if the stream is not writable
     */
    public function write(string $data): void;
}
