<?php
namespace Opulence\Net\Http;

use Opulence\IO\Streams\IStream;

/**
 * Defines the string HTTP body
 */
class StringBody implements IHttpBody
{
    protected $content = '';

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function readAsStream() : IStream
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function readAsString() : string
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function writeToStream(IStream $stream) : void
    {
        // Todo
    }
}
