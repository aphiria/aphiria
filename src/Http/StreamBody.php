<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
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
    protected $stream = null;

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
    public function readAsStream() : IStream
    {
        return $this->stream;
    }

    /**
     * @inheritdoc
     */
    public function readAsString() : string
    {
        return (string)$this->stream;
    }

    /**
     * @inheritdoc
     */
    public function writeToStream(IStream $stream) : void
    {
        $this->stream->copyToStream($stream);
    }
}
