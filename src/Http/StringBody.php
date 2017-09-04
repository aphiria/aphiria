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
 * Defines the string HTTP body
 */
class StringBody implements IHttpBody
{
    /** @var string The body content */
    protected $content = '';

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
