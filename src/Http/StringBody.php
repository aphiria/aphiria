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
use Opulence\IO\Streams\Stream;

/**
 * Defines the string HTTP body
 */
class StringBody implements IHttpBody
{
    /** @var string The body content */
    protected $content = '';
    /** @var IStream The underlying stream */
    private $stream = null;

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
    public function readAsStream() : IStream
    {
        if ($this->stream === null) {
            $this->stream = new Stream(fopen('php://temp', 'r+'));
        }
        
        $this->stream->write($this->content);
        
        return $this->stream;
    }

    /**
     * @inheritdoc
     */
    public function readAsString() : string
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function writeToStream(IStream $stream) : void
    {
        $stream->write($this->content);
    }
}
