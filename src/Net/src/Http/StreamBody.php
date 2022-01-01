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

/**
 * Defines the stream HTTP body
 */
class StreamBody implements IBody
{
    /**
     * @param IStream $stream The body content
     */
    public function __construct(protected readonly IStream $stream)
    {
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->readAsString();
    }

    /**
     * @inheritdoc
     */
    public function getLength(): ?int
    {
        return $this->stream->getLength();
    }

    /**
     * @inheritdoc
     */
    public function readAsStream(): IStream
    {
        return $this->stream;
    }

    /**
     * @inheritdoc
     */
    public function readAsString(): string
    {
        return (string)$this->stream;
    }

    /**
     * @inheritdoc
     */
    public function writeToStream(IStream $stream): void
    {
        if ($this->stream->isSeekable()) {
            $this->stream->rewind();
        }

        $this->stream->copyToStream($stream);
    }
}
