<?php
namespace Opulence\Net\Http;

/**
 * Defines the interface for all HTTP headers to implement
 */
interface IHttpHeaders
{
    public function get(string $name, $default = null, bool $onlyReturnFirst = true);
    
    public function has(string $name) : bool;
    
    public function remove(string $name) : void;
    
    public function set(string $name, $values, bool $shouldReplace = true) : void;
}
