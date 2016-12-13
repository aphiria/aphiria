<?php
namespace Opulence\Net;

/**
 * Defines the interface for URIs to implement
 */
interface IUri 
{
    public static function createFromString(string $uri) : IUri;
    
    public function getFragment() : string;
    
    public function getHost() : string;
    
    public function getPath() : string;
    
    public function getPort() : int;
    
    public function getQueryString() : string;
    
    public function getScheme() : string;
    
    public function getUserInfo() : string;
    
    public function isAbsoluteUri() : bool;
    
    public function matchesPath(string $path, bool $isRegex = false) : bool;
    
    public function matchesUri(string $uri, bool $isRegex = false) : bool;
}