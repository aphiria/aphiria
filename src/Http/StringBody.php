<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http;

use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;

/**
 * Defines the string HTTP body
 */
class StringBody implements IHttpBody
{
    /** @var string The body content */
    protected $content = '';
    /** @var IStream The underlying stream */
    private $stream;

    /**
     * @param string $content The body content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
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
        return \mb_strlen($this->content);
    }

    /**
     * @inheritdoc
     */
    public function readAsStream(): IStream
    {
        if ($this->stream === null) {
            $this->stream = new Stream(fopen('php://temp', 'r+b'));
            $this->stream->write($this->content);
            $this->stream->rewind();
        }

        return $this->stream;
    }

    /**
     * @inheritdoc
     */
    public function readAsString(): string
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function writeToStream(IStream $stream): void
    {
        $stream->write($this->content);
    }
}
