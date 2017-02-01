<?php
namespace Opulence\Net\Http\Requests\Parsers;

use Opulence\Net\Http\Requests\IHttpRequestMessage;

/**
 * Defines the interface for HTTP request message parsers to implement
 */
interface IHttpRequestMessageParser
{
    public function getClientIpAddress(IHttpRequestMessage $message) : string;
    
    public function getFormData(IHttpRequestMessage $message) : array;
    
    public function getInput(IHttpRequestMessage $message, string $name, $default = null);
    
    public function getQueryVar(IHttpRequestMessage $message, string $name, $default = null);
    
    public function getQueryVars(IHttpRequestMessage $message) : array;
}
