<?php
namespace Opulence\Net\Http\Responses\Formatters;

use Opulence\Net\Http\IHttpHeaders;

/**
 * Defines the interface for response header formatters to implement
 */
interface IHttpResponseHeaderFormatter
{
    public function deleteCookie(IHttpHeaders $headers, string $name, string $path = '/', ?string $domain = null, bool $isSecure = false, bool $isHttpOnly = true) : void;

    public function getCookies(IHttpHeaders $headers, bool $includeDeletedCookies = false) : array;

    public function setCookie(IHttpHeaders $headers, Cookie $cookie) : void;

    public function setCookies(IHttpHeaders $headers, array $cookies) : void;
}
