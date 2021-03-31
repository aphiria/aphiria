<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\IO\Streams;

use InvalidArgumentException;
use RuntimeException;

/**
 * Defines a basic stream
 */
final class Stream implements IStream
{
    /** @var list<string> The list of readable stream modes */
    private static array $readStreamModes = [
        'a+',
        'c+',
        'c+b',
        'c+t',
        'r',
        'rb',
        'rt',
        'r+',
        'r+b',
        'r+t',
        'w+',
        'w+b',
        'w+t',
        'x+',
        'x+b',
        'x+t'
    ];
    /** @var list<string> The list of writable stream modes */
    private static array $writeStreamModes = [
        'a',
        'a+',
        'c+',
        'c+b',
        'c+t',
        'rw',
        'r+',
        'r+b',
        'r+t',
        'w',
        'wb',
        'w+',
        'w+b',
        'w+t',
        'x+',
        'x+b',
        'x+t'
    ];
    /** @var resource|null The underlying stream handle */
    private $handle;
    /** @var bool Whether or not the stream is readable */
    private bool $isReadable;
    /** @var bool Whether or not the stream is seekable */
    private bool $isSeekable;
    /** @var bool Whether or not the stream is writable */
    private bool $isWritable;

    /**
     * @param resource $handle The underlying stream handle
     * @param int|null $length The length of the stream, if known
     */
    public function __construct($handle, private ?int $length = null)
    {
        /** @psalm-suppress DocblockTypeContradiction We purposely guard ourselves against non-resources */
        if (!\is_resource($handle)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        $this->handle = $handle;
        $streamMetadata = \stream_get_meta_data($this->handle);
        $this->isReadable = \in_array($streamMetadata['mode'], self::$readStreamModes, true);
        $this->isSeekable = $streamMetadata['seekable'];
        $this->isWritable = \in_array($streamMetadata['mode'], self::$writeStreamModes, true);
    }

    /**
     * Closes the stream
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        try {
            $this->rewind();

            return $this->readToEnd();
        } catch (RuntimeException $ex) {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function close(): void
    {
        if (\is_resource($this->handle)) {
            /** @psalm-suppress InvalidPropertyAssignmentValue We do not care about the "closed-resource" type */
            if (\fclose($this->handle) === false) {
                // Cannot test failed stream closures
                // @codeCoverageIgnoreStart
                throw new RuntimeException('Failed to close stream');
                // @codeCoverageIgnoreEnd
            }

            $this->handle = $this->length = null;
            $this->isReadable = $this->isSeekable = $this->isWritable = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function copyToStream(IStream $stream, int $bufferSize = 8192): void
    {
        while (!$this->isEof()) {
            $stream->write($this->read($bufferSize));
        }
    }

    /**
     * @inheritdoc
     */
    public function getLength(): ?int
    {
        // Handle a closed stream
        if ($this->handle === null) {
            throw new RuntimeException('Unable to get size of closed stream');
        }

        if ($this->length !== null) {
            return $this->length;
        }

        /** @var array{size: int} $fileStats */
        $fileStats = \fstat($this->handle);

        return isset($fileStats['size']) ? ($this->length = $fileStats['size']) : null;
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): int
    {
        if (!\is_resource($this->handle)) {
            throw new RuntimeException('Unable to get position of closed stream');
        }

        if (($position = \ftell($this->handle)) === false) {
            // Cannot test failing to get the position of a stream
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to get position of stream');
            // @codeCoverageIgnoreEnd
        }

        return $position;
    }

    /**
     * @inheritdoc
     */
    public function isEof(): bool
    {
        if (!\is_resource($this->handle)) {
            throw new RuntimeException('Unable to tell if at EOF on closed stream');
        }

        return \feof($this->handle);
    }

    /**
     * @inheritdoc
     */
    public function isReadable(): bool
    {
        return $this->isReadable;
    }

    /**
     * @inheritdoc
     */
    public function isSeekable(): bool
    {
        return $this->isSeekable;
    }

    /**
     * @inheritdoc
     */
    public function isWritable(): bool
    {
        return $this->isWritable;
    }

    /**
     * @inheritdoc
     */
    public function read(int $length): string
    {
        if (!$this->isReadable) {
            throw new RuntimeException('Stream is not readable');
        }

        /** @psalm-suppress PossiblyNullArgument This will never be null if it's readable */
        if (($content = \fread($this->handle, $length)) === false) {
            // Cannot test failing to read a stream
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to read stream');
            // @codeCoverageIgnoreEnd
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function readToEnd(): string
    {
        if (!$this->isReadable) {
            throw new RuntimeException('Stream is not readable');
        }

        /** @psalm-suppress PossiblyNullArgument This will never be null if it's readable */
        if (($content = \stream_get_contents($this->handle)) === false) {
            // Cannot test failing to get a stream's contents
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to read stream');
            // @codeCoverageIgnoreEnd
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @inheritdoc
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        /** @psalm-suppress PossiblyNullArgument This will never be null if it's seekable */
        if (!$this->isSeekable || \fseek($this->handle, $offset, $whence)) {
            throw new RuntimeException('Stream is not seekable');
        }

        /** @psalm-suppress PossiblyNullArgument This will never be null if it's seekable */
        if (\fseek($this->handle, $offset, $whence) === -1) {
            // Cannot test failing to seek in a stream
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Error while seeking stream');
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @inheritdoc
     */
    public function write(string $data): void
    {
        if (!$this->isWritable) {
            throw new RuntimeException('Stream is not writable');
        }

        /** @psalm-suppress PossiblyNullArgument This will never be null if it's writable */
        if (\fwrite($this->handle, $data) === false) {
            // Cannot test failing to write to a stream
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to write to stream');
            // @codeCoverageIgnoreEnd
        }

        // Reset the length, which if knowable will be recalculated next time getLength() is called
        $this->length = null;
    }
}
