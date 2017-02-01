<?php
namespace Opulence\Net\Http;

use Opulence\IO\Streams;

/**
 * Defines the interface for all HTTP message bodies to implement
 */
interface IHttpBody
{
    public function readAsStream() : IStream;
    
    public function readAsString() : string;
    
    public function writeToStream(IStream $stream) : void;
}
