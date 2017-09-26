<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests\Parsers;

use Opulence\Net\Http\Requests\IHttpRequestMessage;

/**
 * Defines the interface for HTTP request message parsers to implement
 */
interface IHttpRequestMessageParser
{
    /**
     * Parses a request for a client IP
     *
     * @param IHttpRequestMessage $message The message to parse
     * @return string The client IP address
     */
    public function getClientIpAddress(IHttpRequestMessage $message) : string;

    /**
     * Parses a request for form data in the body
     *
     * @param IHttpRequestMessage $message The message to parse
     * @return array The mapping of input names to values
     */
    public function getFormData(IHttpRequestMessage $message) : array;

    /**
     * Parses a request for an input value
     *
     * @param IHttpRequestMessage $message The message to parse
     * @param string $name The name of the input whose value we want
     * @param mixed|null $default The default value, if none was found
     * @return mixed The value of the input
     */
    public function getInput(IHttpRequestMessage $message, string $name, $default = null);

    /**
     * Parses a request for a query string parameter  value
     *
     * @param IHttpRequestMessage $message The message to parse
     * @param string $name The name of the query string parameter to look for
     * @param mixed|null $default The default value, if none was found
     * @return mixed The value of the query string parameter
     */
    public function getQueryStringParam(IHttpRequestMessage $message, string $name, $default = null);
}
