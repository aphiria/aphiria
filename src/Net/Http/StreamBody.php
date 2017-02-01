<?php
namespace Opulence\Net\Http;

use Opulence\IO\Streams\IStream;

/**
 * Defines the stream HTTP body
 */
class StreamBody implements IHttpBody
{
    protected $stream = null;
    
    public function __construct(IStream $stream)
    {
        $this->stream = $stream;
    }
    
    // Todo
}
