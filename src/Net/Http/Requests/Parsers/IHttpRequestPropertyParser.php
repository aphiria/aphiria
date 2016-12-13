<?php
namespace Opulence\Net\Http\Requests\Parsers;

use Opulence\Net\Http\Requests\IHttpRequestMessage;

/**
 * Defines the interface for HTTP request property parsers to implement
 */
interface IHttpRequestPropertyParser
{
    public function isSecure(IHttpRequestMessage $message) : bool;
}