<?php
namespace Opulence\IO\Streams;

/**
 * Defines the interface for streams to implement
 */
interface IStream
{
    public function close() : void;
    
    public function getLength() : ?int;
    
    public function getPosition() : int;
    
    public function isEof() : bool;
    
    public function isReadable() : bool;
    
    public function isSeekable() : bool;
    
    public function isWritable() : bool;
    
    public function read(int $length) : string;
    
    public function readToEnd() : string;
    
    public function rewind() : void;
    
    public function seek(int $position) : void;
    
    public function write(string $data);
}
