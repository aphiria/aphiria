<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\IO\Streams\IStream;

/**
 * Defines the stream HTTP body
 */
class StreamBody implements IHttpBody
{
    /** @var IStream The body content */
    protected $stream;

    /**
     * @param IStream $stream The body content
     */
    public function __construct(IStream $stream)
    {
        $this->stream = $stream;
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
