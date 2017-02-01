<?php
namespace Opulence\Net\Http\Requests\Parsers;

use Opulence\Net\Http\IHttpHeaders;

/**
 * Defines the interface for HTTP request header parsers to implement
 */
interface IHttpRequestHeaderParser
{
    public function getCookie(IHttpHeaders $headers, string $name);
    
    public function getCookies(IHttpHeaders $headers) : array;
    
    public function isJson(IHttpHeaders $headers) : bool;
    
    public function isXhr(IHttpHeaders $headers) : bool;
}
