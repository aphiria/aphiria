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
 * Defines the interface for all HTTP message bodies to implement
 */
interface IHttpBody
{
    /**
     * Reads an HTTP body as a stream
     *
     * @return IStream The stream
     */
    public function readAsStream() : IStream;

    /**
     * Reads an HTTP body as a string
     *
     * @return string The string
     */
    public function readAsString() : string;

    /**
     * Writes an HTTP body to a stream
     *
     * @param IStream $stream The stream to write to
     */
    public function writeToStream(IStream $stream) : void;
}
